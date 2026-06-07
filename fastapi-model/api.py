from fastapi import FastAPI, HTTPException
from pydantic import BaseModel
import predict

app = FastAPI(title="Energy Poverty AI API")


# 1. The Cleaned-Up UI Payload (Only 15 Variables Now!)
class HouseholdData(BaseModel):
    income: float
    education: int
    family_size: int
    under_5: int
    age_5_17: int
    employed: int
    floor_area: float
    bedrooms: int
    electricity: int
    utilities: float
    fridge: int
    ac: int
    washing_machine: int
    tv: int
    pc: int


@app.post("/predict")
def analyze_household(data: HouseholdData):
    try:
        ai_input = {
            "Total Household Income": data.income,
            "Education Rank": data.education,
            "Occupation group": 3,
            "Total Number of Family members": data.family_size,
            "Members with age less than 5 year old": data.under_5,
            "Members with age 5 - 17 years old": data.age_5_17,
            "Total number of family members employed": data.employed,
            "House Floor Area": data.floor_area,
            "Number of bedrooms": data.bedrooms,
            "Tenure Status": "Owner",
            "Electricity": data.electricity,
            "Utilities Expenditure": data.utilities,
            "Main Source of Water Supply": "Community System",
            "Number of Refrigerator/Freezer": data.fridge,
            "Number of Airconditioner": data.ac,
            "Number of Washing Machine": data.washing_machine,
            "Number of Television": data.tv,
            "Number of Personal Computer": data.pc,
        }

        # Call the AI Model
        result = predict.predict(ai_input)

        # Format Output
        color = "green"
        level = result.get("Household Energy Vulnerability Level", "Unknown")
        raw_reasoning = result.get("AI Explanation", "")

        if "High" in level:
            color = "red"
            final_reasoning = f"Critical vulnerabilities identified: {raw_reasoning}."
        elif "Medium" in level:
            color = "yellow"
            final_reasoning = f"Moderate risk detected due to: {raw_reasoning}. Household may require monitoring or partial subsidy."
        else:
            # Reframe the Low Risk explanation to focus on stability
            if "Tidak ditemukan" in raw_reasoning:
                final_reasoning = "Household exhibits strong economic, asset, and infrastructure stability. No significant vulnerabilities detected."
            else:
                final_reasoning = f"Despite isolated factors ({raw_reasoning}), the household possesses sufficient income, assets, and infrastructure to remain at Low Risk."

        return {
            "status": f"{level}: {result.get('Subsidy Priority', '')}",
            "color": color,
            "reasoning": final_reasoning,  # <--- Pass the new smart reasoning here
            "score": int(result.get("Household Energy Vulnerability Score", 0)),
            "rf_prediction": str(result.get("Random Forest Prediction", "Unknown")),
            "rf_confidence": float(result.get("Random Forest Confidence (%)", 0.0)),
            "rf_validation": str(result.get("Validation Status", "Unknown")),
        }

    except Exception as e:
        raise HTTPException(status_code=500, detail=str(e))
