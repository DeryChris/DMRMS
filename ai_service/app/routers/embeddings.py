import logging
from math import sqrt

from fastapi import APIRouter, Depends, HTTPException
from pydantic import BaseModel

from app.config import settings
from app.security import optional_api_key
from app.services.openai_client import OpenAIClient
from app.services.fallback import FallbackProcessor

logger = logging.getLogger("dmrms-ai.embeddings")
router = APIRouter()
openai_client = OpenAIClient()
fallback = FallbackProcessor()


class EmbeddingRequest(BaseModel):
    text: str


class EmbeddingResponse(BaseModel):
    embedding: list[float]
    model: str
    dimensions: int


class SimilarityRequest(BaseModel):
    text1: str
    text2: str


class SimilarityResponse(BaseModel):
    similarity: float
    model: str


class RankingRequest(BaseModel):
    candidates: list[dict]
    ideal_profile: str
    top_n: int = 10


class RankingResponse(BaseModel):
    rankings: list[dict]
    model: str


def cosine_similarity(a: list[float], b: list[float]) -> float:
    if len(a) != len(b):
        raise ValueError("Vectors must have the same dimensionality")
    dot = sum(x * y for x, y in zip(a, b))
    norm_a = sqrt(sum(x * x for x in a))
    norm_b = sqrt(sum(x * x for x in b))
    if norm_a == 0 or norm_b == 0:
        return 0.0
    return dot / (norm_a * norm_b)


@router.post("", response_model=EmbeddingResponse)
async def create_embedding(request: EmbeddingRequest, api_key: str | None = Depends(optional_api_key)):
    if not request.text.strip():
        raise HTTPException(status_code=400, detail="Text cannot be empty.")

    try:
        result = await openai_client.create_embeddings(
            text=request.text,
            model=settings.openai_embedding_model,
        )
        return EmbeddingResponse(
            embedding=result["embedding"],
            model=result.get("model", settings.openai_embedding_model),
            dimensions=len(result["embedding"]),
        )
    except Exception as e:
        logger.error("Embedding creation failed: %s", e)
        raise HTTPException(status_code=500, detail="Embedding creation failed.")


@router.post("/similarity", response_model=SimilarityResponse)
async def compute_similarity(request: SimilarityRequest, api_key: str | None = Depends(optional_api_key)):
    if not request.text1.strip() or not request.text2.strip():
        raise HTTPException(status_code=400, detail="Both texts must be non-empty.")

    try:
        result1 = await openai_client.create_embeddings(request.text1, settings.openai_embedding_model)
        result2 = await openai_client.create_embeddings(request.text2, settings.openai_embedding_model)

        similarity = cosine_similarity(result1["embedding"], result2["embedding"])

        return SimilarityResponse(
            similarity=round(similarity, 4),
            model=settings.openai_embedding_model,
        )
    except Exception as e:
        logger.error("Similarity computation failed: %s", e)
        raise HTTPException(status_code=500, detail="Similarity computation failed.")


@router.post("/rank", response_model=RankingResponse)
async def rank_candidates(request: RankingRequest, api_key: str | None = Depends(optional_api_key)):
    if not request.candidates:
        raise HTTPException(status_code=400, detail="Candidates list cannot be empty.")
    if not request.ideal_profile.strip():
        raise HTTPException(status_code=400, detail="Ideal profile cannot be empty.")

    try:
        ideal_embedding = await openai_client.create_embeddings(
            request.ideal_profile, settings.openai_embedding_model
        )

        ranked = []
        for candidate in request.candidates:
            candidate_text = " ".join(str(v) for v in candidate.values())
            if not candidate_text.strip():
                continue

            try:
                cand_embedding = await openai_client.create_embeddings(
                    candidate_text, settings.openai_embedding_model
                )
                score = cosine_similarity(ideal_embedding["embedding"], cand_embedding["embedding"])
                ranked.append({**candidate, "score": round(score, 4)})
            except Exception as e:
                logger.warning("Failed to rank candidate %s: %s", candidate.get("id"), e)
                if fallback.generate_ranking([candidate]):
                    ranked.append({**candidate, "score": 0.5})

        ranked.sort(key=lambda x: x["score"], reverse=True)

        return RankingResponse(
            rankings=ranked[: request.top_n],
            model=settings.openai_embedding_model,
        )
    except Exception as e:
        logger.error("Ranking failed: %s", e)
        raise HTTPException(status_code=500, detail="Candidate ranking failed.")
