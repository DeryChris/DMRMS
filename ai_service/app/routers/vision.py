import logging
import tempfile
from pathlib import Path

from fastapi import APIRouter, Depends, File, HTTPException, UploadFile
from pydantic import BaseModel

from app.security import optional_api_key
from app.services.openai_client import OpenAIClient
from app.services.fallback import FallbackProcessor
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
async def fraud_check(file: UploadFile = File(...), api_key: str | None = Depends(optional_api_key)):
    if file.content_type not in ("image/jpeg", "image/png"):
        raise HTTPException(status_code=400, detail="Only JPEG and PNG images are supported.")

    contents = await file.read()
    with tempfile.NamedTemporaryFile(delete=False, suffix=Path(file.filename).suffix) as tmp:
        tmp.write(contents)
        tmp_path = tmp.name

    try:
        base64_image = preprocess_image(tmp_path)
        result = await openai_client.vision_analysis(
            image_path=base64_image,
            prompt="Analyze this document image for signs of tampering, forgery, or digital manipulation. Look for inconsistent lighting, pixel irregularities, mismatched fonts, altered signatures, or any other artifacts that suggest fraud.",
        )

        content = result.get("content", "").lower()
        is_authentic = "authentic" in content or "genuine" in content
        issues = []
        if "tamper" in content:
            issues.append("Signs of tampering detected")
        if "forgery" in content or "fake" in content:
            issues.append("Possible forgery indicators")
        if "inconsistent" in content:
            issues.append("Inconsistent elements found")

        return FraudCheckResult(
            is_authentic=is_authentic,
            confidence=result.get("confidence", 0.5) if not is_authentic else 0.8,
            issues=issues,
        )
    except Exception as e:
        logger.error("Fraud check failed: %s", e)
        raise HTTPException(status_code=500, detail="Fraud check failed.")
    finally:
        Path(tmp_path).unlink(missing_ok=True)
