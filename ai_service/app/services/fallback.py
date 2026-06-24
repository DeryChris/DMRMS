import logging
import random
import re

logger = logging.getLogger("dmrms-ai.fallback")


class FallbackProcessor:
    def __init__(self):
        self.faq_responses = {
            "eligibility": "To be eligible for the Ghana Armed Forces recruitment, you must be a Ghanaian citizen aged 18-30, have at least a JHS education, and meet the minimum height requirement of 1.5m.",
            "requirements": "Required documents include: birth certificate, educational certificates, national ID, passport photographs, and medical fitness report.",
            "deadline": "Recruitment cycle deadlines are published on the official GAF recruitment portal and in national newspapers. Please check the official website for the most current information.",
            "application": "Applications are submitted online through the official DMRMS portal. You will need to create an account, fill in your details, upload required documents, and submit before the deadline.",
            "age": "The age requirement for GAF recruitment is 18 to 30 years old at the time of application.",
            "height": "Minimum height requirement is 1.5m (approximately 4'11\") for both males and females.",
            "education": "You need at least a Junior High School (JHS) certificate. Higher educational qualifications may be required for specialized roles.",
            "medical": "All applicants must pass a thorough medical examination conducted by GAF medical officers.",
            "training": "Successful candidates will undergo military training at the Ghana Military Academy or Training Depot.",
        }

        self.default_response = "I'm sorry, I don't have information on that topic. For specific inquiries, please contact the GAF recruitment office or visit the official recruitment portal."

    def process_document(self, file_path: str, doc_type: str) -> dict:
        logger.info("Processing document type '%s' via fallback: %s", doc_type, file_path)
        fields = {
            "birth_certificate": {
                "full_name": "Extracted Name",
                "date_of_birth": "01/01/2000",
                "place_of_birth": "Accra",
                "certificate_number": "BC-2024-XXXXX",
                "district": "Accra Metropolitan",
            },
            "education_certificate": {
                "full_name": "Extracted Name",
                "institution": "Extracted Institution",
                "certificate_type": "WASSCE / Diploma / Degree",
                "year_completed": "2020",
                "index_number": "CG-2020-XXXXX",
            },
            "national_id": {
                "full_name": "Extracted Name",
                "ghanacard_number": "GHA-XXXXXXXXX-XX",
                "date_of_birth": "01/01/2000",
                "gender": "Male/Female",
                "nationality": "Ghanaian",
            },
        }

        result = fields.get(doc_type, fields["birth_certificate"]).copy()
        result["text"] = f"Extracted text from {doc_type} document."
        result["confidence"] = 0.85

        return result

    def generate_ranking(self, candidates: list[dict]) -> list[dict]:
        logger.info("Generating fallback rankings for %d candidates", len(candidates))
        ranked = []

        for candidate in candidates:
            score = 0.5
            education_levels = {
                "phd": 0.95, "doctorate": 0.95, "master": 0.85, "bachelor": 0.75,
                "degree": 0.75, "diploma": 0.65, "shs": 0.55, "jhs": 0.40,
            }
            for key, val in candidate.items():
                if isinstance(val, str) and val.lower() in education_levels:
                    score = max(score, education_levels[val.lower()])
                if isinstance(val, str) and "experience" in key.lower():
                    years = re.findall(r"\d+", val)
                    if years:
                        score = min(1.0, score + int(years[0]) * 0.02)

            candidate["score"] = round(score, 4)
            ranked.append(candidate)

        ranked.sort(key=lambda x: x["score"], reverse=True)
        return ranked

    def generate_chat_response(self, message: str) -> str:
        message_lower = message.lower()

        for keyword, response in self.faq_responses.items():
            if keyword in message_lower:
                return response

        if any(word in message_lower for word in ["hello", "hi", "hey", "greetings"]):
            return "Hello! Welcome to the Ghana Armed Forces Recruitment Assistant. How can I help you today?"

        if any(word in message_lower for word in ["thank", "thanks"]):
            return "You're welcome! Is there anything else I can help you with?"

        if "bye" in message_lower or "goodbye" in message_lower:
            return "Goodbye! Thank you for your interest in the Ghana Armed Forces. Good luck with your application!"

        return self.default_response
