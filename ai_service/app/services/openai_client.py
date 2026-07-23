import asyncio
import hashlib
import logging
import time
from typing import Any

from app.config import settings

logger = logging.getLogger("dmrms-ai.openai")


class OpenAIClient:
    def __init__(self):
        self._client = None
        self._cost_tracker: dict[str, float] = {"total_cost": 0.0}
        self._rate_per_1k_tokens = {
            "gpt-4-turbo": {"input": 0.01, "output": 0.03},
            "gpt-4": {"input": 0.03, "output": 0.06},
            "gpt-3.5-turbo": {"input": 0.0015, "output": 0.002},
            "text-embedding-3-small": {"input": 0.00002, "output": 0.0},
            "text-embedding-3-large": {"input": 0.00013, "output": 0.0},
        }

    @property
    def client(self):
        if self._client is None:
            from openai import AsyncOpenAI
            self._client = AsyncOpenAI(api_key=settings.openai_api_key)
        return self._client

    async def verify_connectivity(self) -> bool:
        try:
            await self.client.models.list()
            return True
        except Exception as e:
            logger.error("OpenAI connectivity check failed: %s", e)
            raise

    async def _retry_with_backoff(self, func, max_retries: int = 3) -> Any:
        last_exception = None
        for attempt in range(max_retries):
            try:
                return await func()
            except Exception as e:
                last_exception = e
                if attempt < max_retries - 1:
                    wait = 2 ** attempt + 0.1 * (attempt ** 2)
                    logger.warning("Attempt %d failed: %s. Retrying in %.2fs...", attempt + 1, e, wait)
                    await asyncio.sleep(wait)
        raise last_exception

    def _track_cost(self, model: str, input_tokens: int, output_tokens: int):
        rates = self._rate_per_1k_tokens.get(model, self._rate_per_1k_tokens.get("gpt-4-turbo"))
        cost = (input_tokens / 1000) * rates["input"] + (output_tokens / 1000) * rates["output"]
        self._cost_tracker["total_cost"] += cost
        self._cost_tracker[f"model_{model}"] = self._cost_tracker.get(f"model_{model}", 0) + cost
        return cost

    def get_total_cost(self) -> float:
        return self._cost_tracker["total_cost"]

    def count_tokens(self, text: str) -> int:
        return len(text.split()) + len(text) // 4

    async def chat_completion(
        self,
        messages: list[dict],
        model: str | None = None,
        max_tokens: int = 1024,
        temperature: float = 0.7,
    ) -> dict:
        model = model or settings.openai_model

        async def call():
            response = await self.client.chat.completions.create(
                model=model,
                messages=messages,
                max_tokens=max_tokens,
                temperature=temperature,
            )
            return response

        response = await self._retry_with_backoff(call)

        input_tokens = response.usage.prompt_tokens if response.usage else 0
        output_tokens = response.usage.completion_tokens if response.usage else 0
        cost = self._track_cost(model, input_tokens, output_tokens)

        return {
            "content": response.choices[0].message.content,
            "model": response.model,
            "tokens_used": output_tokens,
            "input_tokens": input_tokens,
            "output_tokens": output_tokens,
            "cost": cost,
        }

    async def vision_analysis(
        self,
        image_path: str,
        prompt: str = "Extract all visible information from this image.",
        model: str | None = None,
    ) -> dict:
        model = model or settings.openai_model

        if image_path.startswith("http"):
            image_url = image_path
        else:
            image_url = f"data:image/jpeg;base64,{image_path}"

        async def call():
            response = await self.client.chat.completions.create(
                model=model,
                messages=[
                    {
                        "role": "user",
                        "content": [
                            {"type": "text", "text": prompt},
                            {"type": "image_url", "image_url": {"url": image_url, "detail": "high"}},
                        ],
                    }
                ],
                max_tokens=2048,
            )
            return response

        response = await self._retry_with_backoff(call)

        input_tokens = response.usage.prompt_tokens if response.usage else 0
        output_tokens = response.usage.completion_tokens if response.usage else 0
        self._track_cost(model, input_tokens, output_tokens)

        return {
            "content": response.choices[0].message.content,
            "model": response.model,
            "tokens_used": output_tokens,
        }

    async def create_embeddings(
        self,
        text: str,
        model: str | None = None,
    ) -> dict:
        model = model or settings.openai_embedding_model

        async def call():
            response = await self.client.embeddings.create(
                model=model,
                input=text,
            )
            return response

        response = await self._retry_with_backoff(call)

        input_tokens = response.usage.prompt_tokens if response.usage else 0
        self._track_cost(model, input_tokens, 0)

        return {
            "embedding": response.data[0].embedding,
            "model": response.model,
            "tokens_used": input_tokens,
        }
