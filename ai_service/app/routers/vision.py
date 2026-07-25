import json
import logging
import re
import tempfile
from pathlib import Path

from fastapi import APIRouter, Depends, File, HTTPException, UploadFile
from pydantic import BaseModel

from app.security import optional_api_key
from app.services.openai_client import OpenAIClient
from app.services.fallback import FallbackProcessor
from app.services.prompt_manager import prompt_manager
from app.utils.image_processing import preprocess_image, validate_image_quality

logger = logging.getLogger("dmrms-ai.vision")
router = APIRouter()
openai_client = OpenAIClient()
fallback = FallbackProcessor()


class DocumentAnalysisResult(BaseModel):
    doc_type: str
    extracted_text: str
    fields: dict
    confidence: float


class FraudCheckResult(BaseModel):
    is_authentic: bool
    confidence: float
    issues: list[str]


@router.post("")
async def analyze_image(file: UploadFile = File(...), api_key: str | None = Depends(optional_api_key)):
    if file.content_type not in ("image/jpeg", "image/png"):
        raise HTTPException(status_code=400, detail="Only JPEG and PNG images are supported.")

    contents = await file.read()
    with tempfile.NamedTemporaryFile(delete=False, suffix=Path(file.filename).suffix) as tmp:
        tmp.write(contents)
        tmp_path = tmp.name

    try:
        quality = validate_image_quality(tmp_path)
        if not quality.get("is_clear", True):
            logger.warning("Image quality check failed: %s", quality)

        base64_image = preprocess_image(tmp_path)
        result = await openai_client.vision_analysis(
            image_path=base64_image,
            prompt="Extract all visible text and information from this document image.",
        )

        return {
            "filename": file.filename,
            "extracted_text": result.get("content", ""),
            "quality": quality,
        }
    except Exception as e:
        logger.error("Vision analysis failed: %s", e)
        raise HTTPException(status_code=500, detail="Vision analysis failed.")
    finally:
        Path(tmp_path).unlink(missing_ok=True)


@router.post("/analyze-document", response_model=DocumentAnalysisResult)
async def analyze_document(
    file: UploadFile = File(...),
    doc_type: str = "general",
    api_key: str | None = Depends(optional_api_key),
):
    doc_types = {"birth_certificate", "education_certificate", "national_id", "general"}
    if doc_type not in doc_types:
        raise HTTPException(status_code=400, detail=f"Unsupported document type. Must be one of: {doc_types}")

    if file.content_type not in ("image/jpeg", "image/png", "application/pdf"):
        raise HTTPException(status_code=400, detail="Only JPEG, PNG, and PDF files are supported.")

    contents = await file.read()
    with tempfile.NamedTemporaryFile(delete=False, suffix=Path(file.filename).suffix) as tmp:
        tmp.write(contents)
        tmp_path = tmp.name

    try:
        result = fallback.process_document(tmp_path, doc_type)
        return DocumentAnalysisResult(
            doc_type=doc_type,
            extracted_text=result.get("text", ""),
            fields=result.get("fields", {}),
            confidence=result.get("confidence", 0.0),
        )
    except Exception as e:
        logger.error("Document analysis failed: %s", e)
        raise HTTPException(status_code=500, detail="Document analysis failed.")
    finally:
        Path(tmp_path).unlink(missing_ok=True)


@router.post("/fraud-check", response_model=FraudCheckResult)
async def fraud_check(
    file: UploadFile = File(...),
    doc_type: str = "general",
    api_key: str | None = Depends(optional_api_key),
):
    if file.content_type not in ("image/jpeg", "image/png"):
        raise HTTPException(status_code=400, detail="Only JPEG and PNG images are supported.")

    contents = await file.read()
    with tempfile.NamedTemporaryFile(delete=False, suffix=Path(file.filename).suffix) as tmp:
        tmp.write(contents)
        tmp_path = tmp.name

    try:
        base64_image = preprocess_image(tmp_path)

        # Use the enhanced fraud detection prompt from prompt manager
        fraud_prompt_template = prompt_manager.get_system_prompt("fraud_detection")
        user_prompt = prompt_manager.render_prompt("fraud_detection", {
            "doc_type": doc_type,
            "image_data": "(image provided as base64)",
            "previous_records": "(no previous records available)",
        })

        prompt = f"{fraud_prompt_template}\n\n{user_prompt}\n\nReturn ONLY valid JSON (no markdown, no explanation)."

        result = await openai_client.vision_analysis(
            image_path=base64_image,
            prompt=prompt,
        )

        # Parse structured JSON from AI response
        content = result.get("content", "")
        parsed = _extract_fraud_json(content)

        if parsed and "overall_assessment" in parsed:
            assessment = parsed["overall_assessment"]
            is_authentic = assessment.get("authentic", False)
            confidence = assessment.get("confidence", 0.5)

            # Collect all fraud indicators from the structured response
            issues = parsed.get("fraud_indicators", [])
            if not isinstance(issues, list):
                issues = []

            # Also extract anomalies from sub-sections
            signature = parsed.get("signature_analysis", {})
            if signature.get("anomalies"):
                issues.append(f"Signature anomaly: {signature['anomalies']}")

            font = parsed.get("font_analysis", {})
            if font.get("font_consistency") == "inconsistent" and font.get("anomalies"):
                issues.append(f"Font inconsistency: {font['anomalies']}")

            forensics = parsed.get("digital_forensics", {})
            for key, val in forensics.items():
                if val and any(w in str(val).lower() for w in ["artifact", "inconsisten", "anomal", "cut", "fake", "suspicious", "replacement", "mismatch"]):
                    issues.append(f"{key}: {val}")

            return FraudCheckResult(
                is_authentic=is_authentic,
                confidence=min(confidence, 0.95) if is_authentic else max(confidence, 0.5),
                issues=list(set(issues)),  # deduplicate
            )
        else:
            # Fallback: attempt keyword extraction if JSON parsing fails
            content_lower = content.lower()
            is_authentic = "authentic" in content_lower or "genuine" in content_lower
            issues = []
            if "tamper" in content_lower:
                issues.append("Signs of tampering detected")
            if "forgery" in content_lower or "fake" in content_lower:
                issues.append("Possible forgery indicators")
            if "inconsistent" in content_lower:
                issues.append("Inconsistent elements found")
            if "manipulat" in content_lower:
                issues.append("Digital manipulation detected")

            return FraudCheckResult(
                is_authentic=is_authentic,
                confidence=result.get("confidence", 0.5),
                issues=issues,
            )
    except Exception as e:
        logger.error("Fraud check failed: %s", e)
        raise HTTPException(status_code=500, detail="Fraud check failed.")
    finally:
        Path(tmp_path).unlink(missing_ok=True)


def _extract_fraud_json(text: str) -> dict | None:
    """Extract structured JSON from AI response, handling markdown and formatting."""
    if not text:
        return None

    # Remove markdown code block fences
    text = re.sub(r'```(?:json)?\s*', '', text)

    # Try to find JSON object
    match = re.search(r'\{.*\}', text, re.DOTALL)
    if not match:
        return None

    try:
        return json.loads(match.group(0))
    except json.JSONDecodeError:
        # Try to fix common issues: trailing commas, single quotes
        fixed = re.sub(r',\s*}', '}', text)
        fixed = re.sub(r',\s*]', ']', fixed)
        try:
            match2 = re.search(r'\{.*\}', fixed, re.DOTALL)
            if match2:
                return json.loads(match2.group(0))
        except json.JSONDecodeError:
            pass
        return None
