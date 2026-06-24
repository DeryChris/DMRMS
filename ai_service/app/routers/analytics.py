import logging

from fastapi import APIRouter, Depends, HTTPException
from pydantic import BaseModel

from app.security import optional_api_key
from app.services.openai_client import OpenAIClient

logger = logging.getLogger("dmrms-ai.analytics")
router = APIRouter()
openai_client = OpenAIClient()


class PredictRequest(BaseModel):
    historical_data: dict
    target_cycle: str | None = None


class PredictResponse(BaseModel):
    predictions: dict
    confidence_scores: dict
    model: str


class InsightsRequest(BaseModel):
    data: dict
    context: str | None = None


class InsightsResponse(BaseModel):
    insights: list[str]
    summary: str


class ReportRequest(BaseModel):
    structured_data: dict
    report_type: str = "summary"


class ReportResponse(BaseModel):
    report: str
    sections: list[str]


@router.post("/predict", response_model=PredictResponse)
async def predict(request: PredictRequest, api_key: str | None = Depends(optional_api_key)):
    try:
        prompt = f"""
Analyze the following historical recruitment data and generate predictions for the next cycle.

Historical Data:
{request.historical_data}

Target Cycle: {request.target_cycle or 'Next cycle'}

Provide predictions for:
1. Expected application volume
2. Estimated success rate
3. Potential bottlenecks
4. Recommended adjustments
"""
        messages = [
            {"role": "system", "content": "You are an AI analytics specialist for military recruitment. Analyze data and provide actionable predictions."},
            {"role": "user", "content": prompt},
        ]

        result = await openai_client.chat_completion(
            messages=messages,
            max_tokens=2048,
        )

        return PredictResponse(
            predictions={
                "forecast": result["content"],
                "volume_estimate": len(str(request.historical_data.get("applicants", []))),
                "bottlenecks_identified": [],
            },
            confidence_scores={
                "volume": 0.75,
                "success_rate": 0.7,
                "bottlenecks": 0.6,
            },
            model=result.get("model", "gpt-4-turbo"),
        )
    except Exception as e:
        logger.error("Prediction failed: %s", e)
        raise HTTPException(status_code=500, detail="Prediction generation failed.")


@router.post("/insights", response_model=InsightsResponse)
async def generate_insights(request: InsightsRequest, api_key: str | None = Depends(optional_api_key)):
    try:
        prompt = f"""
Analyze the following recruitment data and generate key insights.

Context: {request.context or 'General recruitment analysis'}

Data:
{request.data}

Provide:
1. Key trends and patterns
2. Notable anomalies or outliers
3. Recommendations for improvement
4. Risk factors
"""
        messages = [
            {"role": "system", "content": "You are an AI data analyst specializing in recruitment analytics. Extract meaningful insights from data."},
            {"role": "user", "content": prompt},
        ]

        result = await openai_client.chat_completion(
            messages=messages,
            max_tokens=2048,
        )

        content = result["content"]
        lines = [l.strip("- ").strip() for l in content.split("\n") if l.strip()]
        sections = [s for s in lines if len(s) > 20][:5]

        return InsightsResponse(
            insights=sections[:5],
            summary=sections[0] if sections else content[:200],
        )
    except Exception as e:
        logger.error("Insights generation failed: %s", e)
        raise HTTPException(status_code=500, detail="Insights generation failed.")


@router.post("/report", response_model=ReportResponse)
async def generate_report(request: ReportRequest, api_key: str | None = Depends(optional_api_key)):
    try:
        prompt = f"""
Generate a comprehensive {request.report_type} report based on the following structured recruitment data.

Data:
{request.structured_data}

Include:
1. Executive summary
2. Key metrics and KPIs
3. Detailed analysis
4. Conclusions and recommendations
"""
        messages = [
            {"role": "system", "content": "You are an AI report generator for the Ghana Armed Forces recruitment system. Create clear, professional reports."},
            {"role": "user", "content": prompt},
        ]

        result = await openai_client.chat_completion(
            messages=messages,
            max_tokens=4096,
        )

        content = result["content"]
        sections = [s.strip() for s in content.split("\n\n") if s.strip()]

        return ReportResponse(
            report=content,
            sections=sections[:8],
        )
    except Exception as e:
        logger.error("Report generation failed: %s", e)
        raise HTTPException(status_code=500, detail="Report generation failed.")
