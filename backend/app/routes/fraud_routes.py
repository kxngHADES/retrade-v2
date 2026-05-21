from fastapi import APIRouter, BackgroundTasks
from pydantic import BaseModel
from app.services.fraud_services import log_fraud_to_graph

router = APIRouter(prefix="/fraud", tags=["Fraud Tracking"])

class FraudReportPayload(BaseModel):
    reporter_id: str
    target_user_id: str
    reason: str
    description: str = ""

@router.post("/report_graph", status_code=202)
async def report_fraud_graph(payload: FraudReportPayload, background_tasks: BackgroundTasks):
    """
    Endpoint intended to track user reports silently in the background 
    and persist into the Neo4j fraud ring graph.
    """
    background_tasks.add_task(
        log_fraud_to_graph,
        payload.reporter_id,
        payload.target_user_id,
        payload.reason,
        payload.description
    )
    return {"success": True, "message": "Fraud report accepted for graph processing"}
