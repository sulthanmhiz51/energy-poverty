# ==========================================================
# HOUSEHOLD ENERGY POVERTY PREDICTION SYSTEM
# Rule-Based Reasoning + Random Forest Validation
# ==========================================================

import joblib
import pandas as pd
import numpy as np

# ==========================================================
# LOAD MODEL DAN KOMPONEN
# ==========================================================

rf_model = joblib.load("household_energy_learning_model.pkl")

label_encoders = joblib.load("label_encoders.pkl")

thresholds = joblib.load("reasoning_thresholds.pkl")


# ==========================================================
# LOAD THRESHOLD RULE-BASED
# ==========================================================

income_q1 = thresholds["income_q1"]
income_med = thresholds["income_med"]

family_med = thresholds["family_med"]
family_q3 = thresholds["family_q3"]

dependency_med = thresholds["dependency_med"]
dependency_q3 = thresholds["dependency_q3"]

education_med = thresholds["education_med"]

utilities_q1 = thresholds["utilities_q1"]
utilities_med = thresholds["utilities_med"]

floor_q1 = thresholds["floor_q1"]

asset_q1 = thresholds["asset_q1"]
asset_med = thresholds["asset_med"]


# ==========================================================
# PREDICT FUNCTION
# ==========================================================


def predict(input_data):
    """
    input_data berupa dictionary.

    Contoh:

    {

        "Total Household Income": ...,

        "Education Rank": ...,

        "Occupation group": ...,

        "Total Number of Family members": ...,

        "Members with age less than 5 year old": ...,

        "Members with age 5 - 17 years old": ...,

        "Total number of family members employed": ...,

        "House Floor Area": ...,

        "Number of bedrooms": ...,

        "Tenure Status": ...,

        "Electricity": ...,

        "Utilities Expenditure": ...,

        "Main Source of Water Supply": ...,

        "Number of Refrigerator/Freezer": ...,

        "Number of Airconditioner": ...,

        "Number of Washing Machine": ...,

        "Number of Television": ...,

        "Number of Personal Computer": ...

    }

    """

    # =====================================================
    # CONVERT TO DATAFRAME
    # =====================================================

    df = pd.DataFrame([input_data])

    # =====================================================
    # FEATURE ENGINEERING
    # =====================================================

    df["Dependency Ratio"] = (
        df["Members with age less than 5 year old"]
        + df["Members with age 5 - 17 years old"]
    ) / (df["Total number of family members employed"] + 1)

    df["Household Asset Index"] = (
        df["Number of Refrigerator/Freezer"]
        + df["Number of Airconditioner"]
        + df["Number of Washing Machine"]
        + df["Number of Television"]
        + df["Number of Personal Computer"]
    )

    # =====================================================
    # RULE BASED REASONING
    # =====================================================

    row = df.iloc[0]

    score = 0

    # Income

    if row["Total Household Income"] <= income_q1:

        score += 10

    elif row["Total Household Income"] <= income_med:

        score += 5

    # Family Size

    if row["Total Number of Family members"] >= family_q3:

        score += 10

    elif row["Total Number of Family members"] >= family_med:

        score += 5

    # Dependency Ratio

    if row["Dependency Ratio"] >= dependency_q3:

        score += 10

    elif row["Dependency Ratio"] >= dependency_med:

        score += 5

    # Education

    if row["Education Rank"] <= education_med:

        score += 10

    # Utilities

    if row["Utilities Expenditure"] <= utilities_q1:

        score += 10

    elif row["Utilities Expenditure"] <= utilities_med:

        score += 5

    # House Floor Area

    if row["House Floor Area"] <= floor_q1:

        score += 10

    # Asset Index

    if row["Household Asset Index"] <= asset_q1:

        score += 10

    elif row["Household Asset Index"] <= asset_med:

        score += 5

    # Electricity

    if row["Electricity"] == 0:

        score += 10

    # Employment

    if row["Total number of family members employed"] == 0:

        score += 10

    # Bedroom Adequacy

    if row["Number of bedrooms"] < (row["Total Number of Family members"] / 3):

        score += 10

    # =====================================================
    # GENERATE VULNERABILITY LEVEL
    # =====================================================

    if score >= 70:

        level = "High"

    elif score >= 35:

        level = "Medium"

    else:

        level = "Low"

    # =====================================================
    # GENERATE SUBSIDY PRIORITY
    # =====================================================

    if level == "High":

        priority = "Prioritas Tinggi"

    elif level == "Medium":

        priority = "Prioritas Menengah"

    else:

        priority = "Prioritas Rendah"

    # =====================================================
    # GENERATE AI EXPLANATION
    # =====================================================

    explanation = []

    if row["Total Household Income"] <= income_q1:

        explanation.append("Pendapatan rumah tangga relatif rendah")

    if row["Dependency Ratio"] >= dependency_q3:

        explanation.append("Rasio tanggungan keluarga tinggi")

    if row["Household Asset Index"] <= asset_q1:

        explanation.append("Indikator aset rumah tangga relatif rendah")

    if row["House Floor Area"] <= floor_q1:

        explanation.append("Kondisi tempat tinggal relatif terbatas")

    if row["Utilities Expenditure"] <= utilities_q1:

        explanation.append("Pengeluaran utilitas relatif rendah")

    if len(explanation) == 0:

        explanation.append("Tidak ditemukan indikator kerentanan yang dominan")

    explanation = "; ".join(explanation)

    # =====================================================
    # PERSIAPAN DATA UNTUK RANDOM FOREST
    # =====================================================

    df_rf = df.copy()

    # Encode seluruh kolom object menggunakan
    # encoder hasil training

    for col, encoder in label_encoders.items():
        if col in df_rf.columns:
            df_rf[col] = encoder.transform(df_rf[col])

    # Feature yang digunakan Random Forest
    # harus sama seperti saat training

    X_rf = df_rf.drop(
        columns=[
            "Household Energy Vulnerability Score",
            "Household Energy Vulnerability Level",
        ],
        errors="ignore",
    )

    # =====================================================
    # RANDOM FOREST VALIDATION
    # =====================================================

    rf_prediction = str(rf_model.predict(X_rf)[0])

    rf_probability = rf_model.predict_proba(X_rf)[0]

    # =====================================================
    # HITUNG CONFIDENCE RANDOM FOREST
    # =====================================================

    confidence = round(np.max(rf_probability) * 100, 2)

    # =====================================================
    # CEK KESESUAIAN RULE-BASED DAN RANDOM FOREST
    # =====================================================

    if rf_prediction == level:
        validation_status = "Consistent"

    else:
        validation_status = "Need Attention"

    # =====================================================
    # SUSUN HASIL AKHIR
    # =====================================================

    result = {
        "Household Energy Vulnerability Score": int(score),
        "Household Energy Vulnerability Level": level,
        "Subsidy Priority": priority,
        "AI Explanation": explanation,
        "Random Forest Prediction": rf_prediction,
        "Random Forest Confidence (%)": confidence,
        "Validation Status": validation_status,
    }

    return result


# ==========================================================
# CONTOH PENGGUNAAN
# ==========================================================

if __name__ == "__main__":

    sample_input = {
        "Total Household Income": 150000,
        "Education Rank": 2,
        "Occupation group": 3,
        "Total Number of Family members": 5,
        "Members with age less than 5 year old": 1,
        "Members with age 5 - 17 years old": 2,
        "Total number of family members employed": 1,
        "House Floor Area": 45,
        "Number of bedrooms": 2,
        "Tenure Status": "Owner",
        "Electricity": 1,
        "Utilities Expenditure": 350,
        "Main Source of Water Supply": "Community System",
        "Number of Refrigerator/Freezer": 1,
        "Number of Airconditioner": 0,
        "Number of Washing Machine": 1,
        "Number of Television": 1,
        "Number of Personal Computer": 0,
    }

    result = predict(sample_input)

    print("\n===== PREDICTION RESULT =====\n")

    for key, value in result.items():

        print(f"{key} : {value}")
