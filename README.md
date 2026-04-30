# A Complex Event Processing Framework for Patients with Diabetes: Real-Time Anomaly Detection and LSTM-based Classification and Forecasting

Diabetes mellitus is a chronic metabolic disorder that requires continuous monitoring to prevent
acute complications and long-term vascular damage. While modern Continuous Glucose Monitoring
(CGM) sensors provide high-resolution data, they generate vast streams of information that often
cause cognitive overload for both patients and clinicians. Furthermore, while purely data-driven
Artificial Intelligence (AI) models have shown great promise in glycemic forecasting, their opaque
"black-box" nature and inherent probabilistic error margins severely limit their trustworthiness in
safety-critical clinical environments.
To address these challenges, this thesis proposes an end-to-end, hybrid neuro-symbolic frame-
work. It synergistically combines deterministic Complex Event Processing (CEP)—using the for-
mal ISEQL language to detect clinically dangerous patterns in near real-time—with advanced Deep
Learning architectures.
To overcome the structural scarcity of severe pathological data, Discrete-Time Markov Chains
were implemented to generate highly realistic synthetic datasets. A rigorous ablation study identi-
fied an optimized 60-day Long Short-Term Memory (LSTM) network as the core predictive engine
(M60). To guarantee absolute clinical safety, this neural output is strictly supervised by a Determin-
istic Safety Guardrail. Experimental results on over 245,000 glycemic measurements demonstrate
an overall diagnostic accuracy of 83.0% (peaking at 93.1% on 80-day historical windows), while
achieving a crucial 0% rate of fatal False Negatives for highly critical patients.
Additionally,theframeworkimplementsanasynchronouspipelinetotrainpatient-specificLSTM
models for short-term forecasting. Even when extending the prediction horizon to a challenging 60
minutes, the system preserves absolute patient safety, maintaining 99.06% of projections within
the safe zones (A and B) of the Clarke Error Grid. The entire architecture is operationalized
through a secure, interactive web application designed to reduce cognitive load, providing explain-
able AI insights and fail-safe decision support for advanced diabetes management.
