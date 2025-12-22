<?php $__env->startSection('title'); ?>
    Patient Details
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>

    <?php $__env->startComponent('components.breadcrumb'); ?>
        <?php $__env->slot('li_1'); ?>
            Patient
        <?php $__env->endSlot(); ?>
        <?php $__env->slot('title'); ?>
            Patient Details
        <?php $__env->endSlot(); ?>
    <?php echo $__env->renderComponent(); ?>

    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css"/>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css"/>
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.2.2/css/buttons.dataTables.min.css">

    <style>
        /* Modern Card Styling */
        .card {
            border: none;
            box-shadow: 0 0.75rem 1.5rem rgba(18, 38, 63, 0.03);
            border-radius: 0.75rem;
            transition: all 0.3s ease;
        }

        .card,
        .card-body {
            overflow: visible !important;
        }

        .card:hover {
            box-shadow: 0 1rem 3rem rgba(18, 38, 63, 0.08);
        }

        .card-header-custom {
            background-color: transparent;
            border-bottom: 1px solid #f6f6f6;
            padding: 1.25rem;
        }


        /* Stat Icons */
        .avatar-title {
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        /* Date Picker Customization */
        .daterange-input-group {
            background: #fff;
            border-radius: 30px;
            padding: 5px 15px;
            border: 1px solid #e9e9ef;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);
            display: flex;
            align-items: center;
            width: 100%;
            max-width: 400px;
            margin: 0 auto;
        }

        input[name="daterange"] {
            border: none;
            outline: none;
            width: 100%;
            padding-left: 10px;
            color: #495057;
            font-weight: 500;
            background: transparent;
        }

        /* Custom Colors */
        .bg-purple {
            background-color: #6f42c1;
            color: white;
        }

        .text-purple {
            color: #6f42c1;
        }

        .bg-heavenly {
            background-color: rgba(220, 110, 110, 0.15);
            color: #d14949;
        }

        .text-heavenly {
            color: #d14949;
        }

        .bg-soft-primary {
            background-color: rgba(85, 110, 230, 0.1) !important;
            color: #556ee6 !important;
        }

        .bg-soft-success {
            background-color: rgba(52, 195, 143, 0.1) !important;
            color: #34c38f !important;
        }

        .bg-soft-warning {
            background-color: rgba(241, 180, 76, 0.1) !important;
            color: #f1b44c !important;
        }

        .bg-soft-danger {
            background-color: rgba(244, 106, 106, 0.1) !important;
            color: #f46a6a !important;
        }

        .bg-soft-info {
            background-color: rgba(80, 165, 241, 0.1) !important;
            color: #50a5f1 !important;
        }

        /* Helpers */
        .font-size-12 {
            font-size: 12px !important;
        }

        .font-size-13 {
            font-size: 13px !important;
        }
    </style>

    <div class="row">
        <div class="col-lg-12">
            <div class="card overflow-hidden">
                <div class="bg-primary bg-soft">
                    <div class="row">
                        <div class="col-7">
                            <div class="text-primary p-3">
                                <h5 class="text-primary">Patient Profile</h5>
                                <p>Analysis Dashboard for <?php echo e($patient->name); ?></p>
                            </div>
                        </div>
                        <div class="col-5 align-self-end text-end">
                            <i class="bx bx-pulse text-primary"
                               style="font-size: 6rem; opacity: 0.3; margin-right: 10px; margin-bottom: -10px;"></i>
                        </div>
                    </div>
                </div>
                <div class="card-body pt-0">
                    <div class="row">
                        <div class="col-sm-12">
                            <div class="d-flex justify-content-between align-items-start mt-3">
                                <div>
                                    <h5 class="font-size-15 text-truncate"><?php echo e($patient->name." ".$patient->surname); ?></h5>
                                    <p class="text-muted mb-0 text-truncate"><?php echo e($patient->email); ?></p>
                                </div>

                                <div class="dropdown">
                                    <button class="btn btn-primary btn-sm dropdown-toggle waves-effect waves-light"
                                            type="button"
                                            id="bs-download-pdf-modal-button"
                                            data-bs-toggle="dropdown"
                                            data-bs-display="static"
                                            aria-expanded="false">
                                        <i class="bx bx-export me-1"></i> Export PDF
                                    </button>
                                    <div class="dropdown-menu dropdown-menu-end p-3"
                                         aria-labelledby="bs-download-pdf-modal-button"
                                         style="max-height: 300px; overflow-y: auto; min-width: 100px;">
                                        <h6
                                            class="dropdown-header">Export Options</h6>
                                        <form id="downloadPdfForm" method="post"
                                              action="<?php echo e(route('download.pdf', ['patientId' => $patient->id, 'csvId' => $csv->id] + (request('start_date') ? ['start_date' => request('start_date')] : []) + (request('end_date') ? ['end_date' => request('end_date')] : []))); ?>">
                                            <?php echo csrf_field(); ?>
                                            <canvas id="exportGlycemicSwingsChart" width="1200" height="600"
                                                    style="display: none;"></canvas>
                                            <input type="hidden" name="glycemicSwingsChart"
                                                   id="glycemicSwingsChartImage">
                                            <canvas id="exportTooLongChart" width="1200" height="600"
                                                    style="display: none;"></canvas>
                                            <input type="hidden" name="tooLongChart" id="tooLongChartImage">
                                            <canvas id="exportTooFrequentChart" width="1200" height="600"
                                                    style="display: none;"></canvas>
                                            <input type="hidden" name="tooFrequentChart" id="tooFrequentChartImage">
                                            <canvas id="exportTooFrequentTimeSwingsDurationChart" width="1200"
                                                    height="600" style="display: none;"></canvas>
                                            <input type="hidden" name="tooFrequentTimeSwingsDurationChart"
                                                   id="tooFrequentTimeSwingsDurationChartImage">
                                            <canvas id="exportTooFrequentTimeSwingsFrequencyChart" width="1200"
                                                    height="600" style="display: none;"></canvas>
                                            <input type="hidden" name="tooFrequentTimeSwingsFrequencyChart"
                                                   id="tooFrequentTimeSwingsFrequencyChartImage">
                                            <canvas id="exportTimeSwingTooLongGlucoseAnomaliesChart" width="1200"
                                                    height="600" style="display: none;"></canvas>
                                            <input type="hidden" name="timeSwingTooLongChart"
                                                   id="timeSwingTooLongChartImage">

                                            <div class="vstack gap-2">
                                                <div class="form-check">
                                                    <input type="checkbox" class="form-check-input" name="detail"
                                                           id="column16" checked>
                                                    <label class="form-check-label" for="column16">Detail</label>
                                                </div>
                                                <div class="form-check">
                                                    <input type="checkbox" class="form-check-input" name="summary"
                                                           id="column17" checked>
                                                    <label class="form-check-label" for="column17">Summary</label>
                                                </div>
                                                <div class="form-check">
                                                    <input type="checkbox" class="form-check-input" name="time_swing"
                                                           id="column18" checked>
                                                    <label class="form-check-label" for="column18">Time Swing</label>
                                                </div>
                                                <div class="form-check">
                                                    <input type="checkbox" class="form-check-input" name="too_long"
                                                           id="column19" checked>
                                                    <label class="form-check-label" for="column19">Too Long
                                                        Anomalies</label>
                                                </div>
                                                <div class="form-check">
                                                    <input type="checkbox" class="form-check-input" name="too_frequent"
                                                           id="column20" checked>
                                                    <label class="form-check-label" for="column20">Too Frequent
                                                        Anomalies</label>
                                                </div>
                                                <div class="form-check">
                                                    <input type="checkbox" class="form-check-input"
                                                           name="too_frequent_time_swing" id="column21" checked>
                                                    <label class="form-check-label" for="column21">Too Frequent Time
                                                        Swings</label>
                                                </div>
                                                <div class="form-check">
                                                    <input type="checkbox" class="form-check-input"
                                                           name="time_swing_too_long" id="column22" checked>
                                                    <label class="form-check-label" for="column22">Complex Time
                                                        Swings</label>
                                                </div>
                                                <div class="mt-3 d-grid">
                                                    <button type="button" class="btn btn-primary btn-sm"
                                                            id="downloadPdfButton">Download Report
                                                    </button>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>


                            </div>
                        </div>
                    </div>

                    <div class="pt-3">
                        <div class="row">
                            <div class="col-md-6">
                                <table class="table table-borderless table-sm mb-0">
                                    <tbody>
                                    <tr>
                                        <th scope="row" style="width: 140px;">Date of Birth:</th>
                                        <td class="text-muted"><?php echo e($patient->birth ? \Carbon\Carbon::parse($patient->birth)->format('Y/m/d') : 'N/A'); ?></td>
                                    </tr>
                                    <tr>
                                        <th scope="row">Address:</th>
                                        <td class="text-muted"><?php echo e($patient->address ?: 'N/A'); ?></td>
                                    </tr>
                                    <tr>
                                        <th scope="row">Phone:</th>
                                        <td class="text-muted"><?php echo e($patient->telephone_number ?? 'N/A'); ?></td>
                                    </tr>
                                    <tr>
                                        <th scope="row">Gender:</th>
                                        <td class="text-muted"><?php echo e($patient->gender ?? 'N/A'); ?></td>
                                    </tr>
                                    </tbody>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <div class="alert alert-info border-0 shadow-sm" role="alert">
                                    <div class="d-flex align-items-center">
                                        <i class="mdi mdi-calendar-clock font-size-20 me-3"></i>
                                        <div>
                                            <h6 class="alert-heading font-size-14 mb-1">Analysis Period</h6>
                                            <p class="mb-0">
                                                <?php echo e($data['start_time'] ? \Carbon\Carbon::parse($data['start_time'])->format('Y/m/d') : 'N/A'); ?>

                                                <i class="mdi mdi-arrow-right mx-1"></i>
                                                <?php echo e($data['end_time'] ? \Carbon\Carbon::parse($data['end_time'])->format('Y/m/d') : 'N/A'); ?>

                                            </p>
                                            <?php
                                                $startTime = $data['start_time'] ? \Carbon\Carbon::parse($data['start_time'])->format('Y/m/d') : null;
                                                $endTime = $data['end_time'] ? \Carbon\Carbon::parse($data['end_time'])->format('Y/m/d') : null;
                                                $startDateFormatted = isset($startDate) ? \Carbon\Carbon::parse($startDate)->format('Y/m/d') : null;
                                                $endDateFormatted = isset($endDate) ? \Carbon\Carbon::parse($endDate)->format('Y/m/d') : null;
                                            ?>
                                            <?php if(($startDateFormatted !== $startTime || $endDateFormatted !== $endTime) && $startDateFormatted && $endDateFormatted): ?>
                                                <div class="mt-1 small"><span class="badge bg-white text-info">Filtered View</span> <?php echo e($startDateFormatted); ?>

                                                    - <?php echo e($endDateFormatted); ?></div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row" id="scroll-to-form">
        <div class="col-12">
            <div class="card bg-transparent shadow-none">
                <div class="card-body p-0">
                    <form id="date-form"
                          action="<?php echo e(route('viewCsvRange', ['csvId' => $csv->id, 'patientId' => $patient->id])); ?>"
                          method="GET">
                        <div class="d-flex justify-content-center align-items-center gap-3">
                            <div class="daterange-input-group">
                                <i class="bx bx-calendar text-primary font-size-18"></i>
                                <input type="text" name="daterange" value="" placeholder="Filter by date range..."/>
                                <input type="hidden" id="start-date" name="start_date"/>
                                <input type="hidden" id="end-date" name="end_date"/>
                            </div>
                            <?php if(($startDateFormatted !== $startTime || $endDateFormatted !== $endTime) && $startDateFormatted && $endDateFormatted): ?>
                                <a href="<?php echo e(route('viewCsv', ['csvId' => $csv->id, 'patientId' => $patient->id])); ?>"
                                   class="btn btn-primary rounded-pill">
                                    <i class="bx bx-reset me-1"></i> Reset
                                </a>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-3">
        <div class="col-xl-4">
            <div class="card h-100">
                <div class="card-header-custom">
                    <h5 class="card-title mb-0">Glycemic Overview</h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-6 border-end">
                            <div class="mb-3">
                                <i class="bx bx-pulse text-primary h1"></i>
                            </div>
                            <h3 class="mb-1"><?php echo e(round($data['avg'],2) ?? 'N/A'); ?></h3>
                            <p class="text-muted mb-0">Avg Glucose</p>
                        </div>
                        <div class="col-6">
                            <div class="mb-3">
                                <?php if($data['gmi'] < 7): ?>
                                    <i class="bx bx-check-shield text-success h1"></i>
                                <?php elseif($data['gmi'] >= 7 && $data['gmi'] <= 8): ?>
                                    <i class="bx bx-shield-quarter text-warning h1"></i>
                                <?php else: ?>
                                    <i class="bx bx-shield-x text-danger h1"></i>
                                <?php endif; ?>
                            </div>
                            <h3 class="mb-1"><?php echo e(round($data['gmi'],2)."%"?? 'N/A'); ?></h3>
                            <p class="text-muted mb-0">GMI</p>
                        </div>
                    </div>

                    <hr class="my-4">

                    <h6 class="mb-3 text-muted text-uppercase font-size-12">Event Distribution</h6>
                    <div class="row g-2">
                        <div class="col-4">
                            <div class="p-2 border rounded text-center">
                                <p class="mb-1 text-muted font-size-12">High</p>
                                <h6 class="mb-0 text-danger"><?php echo e($data['totals_and_durations']['totals']['high'] ?? 0); ?></h6>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="p-2 border rounded text-center">
                                <p class="mb-1 text-muted font-size-12">Normal</p>
                                <h6 class="mb-0 text-success"><?php echo e($data['totals_and_durations']['totals']['normal'] ?? 0); ?></h6>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="p-2 border rounded text-center">
                                <p class="mb-1 text-muted font-size-12">Low</p>
                                <h6 class="mb-0 text-info"><?php echo e($data['totals_and_durations']['totals']['low'] ?? 0); ?></h6>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="p-2 border rounded text-center bg-soft-warning">
                                <p class="mb-1 text-dark font-size-12">Ext. High</p>
                                <h6 class="mb-0 text-dark"><?php echo e($data['totals_and_durations']['totals']['extremely_high'] ?? 0); ?></h6>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="p-2 border rounded text-center bg-light">
                                <p class="mb-1 text-dark font-size-12">Ext. Low</p>
                                <h6 class="mb-0 text-purple"><?php echo e($data['totals_and_durations']['totals']['extremely_low'] ?? 0); ?></h6>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4">
            <div class="card h-100">
                <div class="card-header-custom">
                    <h5 class="card-title mb-0">Time in Ranges (%)</h5>
                </div>
                <div class="card-body">
                    <div class="mt-2">
                        <div class="d-flex justify-content-between font-size-13 mb-1">
                            <span>Extremely High (>250)</span>
                            <span class="text-muted"><?php echo e(number_format((float)($data['totals_and_durations']['percentages']['extremely_high'] ?? 0), 2)); ?>%</span>
                        </div>
                        <div class="progress mb-3" style="height: 6px;">
                            <div class="progress-bar bg-warning" role="progressbar"
                                 style="width: <?php echo e($data['totals_and_durations']['percentages']['extremely_high'] ?? 0); ?>%"></div>
                        </div>

                        <div class="d-flex justify-content-between font-size-13 mb-1">
                            <span>High (180-250)</span>
                            <span class="text-muted"><?php echo e(number_format((float)($data['totals_and_durations']['percentages']['high'] ?? 0), 2)); ?>%</span>
                        </div>
                        <div class="progress mb-3" style="height: 6px;">
                            <div class="progress-bar bg-danger" role="progressbar"
                                 style="width: <?php echo e($data['totals_and_durations']['percentages']['high'] ?? 0); ?>%"></div>
                        </div>

                        <div class="d-flex justify-content-between font-size-13 mb-1">
                            <span>Normal (81-179)</span>
                            <span class="text-muted"><?php echo e(number_format((float)($data['totals_and_durations']['percentages']['normal'] ?? 0), 2)); ?>%</span>
                        </div>
                        <div class="progress mb-3" style="height: 6px;">
                            <div class="progress-bar bg-success" role="progressbar"
                                 style="width: <?php echo e($data['totals_and_durations']['percentages']['normal'] ?? 0); ?>%"></div>
                        </div>

                        <div class="d-flex justify-content-between font-size-13 mb-1">
                            <span>Low (55-80)</span>
                            <span class="text-muted"><?php echo e(number_format((float)($data['totals_and_durations']['percentages']['low'] ?? 0), 2)); ?>%</span>
                        </div>
                        <div class="progress mb-3" style="height: 6px;">
                            <div class="progress-bar bg-info" role="progressbar"
                                 style="width: <?php echo e($data['totals_and_durations']['percentages']['low'] ?? 0); ?>%"></div>
                        </div>

                        <div class="d-flex justify-content-between font-size-13 mb-1">
                            <span>Extremely Low (<55)</span>
                            <span class="text-muted"><?php echo e(number_format((float)($data['totals_and_durations']['percentages']['extremely_low'] ?? 0), 2)); ?>%</span>
                        </div>
                        <div class="progress" style="height: 6px;">
                            <div class="progress-bar bg-purple" role="progressbar"
                                 style="width: <?php echo e($data['totals_and_durations']['percentages']['extremely_low'] ?? 0); ?>%"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4">
            <div class="card h-100">
                <div class="card-header-custom">
                    <h5 class="card-title mb-0">Risk Indicators</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex align-items-center mb-4">
                        <div class="flex-shrink-0 me-3">
                            <div class="avatar-sm">
                                <span class="avatar-title rounded-circle bg-soft-danger text-danger font-size-20">
                                    <i class="bx bx-calendar-exclamation"></i>
                                </span>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="mb-1">Max Anomalous Day</h6>
                            <p class="text-muted mb-0 font-size-13">
                                <?php echo e($data['totals_and_durations']['max_anomalous_day']['date'] ?? 'N/A'); ?>

                                <span class="badge bg-danger rounded-pill ms-1"><?php echo e($data['totals_and_durations']['max_anomalous_day']['details']['Total Count'] ?? 0); ?> events</span>
                            </p>
                        </div>
                    </div>

                    <div class="d-flex align-items-center mb-4">
                        <div class="flex-shrink-0 me-3">
                            <div class="avatar-sm">
                                <span class="avatar-title rounded-circle bg-soft-primary text-primary font-size-20">
                                    <i class="bx bx-timer"></i>
                                </span>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="mb-1">Total Time Swings</h6>
                            <p class="text-muted mb-0 font-size-13">
                                <?php echo e($data['totals_and_durations']['time_swings_stats']['total_time_swings'] ?? 'N/A'); ?>

                                detected in total
                            </p>
                        </div>
                    </div>

                    <div class="bg-light p-3 rounded">
                        <h6 class="font-size-13 mb-3">Complex Metrics</h6>
                        <div class="row text-center">
                            <div class="col-6 border-end">
                                <p class="mb-1 text-muted font-size-12">Prolonged Swings</p>
                                <h5 class="mb-0 text-dark"><?php echo e(number_format((float)($data['totals_and_durations']['time_swings_stats']['percentage_time_swing_too_long'] ?? 0), 1)); ?>

                                    %</h5>
                            </div>
                            <div class="col-6">
                                <p class="mb-1 text-muted font-size-12">Frequent Swings</p>
                                <h5 class="mb-0 text-dark"><?php echo e(number_format((float)($data['totals_and_durations']['time_swings_stats']['percentage_too_frequent_time_swings'] ?? 0), 1)); ?>

                                    %</h5>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-12">
            <div class="container mt-4 p-0">
                <div class="card border-primary border border-opacity-10">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <div class="d-flex align-items-center gap-2" style="margin: 0 auto; position: relative;">
                                <h3 class="card-title mb-0" style="font-size: 1.3rem;">AI Predictive Analysis</h3>
                                <a href="#" data-bs-toggle="modal" data-bs-target="#lstmInfoModal"
                                   title="How does AI Analysis work?" style="position: absolute; right: -40px;">
                                    <i class="mdi mdi-information-outline fs-5 text-muted font-size-24"></i>
                                </a>
                            </div>
                        </div>

                        <div class="modal fade" id="lstmInfoModal" tabindex="-1" aria-labelledby="lstmInfoModalLabel"
                             aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered modal-lg">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="lstmInfoModalLabel">What is AI Predictive
                                            Analysis?</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <p>This analysis is powered by a **Multi-Scale Ensemble LSTM** (Long Short-Term
                                            Memory) Artificial Intelligence engine.</p>
                                        <p>The system mimics a medical consultation board by analyzing the patient's
                                            glucose history through four distinct time windows simultaneously:</p>
                                        <ul>
                                            <li><strong>15 Days:</strong> Detects acute and immediate emergencies.</li>
                                            <li><strong>30 Days:</strong> Analyzes short-term monthly trends.</li>
                                            <li><strong>60 & 90 Days:</strong> Evaluates long-term stability and chronic
                                                conditions.
                                            </li>
                                        </ul>
                                        <p><strong>Safety-First Logic:</strong> The AI applies an adaptive weighting
                                            system. If a recent deterioration is detected (in the last 15-30 days), the
                                            system prioritizes the short-term alert over the long-term stability,
                                            ensuring no acute risk is overlooked ("Anti-Inertia" mechanism).</p>

                                    </div>
                                </div>
                            </div>
                        </div>

                        <?php if(isset($data['lstm_result']) && isset($data['lstm_result']['status']) && $data['lstm_result']['status'] == 'success'): ?>
                            <?php
                                $diag = strtoupper($data['lstm_result']['diagnosis']);
                                $colorClass = 'bg-secondary';
                                $textColor = 'text-white';
                                $icon = 'bx-question-mark';

                                if($diag == 'RED') {
                                    $colorClass = 'bg-danger';
                                    $icon = 'bx-error-circle';
                                } elseif($diag == 'YELLOW') {
                                    $colorClass = 'bg-warning';
                                    $textColor = 'text-dark';
                                    $icon = 'bx-error';
                                } elseif($diag == 'GREEN') {
                                    $colorClass = 'bg-success';
                                    $icon = 'bx-check-circle';
                                }
                            ?>

                            <div class="row g-4">
                                <div class="col-md-4">
                                    <div class="card h-100 border shadow-none">
                                        <div
                                            class="card-body text-center d-flex flex-column justify-content-center align-items-center <?php echo e($colorClass); ?> <?php echo e($textColor); ?> rounded">
                                            <i class='bx <?php echo e($icon); ?>' style="font-size: 4rem; margin-bottom: 10px;"></i>
                                            <h5 class="card-title <?php echo e($textColor); ?> mb-1">Clinical Profile</h5>
                                            <h2 class="mb-0 <?php echo e($textColor); ?>"><?php echo e($diag); ?></h2>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-8">
                                    <div class="card h-100 border shadow-none">
                                        <div class="card-body">
                                            <h5 class="card-title mb-4">Technical Confidence</h5>

                                            <div class="alert alert-light border-start border-primary border-4"
                                                 role="alert">
                                                <h6 class="text-primary"><i class="mdi mdi-stethoscope me-1"></i> AI
                                                    Assessment:</h6>
                                                <p class="mb-0" style="font-size: 1.1rem;">
                                                    <?php echo e($data['lstm_result']['clinical_message'] ?? 'Analysis complete.'); ?>

                                                </p>
                                            </div>

                                            <div class="row mt-4">
                                                <div class="col-md-6">
                                                    <p class="text-muted mb-1">Model Confidence</p>
                                                    <div class="progress mb-3" style="height: 20px;">
                                                        <div
                                                            class="progress-bar progress-bar-striped progress-bar-animated <?php echo e($colorClass); ?>"
                                                            role="progressbar"
                                                            style="width: <?php echo e($data['lstm_result']['confidence']); ?>%;"
                                                            aria-valuenow="<?php echo e($data['lstm_result']['confidence']); ?>"
                                                            aria-valuemin="0"
                                                            aria-valuemax="100">
                                                            <?php echo e($data['lstm_result']['confidence']); ?>%
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <p class="text-muted mb-1">Data Analyzed</p>
                                                    <h4><?php echo e($data['lstm_result']['days_analyzed']); ?> <small
                                                            class="text-muted fs-6">Days</small></h4>
                                                </div>
                                            </div>

                                            <div class="mt-2">
                                                <small class="text-muted">
                                                    <i class="mdi mdi-brain me-1"></i>
                                                    Active Models:
                                                    <?php if(isset($data['lstm_result']['models_used'])): ?>
                                                        <?php $__currentLoopData = $data['lstm_result']['models_used']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $m): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                            <span class="badge bg-primary bg-soft text-primary"><?php echo e($m); ?>d LSTM</span>
                                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                    <?php endif; ?>
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php elseif(isset($data['lstm_result']) && isset($data['lstm_result']['status']) && $data['lstm_result']['status'] == 'error'): ?>
                            <div class="alert alert-warning d-flex align-items-center" role="alert">
                                <i class="bx bx-error-alt me-2 font-size-20"></i>
                                <div>
                                    <strong>AI Analysis Unavailable:</strong> <?php echo e($data['lstm_result']['message']); ?>

                                </div>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-4">
                                <p class="text-muted">AI Analysis not available for this dataset.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-12">
            <div class="container mt-4 p-0">
                <div class="card mb-4">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h3 class="card-title mb-0" style="font-size: 1.3rem;">Analysis Detected Pattern</h3>
                        </div>

                        <?php if(isset($data['patient_stats'])): ?>
                            <div class="row mb-4 g-3">
                                <div class="col-md-3">
                                    <div class="card border shadow-none h-100 bg-light">
                                        <div class="card-body text-center">
                                            <div class="avatar-sm mx-auto mb-3">
                                                <span
                                                    class="avatar-title rounded-circle bg-soft-primary text-primary font-size-20">
                                                    <i class="bx bx-list-ul"></i>
                                                </span>
                                            </div>
                                            <h6 class="text-muted mb-2">Total Pattern</h6>
                                            <h4 class="mb-0"><?php echo e($data['patient_stats']['total_patterns'] ?? 0); ?></h4>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="card border shadow-none h-100 bg-light">
                                        <div class="card-body text-center">
                                            <div class="avatar-sm mx-auto mb-3">
                                                <span
                                                    class="avatar-title rounded-circle bg-soft-success text-success font-size-20">
                                                    <i class="bx bx-trending-up"></i>
                                                </span>
                                            </div>
                                            <h6 class="text-muted mb-2">Top Pattern</h6>
                                            <p class="mb-0 fw-bold font-size-12 text-wrap"
                                               title="<?php echo e($data['patient_stats']['most_frequent_pattern'] ?? 'N/A'); ?>">
                                                <?php echo e($data['patient_stats']['most_frequent_pattern'] ?? 'N/A'); ?>

                                            </p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="card border shadow-none h-100 bg-light">
                                        <div class="card-body text-center">
                                            <div class="avatar-sm mx-auto mb-3">
                                                <span
                                                    class="avatar-title rounded-circle bg-soft-warning text-warning font-size-20">
                                                    <i class="bx bx-pie-chart-alt-2"></i>
                                                </span>
                                            </div>
                                            <h6 class="text-muted mb-2">Targets (R/Y/G)</h6>
                                            <h5 class="mb-0">
                                                <span
                                                    class="text-danger"><?php echo e($data['patient_stats']['target_distribution']['red'] ?? 0); ?></span>
                                                /
                                                <span
                                                    class="text-warning"><?php echo e($data['patient_stats']['target_distribution']['yellow'] ?? 0); ?></span>
                                                /
                                                <span
                                                    class="text-success"><?php echo e($data['patient_stats']['target_distribution']['green'] ?? 0); ?></span>
                                            </h5>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="card border shadow-none h-100 bg-light">
                                        <div class="card-body text-center">
                                            <div class="avatar-sm mx-auto mb-3">
                                                <span
                                                    class="avatar-title rounded-circle bg-soft-danger text-danger font-size-20">
                                                    <i class="bx bx-time-five"></i>
                                                </span>
                                            </div>
                                            <h6 class="text-muted mb-2">Avg Duration</h6>
                                            <h4 class="mb-0"><?php echo e($data['patient_stats']['avg_duration'] ?? '0'); ?> <small
                                                    class="font-size-12 text-muted">min</small></h4>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>

                        <div class="d-flex justify-content-between align-items-center mb-3 mt-4">
                            <div class="d-flex align-items-center gap-2">
                                <h5 class="card-title mb-0">Patient Pattern Table</h5>
                                <a href="#" data-bs-toggle="modal" data-bs-target="#detectionPatternInfoModal">
                                    <i class="mdi mdi-information-outline fs-5 text-muted font-size-20"></i>
                                </a>
                            </div>
                        </div>

                        <div class="modal fade" id="detectionPatternInfoModal" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered modal-lg">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">What are Pattern Detection?</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <p><strong>Pattern Detection</strong> represent sequences of glucose events that
                                            occur consecutively in the uploaded CSV data. These sequences are
                                            automatically analyzed by algorithms that identify recurring Pattern.</p>
                                        <p>The system extracts the <strong>top 10 most frequent Pattern</strong> from
                                            the dataset, allowing users and clinicians to focus on the most common or
                                            potentially risky sequences of events.</p>
                                        <ul>
                                            <li><strong>Global Pattern Detection:</strong> Pattern extracted from a
                                                large dataset.
                                            </li>
                                            <li><strong>Dominant Target:</strong> the most frequent glucose range.</li>
                                            <li><strong>Max Lift:</strong> strength of association.</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <?php if(isset($data['parsed_top_k_patterns']) && count($data['parsed_top_k_patterns']) > 0): ?>
                            <div class="table-responsive">
                                <table id="datatable-detection-Pattern"
                                       class="table table-hover table-striped table-bordered dt-responsive nowrap w-100">
                                    <thead class="table-light">
                                    <tr>
                                        <th class="text-center" style="width: 120px;">Pattern</th>
                                        <th class="text-center" style="width: 80px;">Frequency</th>
                                        <th class="text-center" style="width: 100px;">Avg Duration</th>
                                        <th class="text-center" style="width: 120px;">Dominant Target</th>
                                        <th class="text-center" style="width: 80px;">Max Lift</th>
                                        <th class="text-center">Action</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php $__currentLoopData = $data['parsed_top_k_patterns']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $pattern): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <tr>
                                            <td class="fw-medium"><?php echo e(implode(' → ', array_map('ucfirst', $pattern['pattern']))); ?></td>
                                            <td class="text-center"><span
                                                    class="badge badge-soft-primary font-size-12"><?php echo e($pattern['frequency'] ?? '0'); ?></span>
                                            </td>

                                            <?php
                                                $total_minutes = 0;
                                                $count = 0;
                                                foreach($pattern['occurrences'] as $occ) {
                                                    if(isset($occ[0]['duration'])) {
                                                        [$h, $m, $s] = explode(':', $occ[0]['duration']);
                                                        $seconds = ($h * 3600) + ($m * 60) + $s;
                                                        $total_minutes += $seconds / 60;
                                                        $count++;
                                                    }
                                                }
                                                $avg_minutes = $count > 0 ? round($total_minutes / $count, 2) : 0;
                                            ?>

                                            <td class="text-center"><?php echo e($avg_minutes); ?> min</td>
                                            <td class="text-center">
                                                <?php if(strtolower($pattern['target']) == 'red'): ?>
                                                    <span class="badge bg-danger">Red</span>
                                                <?php elseif(strtolower($pattern['target']) == 'yellow'): ?>
                                                    <span class="badge bg-warning">Yellow</span>
                                                <?php else: ?>
                                                    <span class="badge bg-success">Green</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-center"><?php echo e(isset($pattern['max_lift']) ? number_format($pattern['max_lift'], 2) : 'N/A'); ?></td>
                                            <td class="text-center">
                                                <?php if(isset($pattern['occurrences']) && count($pattern['occurrences']) > 0): ?>
                                                    <button type="button" class="btn btn-outline-info btn-sm"
                                                            data-bs-toggle="modal"
                                                            data-bs-target="#modal-<?php echo e($loop->index); ?>-pattern">
                                                        <i class="bx bx-list-ul"></i> Details
                                                    </button>

                                                    <div class="modal fade" id="modal-<?php echo e($loop->index); ?>-pattern"
                                                         tabindex="-1" aria-hidden="true">
                                                        <div class="modal-dialog modal-lg modal-dialog-scrollable">
                                                            <div class="modal-content">
                                                                <div class="modal-header">
                                                                    <h5 class="modal-title">Pattern Occurrences</h5>
                                                                    <button type="button" class="btn-close"
                                                                            data-bs-dismiss="modal"
                                                                            aria-label="Close"></button>
                                                                </div>
                                                                <div class="modal-body text-start">
                                                                    <table class="table table-sm table-bordered">
                                                                        <thead>
                                                                        <tr>
                                                                            <th>#</th>
                                                                            <th>Day</th>
                                                                            <th>Start</th>
                                                                            <th>End</th>
                                                                            <th>Duration</th>
                                                                        </tr>
                                                                        </thead>
                                                                        <tbody>
                                                                        <?php $__currentLoopData = $pattern['occurrences']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $k => $occ): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                                            <?php if(is_array($occ) && count($occ) > 0): ?>
                                                                                <?php
                                                                                    $first = $occ[0]; $last = $occ[count($occ)-1];
                                                                                    $start = $first['start'] ?? 'N/A'; $end = $last['end'] ?? 'N/A';
                                                                                    $dur = $occ[0]['duration'] ?? 'N/A';
                                                                                    $day = $start !== 'N/A' ? date('D, d M Y', strtotime($start)) : 'N/A';
                                                                                ?>
                                                                                <tr>
                                                                                    <td><?php echo e($k+1); ?></td>
                                                                                    <td><?php echo e($day); ?></td>
                                                                                    <td><?php echo e($start); ?></td>
                                                                                    <td><?php echo e($end); ?></td>
                                                                                    <td><?php echo e($dur); ?></td>
                                                                                </tr>
                                                                            <?php endif; ?>
                                                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                                        </tbody>
                                                                    </table>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                <?php else: ?>
                                                    <span class="text-muted">-</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-light text-center" role="alert">No patterns detected for this
                                range.
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card mt-4 bg-light border-0">
        <div class="card-body p-0">
            <h4 class="card-title mb-4 text-center mt-3" style="font-size: 1.3rem;">Detailed Anomalies & Swings</h4>

            <div class="row g-4">
                <div class="col-lg-6">
                    <div class="card h-100 border shadow-sm">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h5 class="card-title mb-0 d-flex align-items-center">
                                    <span
                                        class="avatar-title rounded-circle bg-soft-primary text-primary font-size-16 me-2"
                                        style="width: 30px; height: 30px;"><i class="bx bx-transfer"></i></span>
                                    Time Swings
                                    <a href="#" class="ms-2 text-muted" data-bs-toggle="modal"
                                       data-bs-target="#timeSwingInfoModal"><i class="mdi mdi-information-outline"></i></a>
                                </h5>
                                <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal"
                                        data-bs-target="#glycemicSwingsModal">Chart
                                </button>
                            </div>

                            <div class="modal fade" id="timeSwingInfoModal" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered modal-lg">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">What is Time Swing?</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                    aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <p><strong>Time Swing</strong> refers to the interval between two
                                                significant glycemic events, especially when glucose levels rapidly
                                                change between different categories (e.g., from Low to High) <strong>within
                                                    a maximum time frame of two hours</strong>.</p>
                                            <p>This helps identify sharp fluctuations in blood glucose that may require
                                                attention or adjustment in treatment.</p>
                                            <p><strong>Example:</strong> A user experiences a hypoglycemic event (Low)
                                                at 10:00 AM and then reaches a hyperglycemic level (High) by 12:00 PM.
                                                Since this transition occurred within the defined threshold of two
                                                hours, it is flagged as a Time Swing.</p>
                                            <p>Monitoring Time Swings is useful for detecting glycemic instability,
                                                assessing therapy effectiveness, and optimizing insulin and meal
                                                strategies.</p>
                                            <div class="text-center mt-4">
                                                <img src="<?php echo e(URL::asset('/assets/images/pattern/p_ts.png')); ?>"
                                                     alt="Time Swing Pattern" class="img-fluid rounded"
                                                     style="width: 300px; height: auto;">
                                                <small class="d-block mt-2 text-muted">Time Swing from High to Low in
                                                    one hour</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="modal fade" id="glycemicSwingsModal" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-lg">
                                    <div class="modal-content">
                                        <div class="modal-header"><h5 class="modal-title">Time Swing Chart</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <canvas id="glycemicSwingsChart" width="400" height="200"></canvas>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <?php if(isset($data['time_swing']) && count($data['time_swing']) > 0): ?>
                                <div class="table-responsive" style="max-height: 300px; overflow-y: auto;">
                                    <table class="table table-sm table-striped datatable-glycemic-swings">
                                        <thead>
                                        <tr>
                                            <th>Day</th>
                                            <th>From</th>
                                            <th>To</th>
                                            <th>Dur (h)</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        <?php $__currentLoopData = $data['time_swing']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $swing): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <tr>
                                                <td><?php echo e($swing['day']); ?></td>
                                                <td><?php echo e($swing['first_event']); ?></td>
                                                <td><?php echo e($swing['second_event']); ?></td>
                                                <td><?php echo e($swing['duration_time_swing']); ?></td>
                                            </tr>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php else: ?>
                                <p class="text-muted text-center py-4">No Time Swings detected.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="card h-100 border shadow-sm">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h5 class="card-title mb-0 d-flex align-items-center">
                                    <span
                                        class="avatar-title rounded-circle bg-soft-warning text-warning font-size-16 me-2"
                                        style="width: 30px; height: 30px;"><i class="bx bx-hourglass"></i></span>
                                    Too Long Anomalies
                                    <a href="#" class="ms-2 text-muted" data-bs-toggle="modal"
                                       data-bs-target="#tooLongGlucoseInfoModal"><i
                                            class="mdi mdi-information-outline"></i></a>
                                </h5>
                                <button class="btn btn-sm btn-outline-warning" data-bs-toggle="modal"
                                        data-bs-target="#tooLongGlucoseAnomaliesModal">Chart
                                </button>
                            </div>

                            <div class="modal fade" id="tooLongGlucoseInfoModal" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered modal-lg">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">What is Too Long Glucose Anomalies?</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                    aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <p><strong>Too Long Glucose Anomalies</strong> refers to the time spent in
                                                abnormal glucose ranges, which could indicate deteriorating health
                                                conditions. These ranges are defined by specific time thresholds:</p>
                                            <ul>
                                                <li><strong>High Glucose:</strong> Minimum of 1 hour and 30 minutes.
                                                </li>
                                                <li><strong>Low Glucose:</strong> Minimum of 30 minutes.</li>
                                                <li><strong>Extremely High Glucose:</strong> Minimum of 45 minutes.</li>
                                                <li><strong>Extremely Low Glucose:</strong> Minimum of 30 minutes.</li>
                                            </ul>
                                            <p>This helps identify prolonged glucose anomalies that may require
                                                adjustments in treatment, lifestyle, or monitoring.</p>
                                            <p><strong>Example:</strong> A user experiences a hypoglycemic event (Low)
                                                from 9:00 AM to 9:30 AM, then remains in a hyperglycemic state (High)
                                                from 11:00 AM to 12:30 PM. Since this high period lasted over 1 hour and
                                                30 minutes, it qualifies as a Too Long Glucose Anomaly.</p>
                                            <div class="text-center mt-4">
                                                <img src="<?php echo e(URL::asset('/assets/images/pattern/p_tl.png')); ?>"
                                                     alt="Too Long Glucose Anomalies Pattern" class="img-fluid rounded"
                                                     style="width: 300px; height: auto;">
                                                <small class="d-block mt-2 text-muted">Prolonged glucose anomalies with
                                                    High glucose for four hours</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="modal fade" id="tooLongGlucoseAnomaliesModal" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-lg">
                                    <div class="modal-content">
                                        <div class="modal-header"><h5 class="modal-title">Chart</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <canvas id="tooLongGlucoseAnomaliesChart" width="400" height="200"></canvas>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <?php if(isset($data['too_long_glucose_anomalies']) && count($data['too_long_glucose_anomalies']) > 0): ?>
                                <div class="table-responsive" style="max-height: 300px; overflow-y: auto;">
                                    <table id="datatable-too-long-duration" class="table table-sm table-striped">
                                        <thead>
                                        <tr>
                                            <th>Day</th>
                                            <th>Event</th>
                                            <th>Duration (h)</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        <?php $__currentLoopData = $data['too_long_glucose_anomalies']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $swing): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <tr>
                                                <td><?php echo e($swing['day']); ?></td>
                                                <td><?php echo e($swing['event']); ?></td>
                                                <td><?php echo e($swing['duration']); ?></td>
                                            </tr>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php else: ?>
                                <p class="text-muted text-center py-4">No data found.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="card h-100 border shadow-sm">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h5 class="card-title mb-0 d-flex align-items-center">
                                    <span
                                        class="avatar-title rounded-circle bg-soft-danger text-danger font-size-16 me-2"
                                        style="width: 30px; height: 30px;"><i class="bx bx-bar-chart-alt-2"></i></span>
                                    Too Frequent Anomalies
                                    <a href="#" class="ms-2 text-muted" data-bs-toggle="modal"
                                       data-bs-target="#tooFrequentGlucoseInfoModal"><i
                                            class="mdi mdi-information-outline"></i></a>
                                </h5>
                                <button class="btn btn-sm btn-outline-danger" data-bs-toggle="modal"
                                        data-bs-target="#tooFrequentGlucoseAnomaliesModal">Chart
                                </button>
                            </div>

                            <div class="modal fade" id="tooFrequentGlucoseInfoModal" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered modal-lg">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">What is Too Frequent Glucose Anomalies?</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                    aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <p><strong>Too Frequent Glucose Anomalies</strong> refers to the occurrence
                                                of abnormal glucose events happening too frequently within a specific
                                                period. These events are categorized by certain thresholds of frequency
                                                and duration:</p>
                                            <ul>
                                                <li><strong>High Glucose:</strong> Minimum of 3 instances.</li>
                                                <li><strong>Low Glucose:</strong> Minimum of 3 instances.</li>
                                                <li><strong>Extremely High/Low Glucose:</strong> Occurs at least 1 time.
                                                </li>
                                            </ul>
                                            <p>Frequent occurrences of glucose anomalies may indicate an issue with
                                                blood sugar control or the need for adjustments in insulin therapy, meal
                                                planning, or lifestyle modifications.</p>
                                            <div class="text-center mt-4">
                                                <img src="<?php echo e(URL::asset('/assets/images/pattern/p_tf.png')); ?>"
                                                     alt="Too Frequent Glucose Anomalies Pattern"
                                                     class="img-fluid rounded" style="width: 300px; height: auto;">
                                                <small class="d-block mt-2 text-muted">Too Frequent Glucose Anomalies
                                                    with Low glucose for one hour every two hours</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="modal fade" id="tooFrequentGlucoseAnomaliesModal" tabindex="-1"
                                 aria-hidden="true">
                                <div class="modal-dialog modal-lg">
                                    <div class="modal-content">
                                        <div class="modal-header"><h5 class="modal-title">Chart</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <canvas id="tooFrequentGlucoseAnomaliesChart" width="400"
                                                    height="200"></canvas>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <?php if(isset($data['too_frequent_glucose_anomalies']) && count($data['too_frequent_glucose_anomalies']) > 0): ?>
                                <div class="table-responsive" style="max-height: 300px; overflow-y: auto;">
                                    <table id="datatable-anomalous-frequency" class="table table-sm table-striped">
                                        <thead>
                                        <tr>
                                            <th>Day</th>
                                            <th>Total</th>
                                            <th>High</th>
                                            <th>Low</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        <?php $__currentLoopData = $data['too_frequent_glucose_anomalies']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $f): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <tr>
                                                <td><?php echo e($f['day']); ?></td>
                                                <td><?php echo e($f['total_count']); ?></td>
                                                <td><?php echo e($f['high_count']); ?></td>
                                                <td><?php echo e($f['low_count']); ?></td>
                                            </tr>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php else: ?>
                                <p class="text-muted text-center py-4">No data found.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="card h-100 border shadow-sm">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h5 class="card-title mb-0 d-flex align-items-center">
                                    <span class="avatar-title rounded-circle bg-soft-info text-info font-size-16 me-2"
                                          style="width: 30px; height: 30px;"><i class="bx bx-history"></i></span>
                                    Too Frequent Time Swings
                                    <a href="#" class="ms-2 text-muted" data-bs-toggle="modal"
                                       data-bs-target="#tooFrequentTimeSwingsInfoModal"><i
                                            class="mdi mdi-information-outline"></i></a>
                                </h5>
                                <div>
                                    <button class="btn btn-sm btn-outline-info me-1" data-bs-toggle="modal"
                                            data-bs-target="#tooFrequentTimeSwingsDurationModal">Dur
                                    </button>
                                    <button class="btn btn-sm btn-outline-info" data-bs-toggle="modal"
                                            data-bs-target="#tooFrequentTimeSwingsFrequencyModal">Freq
                                    </button>
                                </div>
                            </div>

                            <div class="modal fade" id="tooFrequentTimeSwingsInfoModal" tabindex="-1"
                                 aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered modal-lg">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">What is Too Frequent Time Swings?</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                    aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <p><strong>Too Frequent Time Swings</strong> refers to the frequency of
                                                rapid glucose level changes within a specific time frame (within two
                                                hours), which may indicate potential issues with glucose stability or
                                                treatment effectiveness.</p>
                                            <p>To identify these swings, we evaluate the frequency of significant
                                                glucose transitions (e.g., from Low to High or vice versa) within the
                                                observation period. A minimum of two Time Swings within a day can
                                                indicate a need for closer monitoring or treatment adjustments.</p>
                                            <ul>
                                                <li><strong>High to Low Glucose Swings:</strong> A shift from a
                                                    hyperglycemic state to a hypoglycemic state within a short period
                                                    (e.g., 2 hours).
                                                </li>
                                                <li><strong>Low to High Glucose Swings:</strong> A shift from a
                                                    hypoglycemic state to a hyperglycemic state within a short period
                                                    (e.g., 2 hours).
                                                </li>
                                            </ul>
                                            <div class="text-center mt-4">
                                                <img src="<?php echo e(URL::asset('/assets/images/pattern/p_tstf.png')); ?>"
                                                     alt="Too Frequent Time Swings Pattern" class="img-fluid rounded"
                                                     style="width: 300px; height: auto;">
                                                <small class="d-block mt-2 text-muted">Rapid Time Swings between High
                                                    and Low glucose events within a few hours</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="modal fade" id="tooFrequentTimeSwingsDurationModal" tabindex="-1"
                                 aria-hidden="true">
                                <div class="modal-dialog modal-lg">
                                    <div class="modal-content">
                                        <div class="modal-header"><h5 class="modal-title">Chart</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <canvas id="tooFrequentTimeSwingsDurationChart" width="400"
                                                    height="200"></canvas>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="modal fade" id="tooFrequentTimeSwingsFrequencyModal" tabindex="-1"
                                 aria-hidden="true">
                                <div class="modal-dialog modal-lg">
                                    <div class="modal-content">
                                        <div class="modal-header"><h5 class="modal-title">Chart</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <canvas id="tooFrequentTimeSwingsFrequencyChart" width="400"
                                                    height="200"></canvas>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <?php if(isset($data['too_frequent_time_swings']) && count($data['too_frequent_time_swings']) > 0): ?>
                                <div class="table-responsive" style="max-height: 300px; overflow-y: auto;">
                                    <table id="datatable-swings-followed-by-frequency"
                                           class="table table-sm table-striped">
                                        <thead>
                                        <tr>
                                            <th>Day</th>
                                            <th>Count</th>
                                            <th>Action</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        <?php $__currentLoopData = $data['too_frequent_time_swings']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $swing): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <tr>
                                                <td><?php echo e($swing['Events'][0]['Day'] ?? 'N/A'); ?></td>
                                                <td><?php echo e($swing['Number of Time Swings'] ?? 'N/A'); ?></td>
                                                <td>
                                                    <button type="button" class="btn btn-outline-secondary btn-sm"
                                                            data-bs-toggle="modal"
                                                            data-bs-target="#modal-<?php echo e($loop->index); ?>">Details
                                                    </button>
                                                    <div class="modal fade" id="modal-<?php echo e($loop->index); ?>" tabindex="-1"
                                                         aria-hidden="true">
                                                        <div class="modal-dialog modal-lg">
                                                            <div class="modal-content">
                                                                <div class="modal-header"><h5 class="modal-title">
                                                                        Details</h5>
                                                                    <button type="button" class="btn-close"
                                                                            data-bs-dismiss="modal"></button>
                                                                </div>
                                                                <div class="modal-body">
                                                                    <table
                                                                        id="datatable-too-frequent_time_swings_details-<?php echo e($loop->index); ?>"
                                                                        class="table table-bordered dt-responsive nowrap w-100">
                                                                        <thead>
                                                                        <tr>
                                                                            <th>Day</th>
                                                                            <th>First</th>
                                                                            <th>Second</th>
                                                                            <th>Dur</th>
                                                                        </tr>
                                                                        </thead>
                                                                        <tbody><?php $__currentLoopData = $swing['Events']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $event): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                                            <tr>
                                                                                <td><?php echo e($event['Day']); ?></td>
                                                                                <td><?php echo e($event['First event']); ?></td>
                                                                                <td><?php echo e($event['Second event']); ?></td>
                                                                                <td><?php echo e($event['Duration time swing']); ?></td>
                                                                            </tr>
                                                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></tbody>
                                                                    </table>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php else: ?>
                                <p class="text-muted text-center py-4">No data found.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="col-12">
                    <div class="card h-100 border shadow-sm">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h5 class="card-title mb-0 d-flex align-items-center">
                                    <span
                                        class="avatar-title rounded-circle bg-soft-danger text-danger font-size-16 me-2"
                                        style="width: 30px; height: 30px;"><i class="bx bx-error-alt"></i></span>
                                    Time Swing With Too Long Anomalies
                                    <a href="#" class="ms-2 text-muted" data-bs-toggle="modal"
                                       data-bs-target="#timeSwingTooLongGlucoseInfoModal"><i
                                            class="mdi mdi-information-outline"></i></a>
                                </h5>
                                <button class="btn btn-sm btn-outline-danger" data-bs-toggle="modal"
                                        data-bs-target="#timeSwingTooLongGlucoseAnomaliesModal">Chart
                                </button>
                            </div>

                            <div class="modal fade" id="timeSwingTooLongGlucoseInfoModal" tabindex="-1"
                                 aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered modal-lg">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">What is Time Swing With Too Long Glucose
                                                Anomalies?</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                    aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <p><strong>Time Swing With Too Long Glucose Anomalies</strong> refers to
                                                analyzing swings where one of the intervals before or after the swing
                                                has a prolonged period of abnormal glucose levels. This indicates
                                                extended instability in glucose regulation.</p>
                                            <p>To detect such swings, we evaluate if one of the periods before or after
                                                a time swing (e.g., from Low to High or High to Low) <strong>occurs
                                                    within a maximum time window of two hours</strong> falls within the
                                                defined Too Long Glucose Anomalies thresholds.</p>
                                            <ul>
                                                <li><strong>High Glucose:</strong> Minimum of 1 hour and 30 minutes.
                                                </li>
                                                <li><strong>Low Glucose:</strong> Minimum of 30 minutes.</li>
                                                <li><strong>Extremely High Glucose:</strong> Minimum of 45 minutes.</li>
                                                <li><strong>Extremely Low Glucose:</strong> Minimum of 30 minutes.</li>
                                            </ul>
                                            <div class="text-center mt-4">
                                                <img src="<?php echo e(URL::asset('/assets/images/pattern/p_tstl.png')); ?>"
                                                     alt="Time Swing With Too Long Glucose Anomalies Pattern"
                                                     class="img-fluid rounded" style="width: 300px; height: auto;">
                                                <small class="d-block mt-2 text-muted">Time Swing from Low to High with
                                                    a four-hour High glucose anomaly</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="modal fade" id="timeSwingTooLongGlucoseAnomaliesModal" tabindex="-1"
                                 aria-hidden="true">
                                <div class="modal-dialog modal-lg">
                                    <div class="modal-content">
                                        <div class="modal-header"><h5 class="modal-title">Chart</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <canvas id="timeSwingTooLongGlucoseAnomaliesChart" width="400"
                                                    height="200"></canvas>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <?php if(isset($data['time_swing_with_too_long_glucose_anomalies']) && count($data['time_swing_with_too_long_glucose_anomalies']) > 0): ?>
                                <div class="table-responsive">
                                    <table id="datatable-swings-followed-by-duration"
                                           class="table table-sm table-striped">
                                        <thead>
                                        <tr>
                                            <th>Day</th>
                                            <th>First Swing</th>
                                            <th>Second Swing</th>
                                            <th>Dur Swing</th>
                                            <th>Dur Anomaly</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        <?php $__currentLoopData = $data['time_swing_with_too_long_glucose_anomalies']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $swing): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <tr>
                                                <td><?php echo e($swing['day']); ?></td>
                                                <td><?php echo e($swing['first_event']); ?></td>
                                                <td><?php echo e($swing['second_event']); ?></td>
                                                <td><?php echo e($swing['duration_time_swing']); ?></td>
                                                <td><?php echo e($swing['anomalous_durations']); ?></td>
                                            </tr>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php else: ?>
                                <p class="text-muted text-center py-4">No data found.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="card h-100 border shadow-sm">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h5 class="card-title mb-0 d-flex align-items-center">
                                    <span class="avatar-title rounded-circle bg-soft-dark text-dark font-size-16 me-2"
                                          style="width: 30px; height: 30px;"><i class="bx bx-tachometer"></i></span>
                                    Extremely Time Swings
                                    <a href="#" class="ms-2 text-muted" data-bs-toggle="modal"
                                       data-bs-target="#extremelyTimeSwingInfoModal"><i
                                            class="mdi mdi-information-outline"></i></a>
                                </h5>
                                <button class="btn btn-sm btn-outline-dark" data-bs-toggle="modal"
                                        data-bs-target="#extremelyGlycemicSwingsModal">Chart
                                </button>
                            </div>

                            <div class="modal fade" id="extremelyTimeSwingInfoModal" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-lg">
                                    <div class="modal-content">
                                        <div class="modal-header"><h5 class="modal-title">Info</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body"><p><strong>Extremely Time Swings</strong> refers to
                                                rapid fluctuations involving "Extremely High" or "Extremely Low" glucose
                                                levels within 2 hours.</p></div>
                                    </div>
                                </div>
                            </div>

                            <div class="modal fade" id="extremelyGlycemicSwingsModal" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-lg">
                                    <div class="modal-content">
                                        <div class="modal-header"><h5 class="modal-title">Extremely Time Swing
                                                Chart</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <canvas id="extremelyGlycemicSwingsChart" width="400" height="200"></canvas>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <?php if(isset($data['extremely_time_swing']) && count($data['extremely_time_swing']) > 0): ?>
                                <div class="table-responsive" style="max-height: 300px; overflow-y: auto;">
                                    <table class="table table-sm table-striped">
                                        <thead>
                                        <tr>
                                            <th>Day</th>
                                            <th>From</th>
                                            <th>To</th>
                                            <th>Dur (h)</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        <?php $__currentLoopData = $data['extremely_time_swing']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $swing): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <tr>
                                                <td><?php echo e($swing['day']); ?></td>
                                                <td><?php echo e($swing['first_event']); ?></td>
                                                <td><?php echo e($swing['second_event']); ?></td>
                                                <td><?php echo e($swing['duration_time_swing']); ?></td>
                                            </tr>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php else: ?>
                                <p class="text-muted text-center py-4">No Extremely Time Swings detected.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="card h-100 border shadow-sm">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h5 class="card-title mb-0 d-flex align-items-center">
                    <span class="avatar-title rounded-circle bg-soft-purple text-purple font-size-16 me-2"
                          style="width: 30px; height: 30px;">
                        <i class="bx bx-layer"></i> </span>
                                    Frequent Ext. Swings
                                    <a href="#" class="ms-2 text-muted" data-bs-toggle="modal"
                                       data-bs-target="#tooFrequentExtremelyTimeSwingsInfoModal"><i
                                            class="mdi mdi-information-outline"></i></a>
                                </h5>
                                <div>
                                    <button class="btn btn-sm btn-outline-purple me-1" data-bs-toggle="modal"
                                            data-bs-target="#tooFrequentExtremelyTimeSwingsDurationModal">Dur
                                    </button>
                                    <button class="btn btn-sm btn-outline-purple" data-bs-toggle="modal"
                                            data-bs-target="#tooFrequentExtremelyTimeSwingsFrequencyModal">Freq
                                    </button>
                                </div>
                            </div>

                            <div class="modal fade" id="tooFrequentExtremelyTimeSwingsInfoModal" tabindex="-1"
                                 aria-hidden="true">
                                <div class="modal-dialog modal-lg">
                                    <div class="modal-content">
                                        <div class="modal-header"><h5 class="modal-title">Info</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body"><p>Info about frequent extreme swings...</p></div>
                                    </div>
                                </div>
                            </div>
                            <div class="modal fade" id="tooFrequentExtremelyTimeSwingsDurationModal" tabindex="-1"
                                 aria-hidden="true">
                                <div class="modal-dialog modal-lg">
                                    <div class="modal-content">
                                        <div class="modal-header"><h5 class="modal-title">Chart</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <canvas id="tooFrequentExtremelyTimeSwingsDurationChart" width="400"
                                                    height="200"></canvas>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="modal fade" id="tooFrequentExtremelyTimeSwingsFrequencyModal" tabindex="-1"
                                 aria-hidden="true">
                                <div class="modal-dialog modal-lg">
                                    <div class="modal-content">
                                        <div class="modal-header"><h5 class="modal-title">Chart</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <canvas id="tooFrequentExtremelyTimeSwingsFrequencyChart" width="400"
                                                    height="200"></canvas>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <?php if(isset($data['too_frequent_extremely_time_swings']) && count($data['too_frequent_extremely_time_swings']) > 0): ?>
                                <div class="table-responsive" style="max-height: 300px; overflow-y: auto;">
                                    <table class="table table-sm table-striped">
                                        <thead>
                                        <tr>
                                            <th>Day</th>
                                            <th>Count</th>
                                            <th>Action</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        <?php $__currentLoopData = $data['too_frequent_extremely_time_swings']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $swing): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <tr>
                                                <td><?php echo e($swing['Events'][0]['Day'] ?? 'N/A'); ?></td>
                                                <td><?php echo e($swing['Number of Time Swings'] ?? 'N/A'); ?></td>
                                                <td>
                                                    <button type="button" class="btn btn-outline-secondary btn-sm"
                                                            data-bs-toggle="modal"
                                                            data-bs-target="#modal-ext-<?php echo e($loop->index); ?>">Details
                                                    </button>
                                                    <div class="modal fade" id="modal-ext-<?php echo e($loop->index); ?>"
                                                         tabindex="-1" aria-hidden="true">
                                                        <div class="modal-dialog modal-lg">
                                                            <div class="modal-content">
                                                                <div class="modal-header"><h5 class="modal-title">
                                                                        Details</h5>
                                                                    <button type="button" class="btn-close"
                                                                            data-bs-dismiss="modal"></button>
                                                                </div>
                                                                <div class="modal-body">
                                                                    <table
                                                                        class="table table-bordered dt-responsive nowrap w-100">
                                                                        <thead>
                                                                        <tr>
                                                                            <th>Day</th>
                                                                            <th>First</th>
                                                                            <th>Second</th>
                                                                            <th>Dur</th>
                                                                        </tr>
                                                                        </thead>
                                                                        <tbody><?php $__currentLoopData = $swing['Events']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $event): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                                            <tr>
                                                                                <td><?php echo e($event['Day']); ?></td>
                                                                                <td><?php echo e($event['First event']); ?></td>
                                                                                <td><?php echo e($event['Second event']); ?></td>
                                                                                <td><?php echo e($event['Duration time swing']); ?></td>
                                                                            </tr>
                                                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></tbody>
                                                                    </table>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php else: ?>
                                <p class="text-muted text-center py-4">No data found.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="col-12">
                    <div class="card h-100 border shadow-sm">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h5 class="card-title mb-0 d-flex align-items-center">
                    <span class="avatar-title rounded-circle bg-soft-purple text-purple font-size-16 me-2"
                          style="width: 30px; height: 30px;">
                        <i class="bx bx-timer"></i> </span>
                                    Ext. Swing With Too Long Anomalies
                                    <a href="#" class="ms-2 text-muted" data-bs-toggle="modal"
                                       data-bs-target="#extTimeSwingTooLongInfoModal"><i
                                            class="mdi mdi-information-outline"></i></a>
                                </h5>
                                <button class="btn btn-sm btn-outline-purple" data-bs-toggle="modal"
                                        data-bs-target="#extTimeSwingTooLongModal">Chart
                                </button>
                            </div>

                            <div class="modal fade" id="extTimeSwingTooLongInfoModal" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-lg">
                                    <div class="modal-content">
                                        <div class="modal-header"><h5 class="modal-title">Info</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body"><p>Details about extremely complex swings...</p></div>
                                    </div>
                                </div>
                            </div>
                            <div class="modal fade" id="extTimeSwingTooLongModal" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-lg">
                                    <div class="modal-content">
                                        <div class="modal-header"><h5 class="modal-title">Chart</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <canvas id="extTimeSwingTooLongChart" width="400" height="200"></canvas>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <?php if(isset($data['extremely_time_swing_with_too_long_glucose_anomalies']) && count($data['extremely_time_swing_with_too_long_glucose_anomalies']) > 0): ?>
                                <div class="table-responsive">
                                    <table class="table table-sm table-striped">
                                        <thead>
                                        <tr>
                                            <th>Day</th>
                                            <th>First Swing</th>
                                            <th>Second Swing</th>
                                            <th>Dur Swing</th>
                                            <th>Dur Anomaly</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        <?php $__currentLoopData = $data['extremely_time_swing_with_too_long_glucose_anomalies']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $swing): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <tr>
                                                <td><?php echo e($swing['day']); ?></td>
                                                <td><?php echo e($swing['first_event']); ?></td>
                                                <td><?php echo e($swing['second_event']); ?></td>
                                                <td><?php echo e($swing['duration_time_swing']); ?></td>
                                                <td><?php echo e($swing['anomalous_durations']); ?></td>
                                            </tr>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php else: ?>
                                <p class="text-muted text-center py-4">No data found.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php $__env->startSection('script'); ?>
        <script>
            $(document).ready(function () {
                $.fn.dataTable.moment("ddd, DD MMM YYYY");
                $('.datatable-glycemic-swings').DataTable({order: [[0, "desc"]]});
                $('#datatable-anomalous-duration').DataTable({order: [[0, "desc"]]});
                $('#datatable-anomalous-frequency').DataTable({order: [[0, "desc"]]});
                $('#datatable-too-long-duration').DataTable({order: [[0, "desc"]]});
                $('#datatable-detection-Pattern').DataTable({
                    order: [[1, "desc"]],
                    columnDefs: [{orderable: false, targets: -1}]
                });
                $('#datatable-swings-followed-by-frequency').DataTable({
                    order: [[0, "desc"]],
                    columnDefs: [{orderable: false, targets: -1}]
                });
                $('#datatable-swings-followed-by-duration').DataTable({order: [[0, "desc"]]});

                $('button[data-toggle="modal"]').on('click', function () {
                    var modalId = $(this).data('target');
                    var tableId = $(modalId).find('table').attr('id');
                    if (tableId && !$.fn.DataTable.isDataTable('#' + tableId)) {
                        $('#' + tableId).DataTable({
                            responsive: true,
                            autoWidth: false,
                            order: [[0, "desc"]]
                        });
                    }
                });
            });

            $(function () {
                var startDate = "<?php echo e($startDate ? \Carbon\Carbon::parse($startDate)->format('m/d/Y') : ($data['start_time'] ? \Carbon\Carbon::parse($data['start_time'])->format('m/d/Y') : moment().startOf('month').format('m/d/Y'))); ?>";
                var endDate = "<?php echo e($endDate ? \Carbon\Carbon::parse($endDate)->format('m/d/Y') : ($data['end_time'] ? \Carbon\Carbon::parse($data['end_time'])->format('m/d/Y') : moment().endOf('month').format('m/d/Y'))); ?>";
                var minDate = "<?php echo e($data['start_time'] ? \Carbon\Carbon::parse($data['start_time'])->format('m/d/Y') : ''); ?>";
                var maxDate = "<?php echo e($data['end_time'] ? \Carbon\Carbon::parse($data['end_time'])->format('m/d/Y') : ''); ?>";

                $('input[name="daterange"]').daterangepicker({
                    startDate: startDate,
                    endDate: endDate,
                    minDate: minDate,
                    maxDate: maxDate,
                    opens: 'right',
                    locale: {format: 'MM/DD/YYYY'}
                }, function (start, end, label) {
                    $('#start-date').val(start.format('YYYY-MM-DD'));
                    $('#end-date').val(end.format('YYYY-MM-DD'));
                    var range = start.format('YYYY-MM-DD') + ' - ' + end.format('YYYY-MM-DD');
                    var form = $('#date-form');
                    var action = form.attr('action');
                    form.attr('action', action.split('?')[0] + '?range=' + encodeURIComponent(range));
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

                function ease(t, b, c, d) {
                    t /= d / 2;
                    if (t < 1) return c / 2 * t * t + b;
                    t--;
                    return -c / 2 * (t * (t - 2) - 1) + b;
                }

                requestAnimationFrame(animation);
            }

            document.addEventListener("DOMContentLoaded", function () {
                if (window.location.search.includes('start_date') || window.location.search.includes('end_date')) {
                    var element = document.getElementById("scroll-to-form");
                    if (element) {
                        slowScrollTo(element, 1500);
                    }
                }
            });

            function getCanvasWithWhiteBackground(canvas) {
                const copy = document.createElement('canvas');
                copy.width = canvas.width;
                copy.height = canvas.height;
                const ctx = copy.getContext('2d');
                ctx.fillStyle = 'white';
                ctx.fillRect(0, 0, copy.width, copy.height);
                ctx.drawImage(canvas, 0, 0);
                return copy;
            }

            document.addEventListener("DOMContentLoaded", function () {
                const downloadButton = document.getElementById('downloadPdfButton');
                const form = document.getElementById('downloadPdfForm');
                const canvasIds = ['exportGlycemicSwingsChart', 'exportTooLongChart', 'exportTooFrequentChart', 'exportTooFrequentTimeSwingsDurationChart', 'exportTooFrequentTimeSwingsFrequencyChart', 'exportTimeSwingTooLongGlucoseAnomaliesChart'];
                const hiddenInputs = ['glycemicSwingsChartImage', 'tooLongChartImage', 'tooFrequentChartImage', 'tooFrequentTimeSwingsDurationChartImage', 'tooFrequentTimeSwingsFrequencyChartImage', 'timeSwingTooLongChartImage'];

                downloadButton.addEventListener('click', function () {
                    canvasIds.forEach((canvasId, index) => {
                        const canvas = document.getElementById(canvasId);
                        const hiddenInput = document.getElementById(hiddenInputs[index]);
                        if (!canvas) {
                            alert("Errore: grafico " + canvasId + " non trovato.");
                            return;
                        }
                        try {
                            const canvasWithBg = getCanvasWithWhiteBackground(canvas);
                            const imageData = canvasWithBg.toDataURL('image/png');
                            hiddenInput.value = imageData;
                        } catch (e) {
                            console.error("Errore export", e);
                        }
                    });
                    form.submit();
                });
            });

            document.addEventListener("DOMContentLoaded", function () {
                let swingData = [
                        <?php $__currentLoopData = $data['time_swing'] ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $swing): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>                {
                        day: "<?php echo e(\Carbon\Carbon::parse($swing['day'])->format('d/m/Y')); ?>",
                        duration: "<?php echo e(\Carbon\Carbon::parse($swing['duration_time_swing'])->format('H:i')); ?>",
                        time_swing_type: "<?php echo e($swing['first_event']); ?> to <?php echo e($swing['second_event']); ?>"
                    },
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                ];
                let durationsInMinutes = swingData.map(swing => {
                    let [hours, minutes] = swing.duration.split(":").map(Number);
                    return hours * 60 + minutes;
                });
                let days = swingData.map(swing => swing.day);

                let ctx = document.getElementById('glycemicSwingsChart').getContext('2d');
                new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: days,
                        datasets: [{
                            label: 'Duration of Time Swings (HH:mm)',
                            data: durationsInMinutes,
                            backgroundColor: 'rgba(220, 110, 110, 0.5)',
                            borderColor: 'rgba(220, 110, 110, 1)',
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true, maintainAspectRatio: false,
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    callback: function (value) {
                                        let hours = Math.floor(value / 60);
                                        let minutes = value % 60;
                                        return `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}`;
                                    }
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

                // Export Chart
                let ctx2 = document.getElementById('exportGlycemicSwingsChart').getContext('2d');
                new Chart(ctx2, {
                    type: 'bar',
                    data: {
                        labels: days,
                        datasets: [{
                            label: 'Duration of Time Swings (HH:mm)',
                            data: durationsInMinutes,
                            backgroundColor: 'rgba(220, 110, 110, 0.5)',
                            borderColor: 'rgba(220, 110, 110, 1)',
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: false, width: 1200, height: 600, maintainAspectRatio: false,
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    callback: function (value) {
                                        let hours = Math.floor(value / 60);
                                        let minutes = value % 60;
                                        return `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}`;
                                    }
                                }
                            }
                        }
                    }
                });
            });

            document.addEventListener("DOMContentLoaded", function () {
                let anomalyData = [
                        <?php $__currentLoopData = $data['too_long_glucose_anomalies'] ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $anomaly): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    {
                        day: "<?php echo e(\Carbon\Carbon::parse($anomaly['day'])->format('d/m/Y')); ?>",
                        event: "<?php echo e($anomaly['event']); ?>",
                        duration: "<?php echo e(\Carbon\Carbon::parse($anomaly['duration'])->format('H:i')); ?>"
                    },
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                ];
                let days = [...new Set(anomalyData.map(anomaly => anomaly.day))];
                let convertToMinutes = (hhmm) => {
                    let [hours, minutes] = hhmm.split(":").map(Number);
                    return hours * 60 + minutes;
                };
                let eventTypes = ["high", "low", "extremely_high", "extremely_low"];
                let colors = {
                    "high": {bg: "rgba(255, 99, 132, 0.5)", border: "rgba(255, 99, 132, 1)"},
                    "low": {bg: "rgba(54, 162, 235, 0.5)", border: "rgba(54, 162, 235, 1)"},
                    "extremely_high": {bg: "rgba(255, 159, 64, 0.5)", border: "rgba(255, 159, 64, 1)"},
                    "extremely_low": {bg: "rgba(153, 102, 255, 0.5)", border: "rgba(153, 102, 255, 1)"}
                };
                let datasets = eventTypes.map(event => ({
                    label: event.replace("_", " ").toUpperCase(),
                    data: days.map(day => {
                        let anomaly = anomalyData.find(a => a.day === day && a.event === event);
                        return anomaly ? convertToMinutes(anomaly.duration) : 0;
                    }),
                    backgroundColor: colors[event].bg, borderColor: colors[event].border, borderWidth: 1
                }));

                let ctx = document.getElementById('tooLongGlucoseAnomaliesChart').getContext('2d');
                new Chart(ctx, {
                    type: 'bar',
                    data: {labels: days, datasets: datasets},
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: {
                                beginAtZero: true, ticks: {
                                    callback: function (value) {
                                        let hours = Math.floor(value / 60);
                                        let minutes = value % 60;
                                        return `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}`;
                                    }
                                }
                            }
                        }
                    }
                });

                let ctx2 = document.getElementById('exportTooLongChart').getContext('2d');
                new Chart(ctx2, {
                    type: 'bar',
                    data: {labels: days, datasets: datasets},
                    options: {
                        responsive: false,
                        width: 1200,
                        height: 600,
                        maintainAspectRatio: false,
                        scales: {
                            y: {
                                beginAtZero: true, ticks: {
                                    callback: function (value) {
                                        let hours = Math.floor(value / 60);
                                        let minutes = value % 60;
                                        return `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}`;
                                    }
                                }
                            }
                        }
                    }
                });
            });

            document.addEventListener("DOMContentLoaded", function () {
                let frequencyData = [
                        <?php $__currentLoopData = $data['too_frequent_glucose_anomalies']   ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $frequency): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    {
                        day: "<?php echo e(\Carbon\Carbon::parse($frequency['day'])->format('d/m/Y')); ?>",
                        high_count: <?php echo e($frequency['high_count'] ?? 0); ?>,
                        low_count: <?php echo e($frequency['low_count'] ?? 0); ?>,
                        extremely_high_count: <?php echo e($frequency['extremely_high_count'] ?? 0); ?>,
                        extremely_low_count: <?php echo e($frequency['extremely_low_count'] ?? 0); ?>,
                        total_count: <?php echo e($frequency['total_count'] ?? 0); ?>

                    },
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
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
                        datasets: [{
                            label: 'High Count',
                            data: highCounts,
                            backgroundColor: 'rgba(255, 99, 132, 0.5)',
                            borderColor: 'rgba(255, 99, 132, 1)',
                            borderWidth: 1
                        }, {
                            label: 'Low Count',
                            data: lowCounts,
                            backgroundColor: 'rgba(54, 162, 235, 0.5)',
                            borderColor: 'rgba(54, 162, 235, 1)',
                            borderWidth: 1
                        }, {
                            label: 'Extremely High Count',
                            data: extremelyHighCounts,
                            backgroundColor: 'rgba(255, 159, 64, 0.5)',
                            borderColor: 'rgba(255, 159, 64, 1)',
                            borderWidth: 1
                        }, {
                            label: 'Extremely Low Count',
                            data: extremelyLowCounts,
                            backgroundColor: 'rgba(153, 102, 255, 0.5)',
                            borderColor: 'rgba(153, 102, 255, 1)',
                            borderWidth: 1
                        }]
                    },
                    options: {responsive: true, maintainAspectRatio: false, scales: {y: {beginAtZero: true}}}
                });

                let ctx2 = document.getElementById('exportTooFrequentChart').getContext('2d');
                new Chart(ctx2, {
                    type: 'bar',
                    data: {
                        labels: days,
                        datasets: [{
                            label: 'High Count',
                            data: highCounts,
                            backgroundColor: 'rgba(255, 99, 132, 0.5)',
                            borderColor: 'rgba(255, 99, 132, 1)',
                            borderWidth: 1
                        }, {
                            label: 'Low Count',
                            data: lowCounts,
                            backgroundColor: 'rgba(54, 162, 235, 0.5)',
                            borderColor: 'rgba(54, 162, 235, 1)',
                            borderWidth: 1
                        }, {
                            label: 'Extremely High Count',
                            data: extremelyHighCounts,
                            backgroundColor: 'rgba(255, 159, 64, 0.5)',
                            borderColor: 'rgba(255, 159, 64, 1)',
                            borderWidth: 1
                        }, {
                            label: 'Extremely Low Count',
                            data: extremelyLowCounts,
                            backgroundColor: 'rgba(153, 102, 255, 0.5)',
                            borderColor: 'rgba(153, 102, 255, 1)',
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: false,
                        width: 1200,
                        height: 600,
                        maintainAspectRatio: false,
                        scales: {y: {beginAtZero: true}}
                    }
                });
            });

            document.addEventListener("DOMContentLoaded", function () {
                let frequentSwingData = [
                        <?php $__currentLoopData = $data['too_frequent_time_swings'] ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $swing): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    {
                        day: "<?php echo e(\Carbon\Carbon::parse($swing['Events'][0]['Day'])->format('d/m/Y')); ?>",
                        durations: [<?php $__currentLoopData = $swing['Events']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $event): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?> "<?php echo e(\Carbon\Carbon::parse($event['Duration time swing'])->format('H:i')); ?>", <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?> ],
                        frequency: <?php echo e($swing['Number of Time Swings'] ?? 0); ?>

                    },
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                ];
                let days = frequentSwingData.map(swing => swing.day);
                let uniqueDays = [...new Set(days)];
                let durationDatasets = [];
                let maxSwings = Math.max(...frequentSwingData.map(swing => swing.durations.length));

                for (let i = 0; i < maxSwings; i++) {
                    durationDatasets.push({
                        label: `Time Swing ${i + 1}`,
                        data: uniqueDays.map(day => {
                            let swing = frequentSwingData.find(s => s.day === day);
                            if (swing && swing.durations[i]) {
                                let [hours, minutes] = swing.durations[i].split(":").map(Number);
                                return hours * 60 + minutes;
                            }
                            return null;
                        }),
                        backgroundColor: 'rgba(220, 110, 110, 0.5)',
                        borderColor: 'rgba(220, 110, 110, 1)',
                        borderWidth: 1
                    });
                }
                const formatTimeLabel = (value) => {
                    if (value === null) return '00:00';
                    let hours = Math.floor(value / 60);
                    let minutes = value % 60;
                    return `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}`;
                };

                let ctxDuration = document.getElementById('tooFrequentTimeSwingsDurationChart').getContext('2d');
                new Chart(ctxDuration, {
                    type: 'bar',
                    data: {labels: uniqueDays, datasets: durationDatasets},
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {y: {beginAtZero: true, ticks: {callback: (value) => formatTimeLabel(value)}}},
                        plugins: {
                            tooltip: {
                                callbacks: {
                                    label: function (tooltipItem) {
                                        return `Duration: ${formatTimeLabel(tooltipItem.raw)}`;
                                    }
                                }
                            }
                        }
                    }
                });

                let ctxDuration2 = document.getElementById('exportTooFrequentTimeSwingsDurationChart').getContext('2d');
                new Chart(ctxDuration2, {
                    type: 'bar',
                    data: {labels: uniqueDays, datasets: durationDatasets},
                    options: {
                        responsive: false,
                        width: 1200,
                        height: 600,
                        maintainAspectRatio: false,
                        scales: {y: {beginAtZero: true, ticks: {callback: (value) => formatTimeLabel(value)}}}
                    }
                });

                let frequencyData = frequentSwingData.map(swing => swing.frequency);
                let ctxFrequency = document.getElementById('tooFrequentTimeSwingsFrequencyChart').getContext('2d');
                new Chart(ctxFrequency, {
                    type: 'bar',
                    data: {
                        labels: uniqueDays,
                        datasets: [{
                            label: 'Frequency of Time Swings',
                            data: frequencyData,
                            backgroundColor: 'rgba(153, 102, 255, 0.5)',
                            borderColor: 'rgba(153, 102, 255, 1)',
                            borderWidth: 1
                        }]
                    },
                    options: {responsive: true, maintainAspectRatio: false, scales: {y: {beginAtZero: true}}}
                });

                let ctxFrequency2 = document.getElementById('exportTooFrequentTimeSwingsFrequencyChart').getContext('2d');
                new Chart(ctxFrequency2, {
                    type: 'bar',
                    data: {
                        labels: uniqueDays,
                        datasets: [{
                            label: 'Frequency of Time Swings',
                            data: frequencyData,
                            backgroundColor: 'rgba(153, 102, 255, 0.5)',
                            borderColor: 'rgba(153, 102, 255, 1)',
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: false,
                        width: 1200,
                        height: 600,
                        maintainAspectRatio: false,
                        scales: {y: {beginAtZero: true}}
                    }
                });
            });

            document.addEventListener("DOMContentLoaded", function () {
                let timeSwingData = [
                        <?php $__currentLoopData = $data['time_swing_with_too_long_glucose_anomalies'] ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $swing): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    {
                        day: "<?php echo e(\Carbon\Carbon::parse($swing['day'])->format('d/m/Y')); ?>",
                        duration_time_swing: "<?php echo e(\Carbon\Carbon::parse($swing['duration_time_swing'])->format('H:i')); ?>",
                        // FIX: Aggiunto 'Extremely_low event: ' all'array per gestire l'underscore
                        anomalous_duration: "<?php echo e(\Carbon\Carbon::parse(str_replace(['Low event: ', 'High event: ', 'Extremely high event: ', 'Extremely low event: ', 'Extremely_low event: ', 'Extremely_high event: '], '', $swing['anomalous_durations']))->format('H:i')); ?>",
                        time_swing_type: "<?php echo e($swing['first_event']); ?> to <?php echo e($swing['second_event']); ?>",
                        // FIX: Sostituisce spazi con underscore per matchare l'oggetto colors JS
                        event_type: "<?php echo e(str_replace(' ', '_', strtolower($swing['first_event']))); ?>"
                    },
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                ];

                let days = timeSwingData.map(swing => swing.day);

                // Funzione helper per convertire HH:mm in ore decimali (es. 01:30 -> 1.5)
                const timeToDecimal = (timeStr) => {
                    let [hours, minutes] = timeStr.split(":").map(Number);
                    return hours + (minutes / 60);
                };

                let durationTimeSwing = timeSwingData.map(swing => timeToDecimal(swing.duration_time_swing));
                let anomalousDurations = timeSwingData.map(swing => timeToDecimal(swing.anomalous_duration));
                let eventTypes = timeSwingData.map(swing => swing.event_type);

                let colors = {
                    "high": {bg: "rgba(255, 99, 132, 0.5)", border: "rgba(255, 99, 132, 1)"},
                    "low": {bg: "rgba(54, 162, 235, 0.5)", border: "rgba(54, 162, 235, 1)"},
                    "extremely_high": {bg: "rgba(255, 159, 64, 0.5)", border: "rgba(255, 159, 64, 1)"},
                    "extremely_low": {bg: "rgba(153, 102, 255, 0.5)", border: "rgba(153, 102, 255, 1)"}
                };

                // Configurazione comune per evitare duplicazione codice
                const getChartConfig = (responsive) => {
                    return {
                        type: 'bar',
                        data: {
                            labels: days,
                            datasets: [{
                                label: 'Duration Time Swing (HH:mm)',
                                data: durationTimeSwing,
                                backgroundColor: 'rgba(220, 110, 110, 0.5)',
                                borderColor: 'rgba(220, 110, 110, 1)',
                                borderWidth: 1
                            }, {
                                label: 'Anomalous Duration (HH:mm)',
                                data: anomalousDurations,
                                // Fallback colore sicuro se la chiave non esiste
                                backgroundColor: eventTypes.map(event => colors[event] ? colors[event].bg : 'rgba(200, 200, 200, 0.5)'),
                                borderColor: eventTypes.map(event => colors[event] ? colors[event].border : 'rgba(200, 200, 200, 1)'),
                                borderWidth: 1
                            }]
                        },
                        options: {
                            responsive: responsive,
                            maintainAspectRatio: false,
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    ticks: {
                                        stepSize: 0.5, // Ogni mezz'ora
                                        callback: function (value) {
                                            // Riconverte ore decimali in HH:mm per l'asse
                                            let hours = Math.floor(value);
                                            let minutes = Math.round((value - hours) * 60);
                                            return `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}`;
                                        }
                                    }
                                }
                            },
                            plugins: {
                                tooltip: {
                                    callbacks: {
                                        label: function (context) {
                                            let label = context.dataset.label || '';
                                            if (label) {
                                                label += ': ';
                                            }
                                            let value = context.raw;
                                            let hours = Math.floor(value);
                                            let minutes = Math.round((value - hours) * 60);
                                            return label + `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}`;
                                        }
                                    }
                                }
                            }
                        }
                    };
                };

                // Chart 1 (Principale)
                let ctx = document.getElementById('timeSwingTooLongGlucoseAnomaliesChart').getContext('2d');
                new Chart(ctx, getChartConfig(true));

                // Chart 2 (Export - non responsive)
                let ctx2 = document.getElementById('exportTimeSwingTooLongGlucoseAnomaliesChart').getContext('2d');
                let exportConfig = getChartConfig(false);
                // Override dimensioni fisse per export
                ctx2.canvas.width = 1200;
                ctx2.canvas.height = 600;
                new Chart(ctx2, exportConfig);
            });
            // --- 7. Frequent Ext. Time Swings (NEW CHARTS) ---
            document.addEventListener("DOMContentLoaded", function () {
                let freqData = [
                        <?php $__currentLoopData = $data['too_frequent_extremely_time_swings'] ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $swing): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?> {
                        day: "<?php echo e(\Carbon\Carbon::parse($swing['Events'][0]['Day'])->format('d/m/Y')); ?>",
                        freq: <?php echo e($swing['Number of Time Swings'] ?? 0); ?>,
                        // Raccogliamo le durate per calcolare la media
                        durs: [
                            <?php $__currentLoopData = $swing['Events']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ev): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                "<?php echo e(\Carbon\Carbon::parse($ev['Duration time swing'])->format('H:i')); ?>",
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        ]
                    }, <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                ];

                let days = freqData.map(d => d.day);
                let uniqueDays = [...new Set(days)];

                // 7A. Frequency Chart
                let ctxFreq = document.getElementById('tooFrequentExtremelyTimeSwingsFrequencyChart').getContext('2d');
                new Chart(ctxFreq, {
                    type: 'bar',
                    data: {
                        labels: uniqueDays,
                        datasets: [{
                            label: 'Frequency',
                            data: freqData.map(d => d.freq),
                            backgroundColor: 'rgba(111, 66, 193, 0.5)', // Purple
                            borderColor: 'rgba(111, 66, 193, 1)',
                            borderWidth: 1
                        }]
                    },
                    options: {responsive: true, scales: {y: {beginAtZero: true}}}
                });

                // 7B. Duration Chart (Average duration per day)
                let avgDurs = freqData.map(d => {
                    let totalMin = 0;
                    let count = 0;
                    d.durs.forEach(t => {
                        let [h, m] = t.split(":").map(Number);
                        totalMin += h * 60 + m;
                        count++;
                    });
                    return count > 0 ? totalMin / count : 0;
                });

                let ctxDur = document.getElementById('tooFrequentExtremelyTimeSwingsDurationChart').getContext('2d');
                new Chart(ctxDur, {
                    type: 'bar',
                    data: {
                        labels: uniqueDays,
                        datasets: [{
                            label: 'Avg Duration (min)',
                            data: avgDurs,
                            backgroundColor: 'rgba(111, 66, 193, 0.5)', // Purple
                            borderColor: 'rgba(111, 66, 193, 1)',
                            borderWidth: 1
                        }]
                    },
                    options: {responsive: true, scales: {y: {beginAtZero: true}}}
                });
            });

            document.addEventListener("DOMContentLoaded", function () {
                let complexData = [
                        <?php $__currentLoopData = $data['extremely_time_swing_with_too_long_glucose_anomalies'] ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $swing): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    {
                        day: "<?php echo e(\Carbon\Carbon::parse($swing['day'])->format('d/m/Y')); ?>",

                        // Durata Swing (Pulita)
                        durSwing: "<?php echo e(\Carbon\Carbon::parse($swing['duration_time_swing'])->format('H:i')); ?>",

                        // Durata Anomalia (Sporca - La passiamo grezza)
                        rawAnom: "<?php echo e($swing['anomalous_durations']); ?>"
                    },
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                ];

                let days = complexData.map(d => d.day);

                // Funzione Helper per pulire e convertire in ore
                const toHours = (t) => {
                    if (!t) return 0;
                    // Rimuove testo tipo "Extremely_high event: " lasciando solo 02:30:00
                    let cleanTime = t.replace(/[^0-9:]/g, '');
                    let parts = cleanTime.split(":");
                    if (parts.length < 2) return 0;
                    return parseInt(parts[0]) + parseInt(parts[1]) / 60; // Ore + minuti decimali
                };

                let ctx = document.getElementById('extTimeSwingTooLongChart').getContext('2d');
                new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: days,
                        datasets: [
                            {
                                label: 'Swing Duration (h)',
                                data: complexData.map(d => toHours(d.durSwing)),
                                backgroundColor: 'rgba(111, 66, 193, 0.5)', // Purple
                                borderColor: 'rgba(111, 66, 193, 1)',
                                borderWidth: 1
                            },
                            {
                                label: 'Anomaly Duration (h)',
                                data: complexData.map(d => toHours(d.rawAnom)),
                                backgroundColor: 'rgba(220, 53, 69, 0.5)', // Red for danger
                                borderColor: 'rgba(220, 53, 69, 1)',
                                borderWidth: 1
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        scales: {
                            y: {
                                beginAtZero: true,
                                title: {display: true, text: 'Hours'}
                            }
                        },
                        plugins: {
                            tooltip: {
                                callbacks: {
                                    label: function (context) {
                                        let val = context.raw;
                                        let h = Math.floor(val);
                                        let m = Math.round((val - h) * 60);
                                        return context.dataset.label + ": " + h + "h " + m + "m";
                                    }
                                }
                            }
                        }
                    }
                });
            });
        </script>

        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script src="https://cdn.datatables.net/1.13.1/js/jquery.dataTables.min.js"></script>
        <script src="https://cdn.datatables.net/1.13.1/js/dataTables.bootstrap5.min.js"></script>
        <script src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/moment.min.js"></script>
        <script src="https://cdn.datatables.net/plug-ins/1.13.6/sorting/datetime-moment.js"></script>
        <script src="https://html2canvas.hertzen.com/dist/html2canvas.min.js"></script>
    <?php $__env->stopSection(); ?>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.master', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Users/lorenzotucceri/Progetti/ISEQL/laravel-iseql/resources/views/patientDetails.blade.php ENDPATH**/ ?>