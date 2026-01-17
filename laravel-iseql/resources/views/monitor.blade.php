@extends('layouts.master')

@section('content')
    <div class="container-fluid py-4 bg-gray-100">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="mb-1 fw-bold text-dark">🩺 Live Monitor</h2>
                <p class="text-muted mb-0">Real-time connection with Dexcom Cloud & AI Forecasting</p>
            </div>
            <div class="text-end">
                <button id="manualRefreshBtn" class="btn btn-primary btn-sm rounded-pill shadow-sm px-3 mb-2">
                    <i class="fas fa-sync-alt me-1"></i> Refresh Now
                </button>
                <br>
                <span class="badge bg-warning text-dark px-3 py-2 rounded-pill shadow-sm" id="connectionStatus">
                    <i class="fas fa-sync fa-spin me-1"></i> Initializing...
                </span>
                <div class="text-muted small mt-1" id="lastUpdate">Waiting for data...</div>
            </div>
        </div>

        <div class="row g-4 mb-4">
            <div class="col-lg-4 col-md-6">
                <div class="card border-0 shadow-sm h-100 overflow-hidden">
                    <div class="card-header bg-white border-0 pt-3 pb-0">
                        <h6 class="text-uppercase text-muted fw-bold small ls-1">Current Glucose</h6>
                    </div>
                    <div class="card-body text-center d-flex flex-column justify-content-center py-4">
                        <div class="d-flex justify-content-center align-items-baseline">
                            <span class="display-3 fw-bolder text-primary" id="currentValue">---</span>
                            <span class="fs-5 text-muted ms-2 fw-medium">mg/dL</span>
                        </div>
                        <div class="mt-3 d-flex justify-content-center align-items-center gap-2">
                            <span class="fs-2 lh-1" id="trendArrow">--</span>
                            <span class="badge rounded-pill bg-light text-dark border px-3 py-2" id="trendDesc">
                            Analyzing...
                        </span>
                        </div>
                    </div>
                    <div class="card-footer bg-primary bg-opacity-10 border-0 p-2 text-center">
                        <small class="text-primary fw-bold">Live Sensor Data</small>
                    </div>
                </div>
            </div>

            <div class="col-lg-4 col-md-6">
                <div class="card border-0 shadow-sm h-100" id="riskCard">
                    <div class="card-header bg-transparent border-0 pt-3 pb-0">
                        <h6 class="text-uppercase text-white-50 fw-bold small ls-1 mb-0" id="riskTitle">Risk Analysis</h6>
                    </div>
                    <div class="card-body text-center d-flex flex-column justify-content-center">
                        <div class="mb-2 fs-1 text-white" id="riskIcon">
                            <i class="fas fa-shield-alt"></i>
                        </div>
                        <h2 class="fw-bold text-white mb-1" id="riskAnalysis">Safe</h2>
                        <p class="text-white-50 small mb-0">Based on LSTM predictive model</p>
                    </div>
                </div>
            </div>

            <div class="col-lg-4 col-md-12">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white border-bottom pt-3 pb-2 d-flex justify-content-between align-items-center">
                        <h6 class="text-uppercase text-muted fw-bold small ls-1 mb-0">AI Forecast (60 min)</h6>
                        <span class="badge bg-primary rounded-pill">12 steps</span>
                    </div>
                    <div class="card-body p-0">
                        <div class="list-group list-group-flush" id="forecastList"
                             style="max-height: 220px; overflow-y: auto;">
                            <div class="text-center py-5 text-muted">
                                <div class="spinner-border spinner-border-sm text-primary mb-2" role="status"></div>
                                <br>Loading model...
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between">
                            <h5 class="card-title fw-bold mb-4">Trend & Forecast Verification</h5>
                            <small class="text-muted"><i class="fas fa-info-circle"></i> The Grey line shows what AI predicted <strong>in the past</strong> for this moment.</small>
                        </div>
                        <div style="height: 400px; width: 100%;">
                            <canvas id="liveChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <input type="hidden" id="raw_user" value="{{ request('username') }}">
    <input type="hidden" id="raw_pass" value="{{ request('password') }}">
    <input type="hidden" id="raw_pid" value="{{ request('patient_id') }}">

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <style>
        /* Custom Scrollbar */
        #forecastList::-webkit-scrollbar { width: 6px; }
        #forecastList::-webkit-scrollbar-track { background: #f1f1f1; }
        #forecastList::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
        #forecastList::-webkit-scrollbar-thumb:hover { background: #94a3b8; }

        .ls-1 { letter-spacing: 1px; }
        .bg-gray-100 { background-color: #f8f9fa; }
        .pulse-animation { animation: pulse-green 2s infinite; }
        @keyframes pulse-green {
            0% { box-shadow: 0 0 0 0 rgba(25, 135, 84, 0.7); }
            70% { box-shadow: 0 0 0 10px rgba(25, 135, 84, 0); }
            100% { box-shadow: 0 0 0 0 rgba(25, 135, 84, 0); }
        }
    </style>

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const user = document.getElementById('raw_user').value;
            const pass = document.getElementById('raw_pass').value;
            const pid = document.getElementById('raw_pid').value;
            const refreshBtn = document.getElementById('manualRefreshBtn');

            // --- LOCAL STATE (Buffers) ---
            let sessionHistoryLabels = [];
            let sessionHistoryData = [];      // Linea Blu (Reale)

            // --- LOGICA GHOST TRACE ---
            // Qui salviamo cosa l'AI aveva predetto per QUESTO momento nel PASSATO.
            let sessionGhostData = [];        // Linea Grigia (Cosa pensava l'AI)
            let pendingForecastValue = null;  // Il valore previsto per il "prossimo step"

            if (!user || !pass) {
                alert("Missing credentials.");
                return;
            }

            // --- CHART SETUP ---
            const ctx = document.getElementById('liveChart').getContext('2d');
            let gradientReal = ctx.createLinearGradient(0, 0, 0, 400);
            gradientReal.addColorStop(0, 'rgba(13, 110, 253, 0.5)');
            gradientReal.addColorStop(1, 'rgba(13, 110, 253, 0.0)');

            const liveChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: [],
                    datasets: [
                        {
                            label: 'Real Data',
                            data: [],
                            borderColor: '#0d6efd', // BLU
                            backgroundColor: gradientReal,
                            borderWidth: 3,
                            pointRadius: 4,
                            tension: 0.4,
                            fill: true,
                            order: 1
                        },
                        {
                            label: 'AI Forecast (Future)',
                            data: [],
                            borderColor: '#fd7e14', // ARANCIONE
                            borderWidth: 2,
                            borderDash: [5, 5],
                            pointRadius: 0,
                            tension: 0.4,
                            fill: false,
                            order: 2
                        },
                        {
                            label: 'AI (Past Prediction)',
                            data: [],
                            borderColor: '#adb5bd', // GRIGIO
                            backgroundColor: 'rgba(173, 181, 189, 0.5)',
                            borderWidth: 2,
                            pointRadius: 3,
                            pointStyle: 'crossRot', // Una X per mostrare l'errore
                            showLine: false, // Mostra solo i punti, non la linea
                            order: 0
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: { mode: 'index', intersect: false },
                    plugins: {
                        legend: { position: 'top' },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    let label = context.dataset.label || '';
                                    if (label) {
                                        label += ': ';
                                    }
                                    if (context.parsed.y !== null) {
                                        label += context.parsed.y + ' mg/dL';
                                    }
                                    return label;
                                }
                            }
                        }
                    },
                    scales: {
                        y: { suggestedMin: 40, suggestedMax: 250 },
                        x: { grid: { display: false } }
                    }
                }
            });

            refreshBtn.addEventListener('click', function() { fetchDexcomData(); });

            async function fetchDexcomData() {
                // UI Loading State
                const statusBadge = document.getElementById('connectionStatus');
                statusBadge.className = 'badge bg-warning text-dark px-3 py-2 rounded-pill shadow-sm';
                statusBadge.innerHTML = '<i class="fas fa-satellite-dish fa-spin me-1"></i> Updating...';
                refreshBtn.disabled = true;
                refreshBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Loading...';

                try {
                    const response = await fetch('http://127.0.0.1:5000/dexcom-live', {
                        method: 'POST',
                        headers: {'Content-Type': 'application/json'},
                        body: JSON.stringify({username: user, password: pass, patient_id: pid})
                    });

                    if (!response.ok) throw new Error("API Error");
                    const data = await response.json();

                    if (data.error) {
                        alert(data.error);
                        return;
                    }

                    updateDashboard(data);

                } catch (error) {
                    console.error(error);
                    statusBadge.className = 'badge bg-danger px-3 py-2 rounded-pill';
                    statusBadge.innerHTML = '<i class="fas fa-times-circle me-1"></i> Connection Lost';
                } finally {
                    refreshBtn.disabled = false;
                    refreshBtn.innerHTML = '<i class="fas fa-sync-alt me-1"></i> Refresh Now';
                }
            }

            function updateDashboard(data) {
                const now = new Date();
                const timeStr = now.toLocaleTimeString([], {hour: '2-digit', minute: '2-digit'});

                // 1. UI Updates (Badge, Cards, Lists...)
                document.getElementById('connectionStatus').className = 'badge bg-success px-3 py-2 rounded-pill shadow-sm pulse-animation';
                document.getElementById('connectionStatus').innerHTML = '<i class="fas fa-wifi me-1"></i> Live Connected';
                document.getElementById('lastUpdate').innerText = 'Last update: ' + timeStr;
                document.getElementById('currentValue').innerText = data.current_value;
                document.getElementById('trendDesc').innerText = data.trend_desc;

                // Risk Colors logic... (omitted for brevity, keep your existing logic here)
                const riskEl = document.getElementById('riskAnalysis');
                const riskCard = document.getElementById('riskCard');
                const riskIcon = document.getElementById('riskIcon');
                riskEl.innerText = data.risk_analysis.split(':')[0];
                riskCard.className = "card border-0 shadow-sm h-100 text-white transition-all";

                if (data.risk_analysis.includes("DANGER")) {
                    riskCard.classList.add("bg-danger", "bg-gradient");
                    riskIcon.innerHTML = '<i class="fas fa-exclamation-circle fa-beat"></i>';
                } else if (data.risk_analysis.includes("WARNING")) {
                    riskCard.classList.add("bg-warning", "bg-gradient");
                    riskCard.classList.remove("text-white"); riskCard.classList.add("text-dark");
                    riskIcon.innerHTML = '<i class="fas fa-exclamation-triangle"></i>';
                } else {
                    riskCard.classList.add("bg-success", "bg-gradient");
                    riskIcon.innerHTML = '<i class="fas fa-check-circle"></i>';
                }

                // Update List
                const listEl = document.getElementById('forecastList');
                listEl.innerHTML = '';
                data.forecast_values.forEach((val, idx) => {
                    const time = data.forecast_times[idx];
                    const li = document.createElement('a');
                    li.className = 'list-group-item list-group-item-action d-flex justify-content-between align-items-center py-2 px-3 border-0 border-bottom';
                    let badgeClass = (val < 70) ? 'bg-danger' : (val > 180 ? 'bg-warning text-dark' : 'bg-success');
                    li.innerHTML = `<div><i class="far fa-clock text-muted me-2"></i><span class="fw-bold text-dark">${time}</span></div><span class="badge ${badgeClass} rounded-pill px-3">${val} mg/dL</span>`;
                    listEl.appendChild(li);
                });

                // --- 2. LOGICA AGGIORNAMENTO STORICO (Cruciale) ---

                const lastTimestamp = sessionHistoryLabels[sessionHistoryLabels.length - 1];

                // Se è arrivato un nuovo dato temporale (o è il primo)
                if (sessionHistoryLabels.length === 0 || lastTimestamp !== data.timestamp) {

                    // A. Salva Tempo e Dato Reale
                    sessionHistoryLabels.push(data.timestamp);
                    sessionHistoryData.push(data.current_value);

                    // B. Salva il "Ghost Data" (La previsione fatta nel passato per ADESSO)
                    // Se avevamo una previsione pendente (fatta 5 min fa), ora la scriviamo nella storia
                    if (pendingForecastValue !== null) {
                        sessionGhostData.push(pendingForecastValue);
                    } else {
                        // Primo giro: non c'è storico previsioni
                        sessionGhostData.push(null);
                    }

                    // C. Prepara la previsione per il PROSSIMO giro
                    // data.forecast_values[0] è la previsione a +5 minuti da adesso
                    pendingForecastValue = data.forecast_values[0];
                }

                // Mantieni la finestra pulita (Max 24 punti = 2 ore)
                if (sessionHistoryLabels.length > 24) {
                    sessionHistoryLabels.shift();
                    sessionHistoryData.shift();
                    sessionGhostData.shift();
                }

                // --- 3. COSTRUZIONE DATASET GRAFICO ---

                // Uniamo etichette passate + future
                const allLabels = [...sessionHistoryLabels, ...data.forecast_times];

                // Dataset 1: REALE (Storia + Null nel futuro)
                const realDataset = [...sessionHistoryData];
                for (let i = 0; i < data.forecast_values.length; i++) realDataset.push(null);

                // Dataset 2: FUTURO (Null nel passato + Ultimo Reale + Previsioni)
                const forecastDataset = Array(sessionHistoryData.length - 1).fill(null);
                forecastDataset.push(sessionHistoryData[sessionHistoryData.length - 1]); // Punto di aggancio
                data.forecast_values.forEach(v => forecastDataset.push(v));

                // Dataset 3: GHOST (Storia delle previsioni passate + Null nel futuro)
                // Mostra le X grigie dove l'AI pensava saremmo stati
                const ghostDataset = [...sessionGhostData];
                for (let i = 0; i < data.forecast_values.length; i++) ghostDataset.push(null);

                liveChart.data.labels = allLabels;
                liveChart.data.datasets[0].data = realDataset;   // Linea Blu
                liveChart.data.datasets[1].data = forecastDataset; // Linea Arancione
                liveChart.data.datasets[2].data = ghostDataset;    // X Grigie

                liveChart.update();
            }

            fetchDexcomData();
            setInterval(fetchDexcomData, 420000);
        });
    </script>
@endsection
