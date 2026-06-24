import base64
import io
import logging
from pathlib import Path

from PIL import Image, ImageEnhance, ImageFilter

logger = logging.getLogger("dmrms-ai.image")


def preprocess_image(file_path: str, target_size: tuple[int, int] = (1024, 1024)) -> str:
    img = Image.open(file_path)

    if img.mode != "RGB":
        img = img.convert("RGB")

    img.thumbnail(target_size, Image.LANCZOS)

    enhancer = ImageEnhance.Contrast(img)
    img = enhancer.enhance(1.2)

    enhancer = ImageEnhance.Sharpness(img)
    img = enhancer.enhance(1.5)

    enhancer = ImageEnhance.Brightness(img)
    img = enhancer.enhance(1.1)

    buffer = io.BytesIO()
    img.save(buffer, format="JPEG", quality=90, optimize=True)
    return base64.b64encode(buffer.getvalue()).decode("utf-8")


def validate_image_quality(file_path: str) -> dict:
    img = Image.open(file_path)

    if img.mode != "L":
        gray = img.convert("L")
    else:
        gray = img

    width, height = img.size
    pixels = list(gray.getdata())
    total_pixels = len(pixels)

    brightness = sum(pixels) / total_pixels

    histogram = gray.histogram()
    contrast = sum(abs(i - 128) * count for i, count in enumerate(histogram)) / (total_pixels * 128)

    edge_img = gray.filter(ImageFilter.FIND_EDGES)
    edge_pixels = list(edge_img.getdata())
    edge_intensity = sum(edge_pixels) / (total_pixels * 255)

    blurred = False
    if edge_intensity < 0.05:
        blurred = True

    return {
        "is_clear": not blurred,
        "brightness": round(brightness, 2),
        "contrast": round(contrast, 4),
        "sharpness": round(edge_intensity, 4),
        "dimensions": {"width": width, "height": height},
        "is_blurry": blurred,
    }


def extract_text_tesseract(file_path: str) -> str:
    try:
        import pytesseract
        img = Image.open(file_path)
        text = pytesseract.image_to_string(img)
        return text.strip()
    except ImportError:
        logger.warning("pytesseract not installed. Install with: pip install pytesseract")
        return ""
    except Exception as e:
        logger.error("Tesseract OCR failed: %s", e)
        return ""


def convert_to_base64(file_path: str) -> str:
    with open(file_path, "rb") as f:
        return base64.b64encode(f.read()).decode("utf-8")
