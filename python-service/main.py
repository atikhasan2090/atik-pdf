from fastapi import FastAPI, HTTPException
from fastapi.responses import Response, StreamingResponse
from pydantic import BaseModel
from typing import List, Any, Optional
import io
import os
from pdf_generator import LargeTablePdfGenerator

app = FastAPI(title="Atik PDF Generation Service", description="High-performance PDF generation microservice for Laravel.")

class PdfRequest(BaseModel):
    title: Optional[str] = "Generated Document"
    columns: List[str]
    rows: List[List[Any]]

@app.get("/")
def read_root():
    return {"status": "ok", "message": "Atik PDF Python Engine is running."}

@app.post("/generate-pdf")
def generate_pdf(request: PdfRequest):
    try:
        buffer = io.BytesIO()
        generator = LargeTablePdfGenerator(buffer, title=request.title)
        
        # We pass the data and columns
        generator.generate(request.columns, request.rows)
        
        pdf_bytes = buffer.getvalue()
        buffer.close()
        
        return Response(content=pdf_bytes, media_type="application/pdf")
    except Exception as e:
        raise HTTPException(status_code=500, detail=str(e))

@app.post("/stream-pdf")
def stream_pdf(request: PdfRequest):
    """
    For extremely large datasets, we can yield chunks of the PDF 
    as they are generated, though ReportLab's standard canvas isn't 
    purely streamable in chunks over HTTP out of the box without special handling.
    This serves as a placeholder for streaming logic if we implemented a line-by-line 
    PDF generation tool instead of ReportLab's Platypus.
    """
    # For now, it's just generating and returning like generate-pdf
    # but structured to show where a generator would yield.
    try:
        buffer = io.BytesIO()
        generator = LargeTablePdfGenerator(buffer, title=request.title)
        generator.generate(request.columns, request.rows)
        buffer.seek(0)
        
        return StreamingResponse(buffer, media_type="application/pdf")
    except Exception as e:
        raise HTTPException(status_code=500, detail=str(e))

if __name__ == "__main__":
    import uvicorn
    uvicorn.run("main:app", host="0.0.0.1", port=8000, reload=True)
