import logging
import tempfile
from pathlib import Path

logger = logging.getLogger("dmrms-ai.pdf")


def extract_text_from_pdf(file_path: str) -> str:
    try:
        import pdfplumber

        text_parts = []
        with pdfplumber.open(file_path) as pdf:
            for page in pdf.pages:
                text = page.extract_text()
                if text:
                    text_parts.append(text)

        if text_parts:
            return "\n".join(text_parts)

        logger.warning("pdfplumber returned no text, trying PyMuPDF...")
        return _extract_with_pymupdf(file_path)
    except ImportError:
        logger.warning("pdfplumber not installed, trying PyMuPDF...")
        return _extract_with_pymupdf(file_path)
    except Exception as e:
        logger.error("PDF extraction failed: %s", e)
        return ""


def _extract_with_pymupdf(file_path: str) -> str:
    try:
        import fitz

        text_parts = []
        with fitz.open(file_path) as doc:
            for page in doc:
                text = page.get_text()
                if text:
                    text_parts.append(text)

        return "\n".join(text_parts)
    except ImportError:
        logger.error("Neither pdfplumber nor PyMuPDF (fitz) is available. Install one of: pip install pdfplumber")
        return ""
    except Exception as e:
        logger.error("PyMuPDF extraction failed: %s", e)
        return ""


def extract_text_from_pdf_bytes(data: bytes) -> str:
    with tempfile.NamedTemporaryFile(delete=False, suffix=".pdf") as tmp:
        tmp.write(data)
        tmp_path = tmp.name

    try:
        return extract_text_from_pdf(tmp_path)
    finally:
        Path(tmp_path).unlink(missing_ok=True)
