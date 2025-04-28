@extends('layouts.master')

@section('title')
    Patient Details
@endsection

@section('content')

    @component('components.breadcrumb')
        @slot('li_1')
            Patient
        @endslot
        @slot('title')
            Patient Details
        @endslot
    @endcomponent

    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css"/>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css"/>
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.2.2/css/buttons.dataTables.min.css">

    <style>
        /* Stile per il contenitore dell'input per centrarlo */
        .daterange-container {
            display: flex;
            justify-content: center; /* Allinea orizzontalmente al centro */
            align-items: center; /* Allinea verticalmente al centro */
            margin: 0 auto; /* Centra il contenitore all'interno del suo genitore */
            max-width: 400px; /* Imposta una larghezza massima per l'input */
        }

        /* Stile per l'input del daterangepicker */
        input[name="daterange"] {
            width: 100%; /* Occupa tutta la larghezza del contenitore */
            max-width: 300px; /* Imposta una larghezza massima per l'input */
            padding: 0.5rem; /* Padding interno ridotto per ridurre le dimensioni */
            border: 1px solid #ced4da; /* Colore del bordo grigio chiaro */
            border-radius: 5px; /* Arrotonda gli angoli del box */
            font-size: 0.875rem; /* Dimensione del testo più piccola */
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1); /* Ombra leggera per effetto sollevato */
            transition: border-color 0.3s ease, box-shadow 0.3s ease; /* Transizione per colore del bordo e ombra */
            background-color: #ffffff; /* Colore di sfondo bianco */
        }

        /* Stile per l'input del daterangepicker quando è in focus */
        input[name="daterange"]:focus {
            border-color: #007bff; /* Colore del bordo blu quando è attivo */
            box-shadow: 0 0 4px rgba(0, 123, 255, 0.5); /* Ombra blu attorno all'input quando è attivo */
            outline: none; /* Rimuove l'outline predefinito */
        }

        /* Contenitore del bottone per centrarlo */
        .form-container {
            display: flex;
            justify-content: center; /* Allinea orizzontalmente al centro */
            align-items: center; /* Allinea verticalmente al centro se necessario */
        }

        /* Stile per il bottone */
        .btn-center {
            display: inline-block; /* Mantiene il bottone in linea */
            margin-top: 1rem; /* Spazio sopra il bottone, se necessario */
        }

        .custom-close {
            color: white;
            background-color: #850404;
            border-color: #850404;
        }

        .bg-purple {
            background-color: #800080; /* Purple color */
        }

        .bg-heavenly {
            background-color: rgba(75, 192, 192, 0.2); /* Colore Heavenely (un blu verde chiaro) */
        }

        .bg-heavenly-dark {
            background-color: rgb(50, 150, 150); /* Un blu-verde più scuro */
        }


    </style>
    <div class="row">
        <div class="col-lg-12">
            <!-- Card for Patient Info -->
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h4 class="card-title mb-0">Patient: {{$patient->name." ".$patient->surname}}</h4>

                        <div class="dropdown align-right"
                             style="margin-bottom: 15px; display: flex; justify-content: flex-end;">
                            <button class="btn btn-primary dropdown-toggle"
                                    type="button"
                                    id="bs-download-pdf-modal"
                                    data-bs-toggle="dropdown"
                                    aria-expanded="false">
                                <i class="bx bx-cog font-size-18"></i>
                            </button>

                            <div class="dropdown-menu p-3"
                                 aria-labelledby="bs-download-pdf-modal"
                                 style="max-height: 300px; overflow-y: auto; min-width: 250px;">
                                <div class="text-center mb-2">
                                    <h5 class="text-muted" style="font-size: 0.75rem;">Select section</h5>
                                </div>
                                <form id="download.pdf" method="post"
                                      action="{{ route('download.pdf', ['patientId' => $patient->id, 'csvId' => $csv->id] + (request('start_date') ? ['start_date' => request('start_date')] : []) + (request('end_date') ? ['end_date' => request('end_date')] : [])) }}"
                                      novalidate>
                                    @csrf
                                    <input type="hidden" name="glycemicSwingsChart" id="glycemicSwingsChartImage">
                                    <input type="hidden" name="tooLongGlucoseAnomaliesChart" id="tooLongGlucoseAnomaliesChartImage">
                                    <input type="hidden" name="tooFrequentGlucoseAnomaliesChart" id="tooFrequentGlucoseAnomaliesChartImage">
                                    <input type="hidden" name="tooFrequentTimeSwingsDurationChart" id="tooFrequentTimeSwingsDurationChartImage">
                                    <input type="hidden" name="tooFrequentTimeSwingsFrequencyChart" id="tooFrequentTimeSwingsFrequencyChartImage">
                                    <input type="hidden" name="timeSwingTooLongGlucoseAnomaliesChart" id="timeSwingTooLongGlucoseAnomaliesChartImage">

                                    <div class="row">
                                        <div class="col-12">
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input" name="detail" id="column16" checked>
                                                <label class="form-check-label" for="column16">Detail</label>
                                            </div>
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input" name="summary" id="column17" checked>
                                                <label class="form-check-label" for="column17">Summary</label>
                                            </div>
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input" name="time_swing" id="column18" checked>
                                                <label class="form-check-label" for="column18">Time Swing</label>
                                            </div>
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input" name="too_long" id="column19" checked>
                                                <label class="form-check-label" for="column19">Too Long Glucose Anomalies</label>
                                            </div>
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input" name="too_frequent" id="column20" checked>
                                                <label class="form-check-label" for="column20">Too Frequent Glucose Anomalies</label>
                                            </div>
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input" name="too_frequent_time_swing" id="column21" checked>
                                                <label class="form-check-label" for="column21">Too Frequent Time Swings</label>
                                            </div>
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input" name="time_swing_too_long" id="column22" checked>
                                                <label class="form-check-label" for="column22">Time Swing With Too Long Glucose Anomalies</label>
                                            </div>
                                        </div>
                                        <div class="col-12 text-center mt-3">
                                            <button type="submit" class="btn btn-success">Salva</button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table align-middle table-nowrap">
                            <tbody>
                            <tr>
                                <td><strong>Name:</strong></td>
                                <td>{{ $patient->name ?? 'Not Specified' }}</td>
                            </tr>
                            <tr>
                                <td><strong>Surname:</strong></td>
                                <td>{{ $patient->surname ?? 'Not Specified' }}</td>
                            </tr>
                            <tr>
                                <td><strong>Email:</strong></td>
                                <td>{{ $patient->email ?? 'Not Specified' }}</td>
                            </tr>
                            <tr>
                                <td><strong>Date of Birth:</strong></td>
                                <td>{{ $patient->birth ? \Carbon\Carbon::parse($patient->birth)->format('Y/m/d') : 'Not Specified' }}</td>
                            </tr>
                            <tr>
                                <td><strong>Address:</strong></td>
                                <td>{{ $patient->address ?: 'Not Specified' }}</td>
                            </tr>
                            <tr>
                                <td><strong>Phone Number:</strong></td>
                                <td>{{ $patient->telephone_number ?? 'Not Specified' }}</td>
                            </tr>
                            <tr>
                                <td><strong>Gender:</strong></td>
                                <td>{{ $patient->gender ?? 'Not Specified' }}</td>
                            </tr>
                            <tr>
                                <td><strong>Analysis date:</strong></td>
                                <td>
                                    From {{ $data['start_time'] ? \Carbon\Carbon::parse($data['start_time'])->format('Y/m/d') : 'Not Specified' }}
                                    to {{ $data['end_time'] ? \Carbon\Carbon::parse($data['end_time'])->format('Y/m/d') : 'Not Specified' }}
                                    @php
                                        $startTime = $data['start_time'] ? \Carbon\Carbon::parse($data['start_time'])->format('Y/m/d') : null;
                                        $endTime = $data['end_time'] ? \Carbon\Carbon::parse($data['end_time'])->format('Y/m/d') : null;
                                        $startDateFormatted = isset($startDate) ? \Carbon\Carbon::parse($startDate)->format('Y/m/d') : null;
                                        $endDateFormatted = isset($endDate) ? \Carbon\Carbon::parse($endDate)->format('Y/m/d') : null;
                                    @endphp
                                    @if(($startDateFormatted !== $startTime || $endDateFormatted !== $endTime) && $startDateFormatted && $endDateFormatted)
                                        <br>
                                        Filtered from {{ $startDateFormatted }} to {{ $endDateFormatted }}
                                    @endif
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-12">
            <div class="container mt-4" id="scroll-to-form"> <!-- Aggiungi un ID qui -->
                <div class="card">
                    <div class="card-body">
                        <h3 class="card-title mb-4 text-center" style="font-size: 1.3rem;">Filter Analysis</h3>
                        <form id="date-form"
                              action="{{ route('viewCsvRange', ['csvId' => $csv->id, 'patientId' => $patient->id]) }}"
                              method="GET">
                            <div class="row" id="range">
                                <div class="col-sm-6 mb-3 mb-sm-0 daterange-container">
                                    <input type="text" name="daterange" value=""/>
                                    <input type="hidden" id="start-date" name="start_date"/>
                                    <input type="hidden" id="end-date" name="end_date"/>
                                </div>
                            </div>
                            <!-- Submit Button -->
                            <div class="form-container mt-3">
                                @if(($startDateFormatted !== $startTime || $endDateFormatted !== $endTime) && $startDateFormatted && $endDateFormatted)
                                    <a href="{{ route('viewCsv', ['csvId' => $csv->id, 'patientId' => $patient->id]) }}"
                                       class="btn btn-secondary btn-center"
                                       style="background-color: #007bff; border-color: #007bff; color: white;">Reset
                                        Filter</a>
                                @endif
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-lg-12">
            <!-- Analysis Summary-->
            <div class="container mt-4">
                <div class="card">
                    <div class="card-body">
                        <h3 class="card-title mb-4 text-center" style="font-size: 1.3rem;">Analysis Summary</h3>
                        <div class="row">

                            <!-- Totals Card -->
                            <div class="col-md-6 mb-4">
                                <div class="card h-100">
                                    <div class="card-body">
                                        <h5 class="card-title">Totals</h5>
                                        <ul class="list-unstyled">
                                            <li class="mb-2"><strong>Extremely
                                                    High:</strong> {{ $data['totals_and_durations']['totals']['extremely_high'] ?? 'N/A' }}
                                            </li>
                                            <li class="mb-2"><strong>Extremely
                                                    Low:</strong> {{ $data['totals_and_durations']['totals']['extremely_low'] ?? 'N/A' }}
                                            </li>
                                            <li class="mb-2">
                                                <strong>High:</strong> {{ $data['totals_and_durations']['totals']['high'] ?? 'N/A' }}
                                            </li>
                                            <li class="mb-2">
                                                <strong>Low:</strong> {{ $data['totals_and_durations']['totals']['low'] ?? 'N/A' }}
                                            </li>
                                            <li class="mb-2">
                                                <strong>Normal:</strong> {{ $data['totals_and_durations']['totals']['normal'] ?? 'N/A' }}
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>

                            <!-- Percentage Breakdown Card -->
                            <div class="col-md-6 mb-4">
                                <div class="card h-100">
                                    <div class="card-body">
                                        <h5 class="card-title">Percentage Breakdown</h5>
                                        <ul class="list-unstyled">
                                            <li class="mb-2"><strong>Extremely
                                                    High:</strong> {{ number_format((float)($data['totals_and_durations']['percentages']['extremely_high'] ?? 0), 2) }}
                                                %
                                            </li>
                                            <li class="mb-2"><strong>Extremely
                                                    Low:</strong> {{ number_format((float)($data['totals_and_durations']['percentages']['extremely_low'] ?? 0), 2) }}
                                                %
                                            </li>
                                            <li class="mb-2">
                                                <strong>High:</strong> {{ number_format((float)($data['totals_and_durations']['percentages']['high'] ?? 0), 2) }}
                                                %
                                            </li>
                                            <li class="mb-2">
                                                <strong>Low:</strong> {{ number_format((float)($data['totals_and_durations']['percentages']['low'] ?? 0), 2) }}
                                                %
                                            </li>
                                            <li class="mb-2">
                                                <strong>Normal:</strong> {{ number_format((float)($data['totals_and_durations']['percentages']['normal'] ?? 0), 2) }}
                                                %
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>

                            <!-- Max Anomalous Day Card -->
                            <div class="col-md-6 mb-4">
                                <div class="card h-100">
                                    <div class="card-body">
                                        <h5 class="card-title">Max Anomalous Day</h5>
                                        <p>
                                            <strong>Date:</strong> {{ $data['totals_and_durations']['max_anomalous_day']['date'] ?? 'N/A' }}
                                        </p>
                                        <ul class="list-unstyled">
                                            <li class="mb-2"><strong>Extremely High
                                                    Count:</strong> {{ $data['totals_and_durations']['max_anomalous_day']['details']['Extremely High Count'] ?? 'N/A' }}
                                            </li>
                                            <li class="mb-2"><strong>Extremely Low
                                                    Count:</strong> {{ $data['totals_and_durations']['max_anomalous_day']['details']['Extremely Low Count'] ?? 'N/A' }}
                                            </li>
                                            <li class="mb-2"><strong>High
                                                    Count:</strong> {{ $data['totals_and_durations']['max_anomalous_day']['details']['High Count'] ?? 'N/A' }}
                                            </li>
                                            <li class="mb-2"><strong>Low
                                                    Count:</strong> {{ $data['totals_and_durations']['max_anomalous_day']['details']['Low Count'] ?? 'N/A' }}
                                            </li>
                                            <li class="mb-2"><strong>Total
                                                    Count:</strong> {{ $data['totals_and_durations']['max_anomalous_day']['details']['Total Count'] ?? 'N/A' }}
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>

                            <!-- Glycemic Swings Statistics Card -->
                            <div class="col-md-6 mb-4">
                                <div class="card h-100">
                                    <div class="card-body">
                                        <h5 class="card-title">Time Swings Statistics</h5>
                                        <ul class="list-unstyled">
                                            <li class="mb-2"><strong>Percentage of Time Swing With Too Long Glucose
                                                    Anomalies:</strong> {{ number_format((float)($data['totals_and_durations']['time_swings_stats']['percentage_time_swing_too_long'] ?? 0), 2) }}
                                                %
                                            </li>
                                            <li class="mb-2"><strong>Percentage of Too frequent Time
                                                    Swings:</strong> {{ number_format((float)($data['totals_and_durations']['time_swings_stats']['percentage_too_frequent_time_swings'] ?? 0), 2) }}
                                                %
                                            </li>
                                            <li class="mb-2"><strong>Total Time
                                                    Swing:</strong> {{ $data['totals_and_durations']['time_swings_stats']['total_time_swings'] ?? 'N/A' }}
                                            </li>
                                            <li class="mb-2"><strong>Total Time Swing With Too Long Glucose
                                                    Anomalies:</strong> {{ $data['totals_and_durations']['time_swings_stats']['total_time_swing_too_long'] ?? 'N/A' }}
                                            </li>
                                            <li class="mb-2"><strong>Total Day of Too Frequent Time
                                                    Swings:</strong> {{ $data['totals_and_durations']['time_swings_stats']['total_too_frequent_time_swings'] ?? 'N/A' }}
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mt-4">
                <div class="card-body">
                    <h4 class="card-title mb-3 text-center" style="font-size: 1.3rem;">Analysis Results</h4>

                    <!-- Glycemic Swings Card -->
                    <div class="card mb-4">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <h5 class="card-title">Time Swing</h5>
                                <button class="btn btn-primary" data-bs-toggle="modal"
                                        data-bs-target="#glycemicSwingsModal">
                                    View Chart
                                </button>
                            </div>
                            <br>
                            @if(isset($data['time_swing']) && count($data['time_swing']) > 0)
                                <div class="table-responsive">
                                    <table class="table table-bordered datatable-glycemic-swings" style="width: 100%">
                                        <thead>
                                        <tr>
                                            <th>Day</th>
                                            <th>First Event</th>
                                            <th>Second Event</th>
                                            <th>Duration Time Swing (h)</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @foreach ($data['time_swing'] as $swing)
                                            <tr>
                                                <td>{{ $swing['day'] ?? 'N/A' }}</td>
                                                <td>{{ $swing['first_event'] ?? 'N/A' }}</td>
                                                <td>{{ $swing['second_event'] ?? 'N/A' }}</td>
                                                <td>{{ $swing['duration_time_swing'] ?? 'N/A' }}</td>
                                            </tr>
                                        @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <p class="text-muted">Nessun dato trovato per time swings.</p>
                            @endif
                        </div>
                    </div>
                    <div class="modal fade" id="glycemicSwingsModal" tabindex="-1"
                         aria-labelledby="glycemicSwingsModalLabel" aria-hidden="true">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="glycemicSwingsModalLabel">Time Swing Chart</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                            aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="mb-3">
                                        <strong>Legend:</strong>
                                        <span class="badge bg-heavenly text-dark">Time Swing (Max: 2h)</span>
                                    </div>
                                </div>
                                <div class="modal-body">
                                    <!-- Canvas where the Chart.js graph will be rendered -->
                                    <canvas id="glycemicSwingsChart" width="400" height="200"></canvas>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>


                <!-- Swings Followed By Anomalous Duration Card -->
                <div class="card mb-4">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="card-title">Too Long Glucose Anomalies</h5>
                            <button class="btn btn-primary" data-bs-toggle="modal"
                                    data-bs-target="#tooLongGlucoseAnomaliesModal">
                                View Chart
                            </button>
                        </div>
                        <br>
                        @if(isset($data['too_long_glucose_anomalies']) && count($data['too_long_glucose_anomalies']) > 0)
                            <div class="table-responsive">
                                <table id="datatable-too-long-duration"
                                       class="table table-bordered dt-responsive nowrap w-100">
                                    <thead>
                                    <tr>
                                        <th>Day</th>
                                        <th>Event</th>
                                        <th>Start Time</th>
                                        <th>End Time</th>
                                        <th>Durations (h)</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach ($data['too_long_glucose_anomalies'] as $swing)
                                        <tr>
                                            <td>{{ $swing['day'] ?? 'N/A' }}</td>
                                            <td>{{ $swing['event'] ?? 'N/A' }}</td>
                                            <td>{{ $swing['start_time'] ?? 'N/A' }}</td>
                                            <td>{{ $swing['end_time'] ?? 'N/A' }}</td>
                                            <td>{{ $swing['duration'] ?? 'N/A' }}</td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <p class="text-muted">Nessun dato trovato per swings followed by anomalous duration.</p>
                        @endif
                    </div>
                </div>
                <div class="modal fade" id="tooLongGlucoseAnomaliesModal" tabindex="-1"
                     aria-labelledby="tooLongGlucoseAnomaliesModal" aria-hidden="true">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="tooLongGlucoseAnomaliesModal">Too Long Glucose Anomalies
                                    Chart</h5>

                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                        aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <div class="mb-3">
                                    <strong>Legend:</strong>
                                    <span class="badge bg-danger">High (Min: 1h 30m)</span>
                                    <span class="badge bg-primary">Low (Min: 30m)</span>
                                    <span class="badge bg-warning text-dark">Extremely High (Min: 45m)</span>
                                    <span class="badge bg-purple text-white">Extremely Low (Min: 10m)</span>
                                </div>
                            </div>
                            <div class="modal-body">
                                <!-- Canvas where the Chart.js graph will be rendered -->
                                <canvas id="tooLongGlucoseAnomaliesChart" width="400" height="200"></canvas>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close
                                </button>
                            </div>
                        </div>
                    </div>
                </div>


                <!-- Anomalous Frequency Card -->
                <div class="card mb-4">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="card-title">Too Frequent Glucose Anomalies</h5>
                            <button class="btn btn-primary" data-bs-toggle="modal"
                                    data-bs-target="#tooFrequentGlucoseAnomaliesModal">
                                View Chart
                            </button>
                        </div>
                        <br>
                        @if(isset($data['too_frequent_glucose_anomalies']) && count($data['too_frequent_glucose_anomalies']) > 0)
                            <div class="table-responsive">
                                <table id="datatable-anomalous-frequency"
                                       class="table table-bordered dt-responsive nowrap w-100">
                                    <thead>
                                    <tr>
                                        <th>Day</th>
                                        <th>High Count</th>
                                        <th>Low Count</th>
                                        <th>Extremely High Count</th>
                                        <th>Extremely Low Count</th>
                                        <th>Total Count</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach ($data['too_frequent_glucose_anomalies'] as $frequency)
                                        <tr>
                                            <td>{{ $frequency['day'] ?? 'N/A' }}</td>
                                            <td>{{ $frequency['high_count'] ?? 'N/A' }}</td>
                                            <td>{{ $frequency['low_count'] ?? 'N/A' }}</td>
                                            <td>{{ $frequency['extremely_high_count'] ?? 'N/A' }}</td>
                                            <td>{{ $frequency['extremely_low_count'] ?? 'N/A' }}</td>
                                            <td>{{ $frequency['total_count'] ?? 'N/A' }}</td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <p class="text-muted">Nessun dato trovato per anomalous frequency.</p>
                        @endif
                    </div>
                </div>
                <div class="modal fade" id="tooFrequentGlucoseAnomaliesModal" tabindex="-1"
                     aria-labelledby="tooFrequentGlucoseAnomaliesModal" aria-hidden="true">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="tooFrequentGlucoseAnomaliesModal">Too Frequent Glucose
                                    Anomalies
                                    Chart</h5>

                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                        aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <div class="mb-3">
                                    <strong>Legend:</strong>
                                    <span class="badge bg-danger">High (Min: 3 times)</span>
                                    <span class="badge bg-primary">Low (Min: 3 times)</span>
                                    <span class="badge bg-warning text-dark">Extremely High (1 time)</span>
                                    <span class="badge bg-purple text-white">Extremely Low (1 time)</span>
                                </div>
                            </div>
                            <div class="modal-body">
                                <!-- Canvas where the Chart.js graph will be rendered -->
                                <canvas id="tooFrequentGlucoseAnomaliesChart" width="400" height="200"></canvas>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close
                                </button>
                            </div>
                        </div>
                    </div>
                </div>


                <!-- Swings Followed By Anomalous Frequency Card -->
                <div class="card mb-4">
                    <div class="card-body"><div class="d-flex justify-content-between align-items-center">
                            <h5 class="card-title">Too Frequent Time Swings</h5>
                            <div>
                                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#tooFrequentTimeSwingsDurationModal">
                                    View Duration Chart
                                </button>
                                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#tooFrequentTimeSwingsFrequencyModal">
                                    View Frequency Chart
                                </button>
                            </div>
                        </div>

                        <br>
                        @if(isset($data['too_frequent_time_swings']) && count($data['too_frequent_time_swings']) > 0)
                            <div class="table-responsive">
                                <table id="datatable-swings-followed-by-frequency"
                                       class="table table-bordered dt-responsive nowrap w-100">
                                    <thead>
                                    <tr>
                                        <th>Day</th>
                                        <th>First Swing Event</th>
                                        <th>Second Swing Event</th>
                                        <th>Duration Time Swing (h)</th>
                                        <th>Frequency</th>
                                        <th>Action</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach ($data['too_frequent_time_swings'] as $swing)
                                        <tr>
                                            <td>{{ $swing['Events'][0]['Day'] ?? 'N/A' }}</td>
                                            <td>{{ $swing['Events'][0]['First event'] ?? 'N/A' }}</td>
                                            <td>{{ $swing['Events'][0]['Second event'] ?? 'N/A' }}</td>
                                            <td>{{ $swing['Events'][0]['Duration time swing'] ?? 'N/A' }}</td>
                                            <td>{{ $swing['Number of Time Swings'] ?? 'N/A' }}</td>
                                            <td>
                                                <!-- Bottone per aprire il modale -->
                                                <button type="button" class="btn btn-info btn-sm" data-toggle="modal"
                                                        data-target="#modal-{{ $loop->index }}">
                                                    View Details
                                                </button>

                                                <!-- Modale per visualizzare i dettagli -->
                                                <div class="modal fade-" id="modal-{{ $loop->index }}" tabindex="-1"
                                                     role="dialog" aria-labelledby="modalLabel-{{ $loop->index }}"
                                                     aria-hidden="true">
                                                    <div class="modal-dialog modal-lg" role="document">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title"
                                                                    id="modalLabel-{{ $loop->index }}">Time Swings
                                                                    Details</h5>
                                                                <button type="button" class="close" data-dismiss="modal"
                                                                        aria-label="Close">
                                                                    <span aria-hidden="true">&times;</span>
                                                                </button>
                                                            </div>
                                                            <div class="modal-body">
                                                                <!-- Tabella con i dettagli dei time swings -->
                                                                <table id="datatable-too-frequent_time_swings_details-{{ $loop->index }}"
                                                                       class="table table-bordered dt-responsive nowrap w-100">                                                                    <thead>
                                                                    <tr>
                                                                        <th>Day</th>
                                                                        <th>First event</th>
                                                                        <th>Second event</th>
                                                                        <th>Duration time swing</th>
                                                                    </tr>
                                                                    </thead>
                                                                    <tbody>
                                                                    @foreach ($swing['Events'] as $event)
                                                                        <tr>
                                                                            <td>{{ $event['Day'] ?? 'N/A' }}</td>
                                                                            <td>{{ $event['First event'] ?? 'N/A' }}</td>
                                                                            <td>{{ $event['Second event'] ?? 'N/A' }}</td>
                                                                            <td>{{ $event['Duration time swing'] ?? 'N/A' }}</td>
                                                                        </tr>
                                                                    @endforeach
                                                                    </tbody>
                                                                </table>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button"
                                                                        class="btn btn-secondary"
                                                                        data-dismiss="modal">Close
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <p class="text-muted">Nessun dato trovato per time swings too frequent.</p>
                        @endif
                    </div>
                </div>
                <div class="modal fade" id="tooFrequentTimeSwingsDurationModal" tabindex="-1" aria-labelledby="tooFrequentTimeSwingsDurationModal" aria-hidden="true">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Time Swing Duration Chart</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <canvas id="tooFrequentTimeSwingsDurationChart" width="400" height="200"></canvas>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Modal per Frequenza -->
                <div class="modal fade" id="tooFrequentTimeSwingsFrequencyModal" tabindex="-1" aria-labelledby="tooFrequentTimeSwingsFrequencyModal" aria-hidden="true">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Time Swing Frequency Chart</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <canvas id="tooFrequentTimeSwingsFrequencyChart" width="400" height="200"></canvas>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Swings Followed By Anomalous Duration Card -->
                <div class="card mb-4">
                    <div class="card-body">
                        <h5 class="card-title"></h5>
                        <div class="d-flex justify-content-between align-items-center">

                            <h5 class="card-title">Time Swing With Too Long Glucose Anomalies</h5>
                            <button class="btn btn-primary" data-bs-toggle="modal"
                                    data-bs-target="#timeSwingTooLongGlucoseAnomaliesModal">
                                View Chart
                            </button>
                        </div>
                        <br>
                        @if(isset($data['time_swing_with_too_long_glucose_anomalies']) && count($data['time_swing_with_too_long_glucose_anomalies']) > 0)
                            <div class="table-responsive">
                                <table id="datatable-swings-followed-by-duration"
                                       class="table table-bordered dt-responsive nowrap w-100">
                                    <thead>
                                    <tr>
                                        <th>Day</th>
                                        <th>First Swing Event</th>
                                        <th>Second Swing Event</th>
                                        <th>Duration Time Swing (h)</th>
                                        <th>Duration(h)</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach ($data['time_swing_with_too_long_glucose_anomalies'] as $swing)
                                        <tr>
                                            <td>{{ $swing['day'] ?? 'N/A' }}</td>
                                            <td>{{ $swing['first_event'] ?? 'N/A' }}</td>
                                            <td>{{ $swing['second_event'] ?? 'N/A' }}</td>
                                            <td>{{ $swing['duration_time_swing'] ?? 'N/A' }}</td>
                                            <td>{{ $swing['anomalous_durations'] ?? 'N/A' }}</td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <p class="text-muted">Nessun dato trovato per swings followed by anomalous duration.</p>
                        @endif
                    </div>
                </div>
                <div class="modal fade" id="timeSwingTooLongGlucoseAnomaliesModal" tabindex="-1"
                     aria-labelledby="timeSwingTooLongGlucoseAnomaliesModal" aria-hidden="true">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="timeSwingTooLongGlucoseAnomaliesModal">Time Swing with Too
                                    Long Glucose Anomalies
                                    Chart</h5>

                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                        aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <div class="mb-3">
                                    <strong>Legend:</strong>
                                    <span class="badge bg-heavenly text-dark">Time Swing (Max: 2h)</span>
                                    <span class="badge bg-danger">High (Min: 1h 30m)</span>
                                    <span class="badge bg-primary">Low (Min: 30m)</span>
                                    <span class="badge bg-warning text-dark">Extremely High (Min: 45m)</span>
                                    <span class="badge bg-purple text-white">Extremely Low (Min: 10m)</span>
                                </div>
                            </div>
                            <div class="modal-body">
                                <!-- Canvas where the Chart.js graph will be rendered -->
                                <canvas id="timeSwingTooLongGlucoseAnomaliesChart" width="400" height="200"></canvas>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close
                                </button>
                            </div>
                        </div>
                    </div>
                </div>


            </div>
        </div>
    </div>
    </div>


    <script>
        document.addEventListener("DOMContentLoaded", function () {
            let swingData = [
                    @foreach ($data['time_swing'] as $swing)
                {
                    day: "{{ \Carbon\Carbon::parse($swing['day'])->format('d/m/Y') }}",  // Formattazione della data
                    duration: "{{ \Carbon\Carbon::parse($swing['duration_time_swing'])->format('H:i') }}", // Durata in formato HH:mm
                    time_swing_type: "{{ $swing['first_event'] }} to {{ $swing['second_event'] }}" // Tipo di evento (high-low, low-high)
                },
                @endforeach
            ];

            // Convertire HH:mm in minuti per il grafico
            let durationsInMinutes = swingData.map(swing => {
                let [hours, minutes] = swing.duration.split(":").map(Number);
                return hours * 60 + minutes; // Convertiamo tutto in minuti
            });

            let days = swingData.map(swing => swing.day);
            let timeSwingTypes = swingData.map(swing => swing.time_swing_type);

            let ctx = document.getElementById('glycemicSwingsChart').getContext('2d');
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: days,
                    datasets: [{
                        label: 'Duration of Time Swings (HH:mm)',
                        data: durationsInMinutes, // Passiamo i valori in minuti
                        backgroundColor: 'rgba(75, 192, 192, 0.2)',
                        borderColor: 'rgba(75, 192, 192, 1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: 'Duration (HH:mm)'
                            },
                            ticks: {
                                callback: function (value) {
                                    let hours = Math.floor(value / 60);
                                    let minutes = value % 60;
                                    return `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}`; // Mostra in HH:mm con due cifre
                                }
                            }
                        },
                        x: {
                            title: {
                                display: true,
                                text: 'Date'
                            },
                            ticks: {
                                autoSkip: true,
                                maxRotation: 45,
                                minRotation: 45,
                            },
                            grid: {
                                display: false,
                            }
                        }
                    },
                    plugins: {
                        tooltip: {
                            callbacks: {
                                label: function (tooltipItem) {
                                    let index = tooltipItem.dataIndex;
                                    let durationInMinutes = durationsInMinutes[index];
                                    let hours = Math.floor(durationInMinutes / 60);
                                    let minutes = durationInMinutes % 60;
                                    return `Duration: ${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}\nTime Swing: ${swingData[index].time_swing_type}`;
                                }
                            }
                        }
                    }
                }
            });
        });

        document.addEventListener("DOMContentLoaded", function () {
            let anomalyData = [
                    @foreach ($data['too_long_glucose_anomalies'] as $anomaly)
                {
                    day: "{{ \Carbon\Carbon::parse($anomaly['day'])->format('d/m/Y') }}", // Formattazione della data
                    event: "{{ $anomaly['event'] }}",
                    duration: "{{ \Carbon\Carbon::parse($anomaly['duration'])->format('H:i') }}" // Formattazione HH:mm
                },
                @endforeach
            ];

            let days = [...new Set(anomalyData.map(anomaly => anomaly.day))]; // Lista unica di date

            // Convertire HH:mm in minuti per Chart.js
            let convertToMinutes = (hhmm) => {
                let [hours, minutes] = hhmm.split(":").map(Number);
                return hours * 60 + minutes;
            };

            let eventTypes = ["high", "low", "extremely_high", "extremely_low"];
            let colors = {
                "high": { bg: "rgba(255, 99, 132, 0.5)", border: "rgba(255, 99, 132, 1)" },
                "low": { bg: "rgba(54, 162, 235, 0.5)", border: "rgba(54, 162, 235, 1)" },
                "extremely_high": { bg: "rgba(255, 159, 64, 0.5)", border: "rgba(255, 159, 64, 1)" },
                "extremely_low": { bg: "rgba(153, 102, 255, 0.5)", border: "rgba(153, 102, 255, 1)" }
            };

            let datasets = eventTypes.map(event => ({
                label: event.replace("_", " ").toUpperCase(),
                data: days.map(day => {
                    let anomaly = anomalyData.find(a => a.day === day && a.event === event);
                    return anomaly ? convertToMinutes(anomaly.duration) : 0; // Convertiamo in minuti
                }),
                backgroundColor: colors[event].bg,
                borderColor: colors[event].border,
                borderWidth: 1
            }));

            let ctx = document.getElementById('tooLongGlucoseAnomaliesChart').getContext('2d');

            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: days,
                    datasets: datasets
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: 'Duration (HH:mm)' // Testo aggiornato per HH:mm
                            },
                            ticks: {
                                callback: function (value) {
                                    let hours = Math.floor(value / 60);
                                    let minutes = value % 60;
                                    return `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}`; // Formattazione HH:mm
                                }
                            }
                        },
                        x: {
                            title: {
                                display: true,
                                text: 'Date'
                            },
                            ticks: {
                                maxRotation: 45,
                                minRotation: 30
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            display: true,
                            position: 'top'
                        },
                        tooltip: {
                            callbacks: {
                                label: function (tooltipItem) {
                                    let index = tooltipItem.dataIndex;
                                    let durationInMinutes = datasets[tooltipItem.datasetIndex].data[index];
                                    let hours = Math.floor(durationInMinutes / 60);
                                    let minutes = durationInMinutes % 60;
                                    return `Duration: ${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}`;
                                }
                            }
                        }
                    }
                }
            });
        });
        document.addEventListener("DOMContentLoaded", function () {
            let frequencyData = [
                    @foreach ($data['too_frequent_glucose_anomalies'] as $frequency)
                {
                    day: "{{ \Carbon\Carbon::parse($frequency['day'])->format('d/m/Y') }}", // Formattazione della data
                    high_count: {{ $frequency['high_count'] ?? 0 }},
                    low_count: {{ $frequency['low_count'] ?? 0 }},
                    extremely_high_count: {{ $frequency['extremely_high_count'] ?? 0 }},
                    extremely_low_count: {{ $frequency['extremely_low_count'] ?? 0 }},
                    total_count: {{ $frequency['total_count'] ?? 0 }}
                },
                @endforeach
            ];

            let days = frequencyData.map(frequency => frequency.day);
            let highCounts = frequencyData.map(frequency => frequency.high_count);
            let lowCounts = frequencyData.map(frequency => frequency.low_count);
            let extremelyHighCounts = frequencyData.map(frequency => frequency.extremely_high_count);
            let extremelyLowCounts = frequencyData.map(frequency => frequency.extremely_low_count);

            let ctx = document.getElementById('tooFrequentGlucoseAnomaliesChart').getContext('2d');
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: days,
                    datasets: [
                        {
                            label: 'High Count',
                            data: highCounts,
                            backgroundColor: 'rgba(255, 99, 132, 0.5)',
                            borderColor: 'rgba(255, 99, 132, 1)',
                            borderWidth: 1
                        },
                        {
                            label: 'Low Count',
                            data: lowCounts,
                            backgroundColor: 'rgba(54, 162, 235, 0.5)',
                            borderColor: 'rgba(54, 162, 235, 1)',
                            borderWidth: 1
                        },
                        {
                            label: 'Extremely High Count',
                            data: extremelyHighCounts,
                            backgroundColor: 'rgba(255, 159, 64, 0.5)',
                            borderColor: 'rgba(255, 159, 64, 1)',
                            borderWidth: 1
                        },
                        {
                            label: 'Extremely Low Count',
                            data: extremelyLowCounts,
                            backgroundColor: 'rgba(153, 102, 255, 0.5)',
                            borderColor: 'rgba(153, 102, 255, 1)',
                            borderWidth: 1
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: 'Event Counts'
                            }
                        },
                        x: {
                            title: {
                                display: true,
                                text: 'Date'
                            },
                            ticks: {
                                maxRotation: 45,
                                minRotation: 30
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            position: 'top'
                        },
                        tooltip: {
                            callbacks: {
                                label: function (tooltipItem) {
                                    return `${tooltipItem.dataset.label}: ${tooltipItem.raw} events`;
                                }
                            }
                        }
                    }
                }
            });
        });

        document.addEventListener("DOMContentLoaded", function () {
            let frequentSwingData = [
                    @foreach ($data['too_frequent_time_swings'] as $swing)
                {
                    day: "{{ \Carbon\Carbon::parse($swing['Events'][0]['Day'])->format('d/m/Y') }}",
                    durations: [
                        @foreach ($swing['Events'] as $event)
                            "{{ \Carbon\Carbon::parse($event['Duration time swing'])->format('H:i') }}",
                        @endforeach
                    ],
                    frequency: {{ $swing['Number of Time Swings'] ?? 0 }}
                },
                @endforeach
            ];

            // Estrarre i giorni unici
            let days = frequentSwingData.map(swing => swing.day);
            let uniqueDays = [...new Set(days)]; // Rimuove i duplicati

// Creare i dataset per ogni giorno (array di array)
            let durationDatasets = [];
            let maxSwings = Math.max(...frequentSwingData.map(swing => swing.durations.length));

            for (let i = 0; i < maxSwings; i++) {
                durationDatasets.push({
                    label: `Time Swing ${i + 1}`,
                    data: uniqueDays.map(day => {
                        let swing = frequentSwingData.find(s => s.day === day);
                        if (swing && swing.durations[i]) {
                            // Convertiamo la durata in minuti
                            let [hours, minutes] = swing.durations[i].split(":").map(Number);
                            let totalMinutes = hours * 60 + minutes; // Durata in minuti
                            return totalMinutes; // Restituiamo il valore in minuti
                        }
                        return null; // Se non esiste la durata
                    }),
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    borderColor: 'rgba(75, 192, 192, 1)',
                    borderWidth: 1
                });
            }

// Funzione per formattare l'asse Y in HH:mm
            const formatTimeLabel = (value) => {
                if (value === null) return '00:00'; // In caso di valore nullo
                let hours = Math.floor(value / 60); // Ore
                let minutes = value % 60; // Minuti
                return `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}`;
            };

// Grafico Durata (una sola data per gruppo di Time Swings)
            let ctxDuration = document.getElementById('tooFrequentTimeSwingsDurationChart').getContext('2d');

            new Chart(ctxDuration, {
                type: 'bar',
                data: {
                    labels: uniqueDays, // Mostra solo date uniche
                    datasets: durationDatasets
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: 'Duration (HH:mm)'
                            },
                            ticks: {
                                stepSize: 30, // Aggiusta stepSize per adattarsi alla visualizzazione
                                callback: (value) => formatTimeLabel(value) // Usa la funzione per formattare
                            }
                        },
                        x: {
                            title: {
                                display: true,
                                text: 'Dates'
                            }
                        }
                    },
                    plugins: {
                        tooltip: {
                            callbacks: {
                                // Personalizzare la visualizzazione del tooltip
                                label: function(tooltipItem) {
                                    let minutes = tooltipItem.raw; // Recupera i dati numerici
                                    return `Duration: ${formatTimeLabel(minutes)}`; // Mostra in formato HH:mm
                                }
                            }
                        }
                    }
                }
            });
            // Grafico Frequenza
            let frequencyData = frequentSwingData.map(swing => swing.frequency);
            let ctxFrequency = document.getElementById('tooFrequentTimeSwingsFrequencyChart').getContext('2d');
            new Chart(ctxFrequency, {
                type: 'bar',
                data: {
                    labels: uniqueDays, // Mostra solo date uniche
                    datasets: [{
                        label: 'Frequency of Time Swings',
                        data: frequencyData,
                        backgroundColor: 'rgba(153, 102, 255, 0.5)',
                        borderColor: 'rgba(153, 102, 255, 1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: 'Number of Swings'
                            }
                        },
                        x: {
                            title: {
                                display: true,
                                text: 'Date'
                            }
                        }
                    }
                }
            });
        });
        document.addEventListener("DOMContentLoaded", function () {
            let timeSwingData = [
                    @foreach ($data['time_swing_with_too_long_glucose_anomalies'] as $swing)
                {
                    day: "{{ \Carbon\Carbon::parse($swing['day'])->format('d/m/Y') }}", // Formattazione data
                    duration_time_swing: "{{ \Carbon\Carbon::parse($swing['duration_time_swing'])->format('H:i') }}", // Durata in HH:mm
                    anomalous_duration: "{{ \Carbon\Carbon::parse(str_replace(['Low event: ', 'High event: ', 'Extremely high event: ', 'Extremely low event: '], '', $swing['anomalous_durations']))->format('H:i') }}",
                    time_swing_type: "{{ $swing['first_event'] }} to {{ $swing['second_event'] }}",
                    anomalous_event: "{{ $swing['anomalous_durations'] }}",
                    event_type: "{{ strtolower($swing['first_event']) }}" // Normalizziamo l'evento per i colori
                },
                @endforeach
            ];

            let days = timeSwingData.map(swing => swing.day);
            let durationTimeSwing = timeSwingData.map(swing => swing.duration_time_swing);
            let anomalousDurations = timeSwingData.map(swing => swing.anomalous_duration);
            let eventTypes = timeSwingData.map(swing => swing.event_type); // Per determinare il colore

            // Colori per ogni evento anomalo
            let colors = {
                "high": { bg: "rgba(255, 99, 132, 0.5)", border: "rgba(255, 99, 132, 1)" },
                "low": { bg: "rgba(54, 162, 235, 0.5)", border: "rgba(54, 162, 235, 1)" },
                "extremely_high": { bg: "rgba(255, 159, 64, 0.5)", border: "rgba(255, 159, 64, 1)" },
                "extremely_low": { bg: "rgba(153, 102, 255, 0.5)", border: "rgba(153, 102, 255, 1)" }
            };

            // Funzione di formattazione HH:mm
            const formatTime = (time) => {
                let [hours, minutes] = time.split(":").map(Number);
                return `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}`;
            };

            let ctx = document.getElementById('timeSwingTooLongGlucoseAnomaliesChart').getContext('2d');

            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: days,
                    datasets: [{
                        label: 'Duration Time Swing (HH:mm)',
                        data: durationTimeSwing.map(d => parseFloat(d.replace(':', '.'))), // Converte HH:mm in formato numerico
                        backgroundColor: 'rgba(75, 192, 192, 0.2)', // Colore di background per il dataset Duration Time Swing
                        borderColor: 'rgba(75, 192, 192, 1)',      // Colore di bordo per il dataset Duration Time Swing
                        borderWidth: 1
                    }, {
                        label: 'Anomalous Duration (HH:mm)',
                        data: anomalousDurations.map(d => parseFloat(d.replace(':', '.'))),
                        // Usa il colore di sfondo e di bordo definito in colors per ciascun tipo di evento anomalo
                        backgroundColor: eventTypes.map(event => colors[event] ? colors[event].bg : 'rgba(255, 99, 132, 0.2)'),
                        borderColor: eventTypes.map(event => colors[event] ? colors[event].border : 'rgba(255, 99, 132, 1)'),
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: 'Duration (HH:mm)' // Testo aggiornato per HH:mm
                            },
                            ticks: {
                                stepSize: 0.5, // Intervallo di 30 minuti (0.5 ore)
                                callback: function (value) {
                                    // Calcola ore e minuti
                                    let hours = Math.floor(value); // Ore
                                    let minutes = Math.round((value - hours) * 60); // Minuti

                                    // Ritorna la durata nel formato HH:mm
                                    return `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}`;
                                }
                            }
                        },
                        x: {
                            title: {
                                display: true,
                                text: 'Date'
                            },
                            ticks: {
                                maxRotation: 45,
                                minRotation: 30
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            display: true
                        },
                        tooltip: {
                            callbacks: {
                                label: function (tooltipItem) {
                                    let index = tooltipItem.dataIndex;
                                    let timeSwing = timeSwingData[index];

                                    // Estrai il tipo di evento anomalo
                                    let anomalousType = timeSwing.event_type.charAt(0).toUpperCase() + timeSwing.event_type.slice(1); // Prima lettera maiuscola

                                    return [
                                        'Time Swing: ' + timeSwing.time_swing_type,
                                        'Duration Time Swing: ' + formatTime(timeSwing.duration_time_swing), // Usa la funzione di formattazione per durata
                                        'Anomalous Duration: ' + anomalousType + " " + formatTime(timeSwing.anomalous_duration)  // Usa la funzione di formattazione per anomalous_duration
                                    ];
                                }
                            }
                        }
                    }
                }
            });
        });

    </script>

@endsection

@section('script')
    <!-- apexcharts -->
    <script>


        $(document).ready(function () {


            $.fn.dataTable.moment("ddd, DD MMM YYYY");

            // Inizializza DataTables su tutte le tabelle con ordinamento sulla prima colonna (indice 0)
            $('.datatable-glycemic-swings').DataTable({ order: [[0, "desc"]] });
            $('#datatable-anomalous-duration').DataTable({ order: [[0, "desc"]] });
            $('#datatable-anomalous-frequency').DataTable({ order: [[0, "desc"]] });
            $('#datatable-too-long-duration').DataTable({ order: [[0, "desc"]] });
            $('#datatable-swings-followed-by-frequency').DataTable({
                order: [[0, "desc"]], // Ordina per la prima colonna (data)
                columnDefs: [
                    { orderable: false, targets: -1 } // Disabilita ordinamento sull'ultima colonna
                ]
            });
            $('#datatable-swings-followed-by-duration').DataTable({ order: [[0, "desc"]] });

            //PER DETAILS TOO FREQUENT TIME SWINGS
            $('button[data-toggle="modal"]').on('click', function () {
                var modalId = $(this).data('target'); // Ottieni l'ID del modal
                var tableId = $(modalId).find('table').attr('id'); // Trova l'ID della tabella dentro il modal

                if (!$.fn.DataTable.isDataTable('#' + tableId)) { // Evita di reinizializzare la tabella
                    $('#' + tableId).DataTable({
                        responsive: true,
                        autoWidth: false,
                        order: [[0, "desc"]] // Ordina per data decrescente
                    });
                }
            });
        });




        $(function () {
            // Estrai le date dal backend e formattale in modo appropriato
            var startDate = "{{ $startDate ? \Carbon\Carbon::parse($startDate)->format('m/d/Y') : ($data['start_time'] ? \Carbon\Carbon::parse($data['start_time'])->format('m/d/Y') : moment().startOf('month').format('m/d/Y')) }}";
            var endDate = "{{ $endDate ? \Carbon\Carbon::parse($endDate)->format('m/d/Y') : ($data['end_time'] ? \Carbon\Carbon::parse($data['end_time'])->format('m/d/Y') : moment().endOf('month').format('m/d/Y')) }}";
            var minDate = "{{ $data['start_time'] ? \Carbon\Carbon::parse($data['start_time'])->format('m/d/Y') : '' }}";
            var maxDate = "{{ $data['end_time'] ? \Carbon\Carbon::parse($data['end_time'])->format('m/d/Y') : '' }}";

            // Log per verificare se i dati sono corretti
            console.log("Start Date:", startDate);
            console.log("End Date:", endDate);

            // Configura il daterangepicker
            $('input[name="daterange"]').daterangepicker({
                startDate: startDate,
                endDate: endDate,
                minDate: minDate,
                maxDate: maxDate,
                opens: 'right',
                locale: {
                    format: 'MM/DD/YYYY' // Formato della data
                }
            }, function (start, end, label) {
                // Imposta i valori dei campi nascosti
                $('#start-date').val(start.format('YYYY-MM-DD'));
                $('#end-date').val(end.format('YYYY-MM-DD'));

                // Costruisci il range in formato 'YYYY-MM-DD - YYYY-MM-DD'
                var range = start.format('YYYY-MM-DD') + ' - ' + end.format('YYYY-MM-DD');

                // Imposta il parametro range nella query string
                var form = $('#date-form'); // Assicurati di usare l'ID corretto del form
                var action = form.attr('action');
                form.attr('action', action.split('?')[0] + '?range=' + encodeURIComponent(range));

                // Invia il form
                form.submit();
            });
        });

        function slowScrollTo(element, duration) {
            var targetPosition = element.getBoundingClientRect().top;
            var startPosition = window.pageYOffset;
            var startTime = null;

            function animation(currentTime) {
                if (startTime === null) startTime = currentTime;
                var timeElapsed = currentTime - startTime;
                var run = ease(timeElapsed, startPosition, targetPosition, duration);
                window.scrollTo(0, run);
                if (timeElapsed < duration) requestAnimationFrame(animation);
            }

            // Funzione di easing per rendere lo scroll più naturale
            function ease(t, b, c, d) {
                t /= d / 2;
                if (t < 1) return c / 2 * t * t + b;
                t--;
                return -c / 2 * (t * (t - 2) - 1) + b;
            }

            requestAnimationFrame(animation);
        }

        document.addEventListener("DOMContentLoaded", function () {
            // Controlla se l'URL contiene parametri indicativi dell'invio del form
            if (window.location.search.includes('start_date') || window.location.search.includes('end_date')) {
                var element = document.getElementById("scroll-to-form");
                if (element) {
                    slowScrollTo(element, 1500); // Imposta la durata dello scroll (in millisecondi), ad esempio 1500ms
                }
            }
        });

        document.getElementById('bs-download-pdf-modal').addEventListener('click', function() {
            var menu = this.nextElementSibling;
            menu.style.display = menu.style.display === 'block' ? 'none' : 'block';
        });

        document.addEventListener("DOMContentLoaded", function() {
            // Funzione per catturare un canvas come immagine
            async function captureChart(canvasId, hiddenInputId) {
                const canvas = document.getElementById(canvasId);
                if (!canvas) {
                    console.error(`Canvas ${canvasId} non trovato`);
                    return;
                }

                try {
                    const canvasImage = await html2canvas(canvas, {
                        scale: 2, // Migliora la qualità
                        logging: true, // Abilita logging per debug
                        useCORS: true,
                        allowTaint: true
                    });

                    document.getElementById(hiddenInputId).value = canvasImage.toDataURL('image/png');
                    console.log(`Canvas ${canvasId} catturato con successo`);
                } catch (error) {
                    console.error(`Errore nella cattura di ${canvasId}:`, error);
                }
            }

            // Intercetta l'invio del form
            document.getElementById('download.pdf').addEventListener('submit', async function(e) {
                e.preventDefault();

                // Cattura tutti i grafici
                await Promise.all([
                    captureChart('glycemicSwingsChart', 'glycemicSwingsChartImage'),
                    captureChart('tooLongGlucoseAnomaliesChart', 'tooLongGlucoseAnomaliesChartImage'),
                    captureChart('tooFrequentGlucoseAnomaliesChart', 'tooFrequentGlucoseAnomaliesChartImage'),
                    captureChart('tooFrequentTimeSwingsDurationChart', 'tooFrequentTimeSwingsDurationChartImage'),
                    captureChart('tooFrequentTimeSwingsFrequencyChart', 'tooFrequentTimeSwingsFrequencyChartImage'),
                    captureChart('timeSwingTooLongGlucoseAnomaliesChart', 'timeSwingTooLongGlucoseAnomaliesChartImage')
                ]);

                // Invia il form dopo che tutte le immagini sono state catturate
                this.submit();
            });
        });


    </script>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.datatables.net/1.13.1/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.1/js/dataTables.bootstrap5.min.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>

    <script src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/moment.min.js"></script>
    <script src="https://cdn.datatables.net/plug-ins/1.13.6/sorting/datetime-moment.js"></script>
    <script src="https://html2canvas.hertzen.com/dist/html2canvas.min.js"></script>
@endsection

