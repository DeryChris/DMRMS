from contextlib import asynccontextmanager
import logging

from fastapi import FastAPI
from fastapi.middleware.cors import CORSMiddleware

from app.config import settings
from app.routers import chat, vision, embeddings, analytics
from app.services.openai_client import OpenAIClient


logging.basicConfig(level=getattr(logging, settings.log_level.upper(), logging.INFO))
logger = logging.getLogger("dmrms-ai")


@asynccontextmanager
async def lifespan(app: FastAPI):
    logger.info("Starting DMRMS AI Service...")
    try:
        client = OpenAIClient()
        await client.verify_connectivity()
        logger.info("OpenAI connectivity verified successfully.")
    except Exception as e:
        logger.warning("OpenAI connectivity check failed: %s", e)
    yield
    logger.info("Shutting down DMRMS AI Service.")


app = FastAPI(
    title="DMRMS AI Service",
    description="AI microservice for the Defence Manpower Recruitment Management System",
    version="1.0.0",
    lifespan=lifespan,
)

app.add_middleware(
    CORSMiddleware,
    allow_origins=[
        "http://localhost",
        "http://localhost:3000",
        "http://localhost:5173",
        "http://127.0.0.1",
        "http://127.0.0.1:8000",
    ],
    allow_credentials=True,
    allow_methods=["*"],
    allow_headers=["*"],
)

app.include_router(chat.router, prefix="/chat", tags=["Chat"])
app.include_router(vision.router, prefix="/vision", tags=["Vision"])
app.include_router(embeddings.router, prefix="/embeddings", tags=["Embeddings"])
app.include_router(analytics.router, prefix="/analytics", tags=["Analytics"])


@app.get("/health", tags=["Health"])
async def health():
    return {
        "status": "healthy",
        "service": "DMRMS AI Service",
        "version": "1.0.0",
    }
