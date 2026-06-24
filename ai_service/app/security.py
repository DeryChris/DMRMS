from fastapi import Header, HTTPException, status

from app.config import settings


def validate_api_key(api_key: str) -> bool:
    return api_key == settings.internal_api_key


async def verify_api_key(x_api_key: str = Header(...)) -> None:
    if not validate_api_key(x_api_key):
        raise HTTPException(
            status_code=status.HTTP_401_UNAUTHORIZED,
            detail="Invalid API key",
        )


async def optional_api_key(x_api_key: str | None = Header(default=None)) -> str | None:
    if x_api_key and not validate_api_key(x_api_key):
        raise HTTPException(
            status_code=status.HTTP_401_UNAUTHORIZED,
            detail="Invalid API key",
        )
    return x_api_key
