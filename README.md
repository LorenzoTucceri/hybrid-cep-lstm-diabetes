# A Complex Event Processing Framework for Patients with Diabetes: Real-Time Anomaly Detection and LSTM-based Classification and Forecasting

Hybrid Neuro-Symbolic Framework for Safe Diabetes Management

Diabetes mellitus is a chronic metabolic disorder that requires continuous monitoring to prevent acute complications and long-term vascular damage. While modern Continuous Glucose Monitoring (CGM) systems provide high-resolution data, they also generate large information streams that can overwhelm both patients and clinicians.

This project introduces an end-to-end hybrid neuro-symbolic framework designed to enhance both interpretability and safety in glycemic monitoring and prediction. The system combines:

* Deterministic Complex Event Processing (CEP) using the formal ISEQL language to detect clinically critical patterns in real time
* Deep Learning models, specifically optimized Long Short-Term Memory (LSTM) networks, for accurate glucose forecasting

To address the scarcity of severe pathological data, Discrete-Time Markov Chains (DTMCs) are used to generate realistic synthetic datasets. A rigorous ablation study identified a 60-day LSTM model (M60) as the optimal predictive core.

A key component of the architecture is a Deterministic Safety Guardrail, which supervises neural predictions to ensure clinical reliability and eliminate critical errors.

Key Results

* 83.0% overall diagnostic accuracy, peaking at 93.1% with extended historical windows
* 0% fatal false negatives in high-risk scenarios
* 99.06% of predictions within safe zones (A and B) of the Clarke Error Grid, even with a 60-minute prediction horizon

System Features

* Hybrid AI combining symbolic reasoning + neural networks
* Real-time pattern detection via CEP
* Patient-specific LSTM models trained asynchronously
* Explainable AI outputs to improve trust and usability
* Secure, interactive web application to reduce cognitive load and support clinical decisions
