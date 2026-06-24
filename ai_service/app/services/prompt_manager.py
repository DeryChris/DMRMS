import json
import logging
from pathlib import Path

logger = logging.getLogger("dmrms-ai.prompts")


class PromptManager:
    def __init__(self, prompts_dir: str | None = None):
        if prompts_dir is None:
            prompts_dir = str(Path(__file__).parent.parent.parent / "prompts")
        self.prompts_dir = Path(prompts_dir)
        self._cache: dict[str, dict] = {}

    def load_prompt(self, prompt_name: str) -> dict:
        if prompt_name in self._cache:
            return self._cache[prompt_name]

        json_path = self.prompts_dir / f"{prompt_name}.json"
        txt_path = self.prompts_dir / f"{prompt_name}.txt"

        if json_path.exists():
            with open(json_path, "r", encoding="utf-8") as f:
                prompt = json.load(f)
                self._cache[prompt_name] = prompt
                return prompt
        elif txt_path.exists():
            with open(txt_path, "r", encoding="utf-8") as f:
                content = f.read()
            prompt = {
                "name": prompt_name,
                "version": 1,
                "system_prompt": content,
                "user_prompt_template": "{input}",
            }
            self._cache[prompt_name] = prompt
            return prompt
        else:
            logger.warning("Prompt '%s' not found, using default.", prompt_name)
            return {
                "name": prompt_name,
                "version": 1,
                "system_prompt": "You are a helpful assistant for the Ghana Armed Forces recruitment system.",
                "user_prompt_template": "{input}",
            }

    def get_system_prompt(self, prompt_name: str) -> str:
        prompt = self.load_prompt(prompt_name)
        return prompt.get("system_prompt", "")

    def render_prompt(self, prompt_name: str, variables: dict | None = None) -> str:
        prompt = self.load_prompt(prompt_name)
        template = prompt.get("user_prompt_template", "{input}")
        if variables:
            try:
                return template.format(**variables)
            except KeyError as e:
                logger.warning("Missing template variable: %s", e)
                return template
        return template


prompt_manager = PromptManager()
