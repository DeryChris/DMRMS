import logging
import uuid

from fastapi import APIRouter, Depends, HTTPException
from pydantic import BaseModel

from app.config import settings
from app.security import optional_api_key
from app.services.openai_client import OpenAIClient
from app.services.fallback import FallbackProcessor

logger = logging.getLogger("dmrms-ai.chat")
router = APIRouter()
openai_client = OpenAIClient()
fallback = FallbackProcessor()

sessions: dict[str, list[dict]] = {}


class ChatRequest(BaseModel):
    message: str
    session_id: str | None = None


class ChatResponse(BaseModel):
    response: str
    session_id: str
    model: str
    tokens_used: int


@router.post("", response_model=ChatResponse)
async def chat(request: ChatRequest, api_key: str | None = Depends(optional_api_key)):
    session_id = request.session_id or str(uuid.uuid4())

    if session_id not in sessions:
        sessions[session_id] = [
            {
                "role": "system",
                "content": "You are a helpful recruitment assistant for the Ghana Armed Forces. Provide accurate and concise information about the recruitment process, eligibility requirements, and application procedures.",
            }
        ]

    sessions[session_id].append({"role": "user", "content": request.message})

    try:
        result = await openai_client.chat_completion(
            messages=sessions[session_id],
            model=settings.openai_model,
            max_tokens=1024,
        )
        sessions[session_id].append({
            "role": "assistant",
            "content": result["content"],
        })

        return ChatResponse(
            response=result["content"],
            session_id=session_id,
            model=result.get("model", settings.openai_model),
            tokens_used=result.get("tokens_used", 0),
        )
    except Exception as e:
        logger.warning("OpenAI chat failed, using fallback: %s", e)
        fallback_response = fallback.generate_chat_response(request.message)
        sessions[session_id].append({
            "role": "assistant",
            "content": fallback_response,
        })

        return ChatResponse(
            response=fallback_response,
            session_id=session_id,
            model="fallback",
            tokens_used=0,
        )
