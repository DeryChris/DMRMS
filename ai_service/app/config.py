from pydantic_settings import BaseSettings


class Settings(BaseSettings):
    openai_api_key: str = ""
    openai_model: str = "gpt-4-turbo"
    openai_embedding_model: str = "text-embedding-3-small"
    internal_api_key: str = "dmrms-internal-key-2026"
    rate_limit_per_minute: int = 10
    log_level: str = "INFO"

    model_config = {
        "env_file": ".env",
        "env_file_encoding": "utf-8",
        "extra": "ignore",
    }


settings = Settings()
