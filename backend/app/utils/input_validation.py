from fastapi import Request, HTTPException

async def validate_form_content_type(request: Request):
    content_type = request.headers.get("Content-Type", "")
    if "application/json" in content_type:
        raise HTTPException(status_code=400, detail="JSON payload not allowed for this form endpoint.")
    if "multipart/form-data" not in content_type and "application/x-www-form-urlencoded" not in content_type:
        raise HTTPException(status_code=400, detail="Unsupported Content-Type. Please use multipart/form-data or application/x-www-form-urlencoded.")
