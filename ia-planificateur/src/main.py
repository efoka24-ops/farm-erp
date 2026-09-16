from fastapi import FastAPI

app = FastAPI(title="TRU FARM ERP - IA Planificateur")


@app.get("/health")
def health():
    return {"status": "ok"}
