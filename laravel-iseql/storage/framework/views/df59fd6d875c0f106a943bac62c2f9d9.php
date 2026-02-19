<?php $__env->startSection('title'); ?>
    Patient: <?php echo e($patient->name); ?> <?php echo e($patient->surname); ?>

<?php $__env->stopSection(); ?>

<?php $__env->startSection('css'); ?>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css"/>
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.2.2/css/buttons.dataTables.min.css">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">

    <style>
        /* Stile Bottone Live Monitor */
        .btn-live-monitor {
            background: linear-gradient(45deg, #ff3b30, #ff9500);
            border: none;
            color: white;
            font-weight: 600;
            box-shadow: 0 4px 15px rgba(255, 59, 48, 0.4);
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .btn-live-monitor:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(255, 59, 48, 0.6);
            color: white;
        }

        .pulse-icon {
            animation: pulse-animation 2s infinite;
        }

        @keyframes pulse-animation {
            0% {
                transform: scale(1);
                opacity: 1;
            }
            50% {
                transform: scale(1.2);
                opacity: 0.8;
            }
            100% {
                transform: scale(1);
                opacity: 1;
            }
        }

        .badge-no-model {
            background-color: #f0f2f5;
            color: #74788d;
            border: 1px dashed #ced4da;
            padding: 8px 15px;
            border-radius: 50rem;
        }

        /* --- NUOVO CSS PER I FILTRI --- */
        .filter-card {
            background-color: #f8f9fa;
            border: 1px solid #eff2f7;
            border-radius: 8px;
        }

        .filter-btn {
            border: 1px solid transparent;
            font-weight: 500;
            transition: all 0.2s;
        }

        .filter-btn.active {
            border-color: #556ee6;
            background-color: #eff2f7;
            color: #556ee6;
        }

        .filter-btn:hover {
            transform: translateY(-1px);
        }
    </style>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>

    <?php $__env->startComponent('components.breadcrumb'); ?>
        <?php $__env->slot('li_1'); ?>
            Patients
        <?php $__env->endSlot(); ?>
        <?php $__env->slot('title'); ?>
            Patient Details
        <?php $__env->endSlot(); ?>
    <?php echo $__env->renderComponent(); ?>

    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-body">

                    <?php $__errorArgs = ["error"];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                    <div class="alert alert-danger" role="alert"><?php echo e($message); ?></div>
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>

                    <?php if(\Session::has('success')): ?>
                        <div class="alert alert-success" role="alert"><?php echo e(Session::get('success')); ?></div>
                    <?php endif; ?>

                    <div class="d-flex justify-content-between align-items-center mb-4 pb-3 border-bottom">
                        <div>
                            <h3 class="mb-1">Patient: <?php echo e($patient->name.' '.$patient->surname); ?></h3>
                            <p class="text-muted mb-0">Manage CSV files and AI Monitoring</p>
                        </div>

                        <div>
                            <?php if($patient->has_trained_model): ?>
                                <button type="button" class="btn btn-live-monitor rounded-pill px-4 py-2"
                                        onclick="openForecastingModal()">
                                    <i class="mdi mdi-access-point-network pulse-icon font-size-18"></i>
                                    Start Live Monitor
                                </button>
                            <?php else: ?>
                                <span class="badge-no-model" data-bs-toggle="tooltip"
                                      title="Upload at least 15 days of CSV data to enable AI">
                                    <i class="mdi mdi-alert-circle-outline me-1"></i> AI Model Not Ready
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="accordion mb-4" id="uploadAccordion">
                        <div class="accordion-item border-0 shadow-sm">
                            <h2 class="accordion-header" id="headingUpload">
                                <button class="accordion-button collapsed bg-light fw-semibold" type="button"
                                        data-bs-toggle="collapse" data-bs-target="#collapseUpload" aria-expanded="false"
                                        aria-controls="collapseUpload">
                                    <i class="mdi mdi-cloud-upload-outline me-2 font-size-18"></i> Upload New CSV File
                                </button>
                            </h2>
                            <div id="collapseUpload" class="accordion-collapse collapse" aria-labelledby="headingUpload"
                                 data-bs-parent="#uploadAccordion">
                                <div class="accordion-body">
                                    <form action="<?php echo e(route('uploadCsv')); ?>" method="POST" enctype="multipart/form-data">
                                        <?php echo csrf_field(); ?>
                                        <input type="hidden" name="patient_id" value="<?php echo e($patient->id); ?>">
                                        <input type="hidden" name="role" value="<?php echo e(auth()->user()->role->name); ?>">

                                        <?php if(auth()->user()->role->name == "Patient"): ?>
                                            <input type="hidden" name="doctor"
                                                   value="<?php echo e(auth()->user()->patient ? auth()->user()->patient->doctor_id : ''); ?>">
                                        <?php else: ?>
                                            <input type="hidden" name="doctor" value="<?php echo e(auth()->user()->id); ?>">
                                        <?php endif; ?>

                                        <div class="row align-items-end">
                                            <div class="col-md-8">
                                                <label class="form-label">Select File(s)</label>
                                                <input id="csv" name="csv[]" type="file"
                                                       class="form-control <?php $__errorArgs = ['csv'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" multiple
                                                       required>
                                                <?php $__errorArgs = ['csv'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                                <span class="invalid-feedback"
                                                      role="alert"><strong><?php echo e($message); ?></strong></span>
                                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                            </div>
                                            <div class="col-md-4">
                                                <button type="submit" class="btn btn-primary w-100">
                                                    <i class="mdi mdi-upload me-1"></i> Upload
                                                </button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card filter-card mb-4">
                        <div class="card-body p-3">
                            <div class="row g-3 align-items-center">
                                <div class="col-md-auto">
                                    <h6 class="mb-0 text-muted"><i class="mdi mdi-filter-variant me-1"></i> Filters:
                                    </h6>
                                </div>

                                <div class="col-md-auto">
                                    <div class="btn-group" role="group" aria-label="GMI Filter">
                                        <button type="button" class="btn btn-sm btn-outline-secondary filter-btn active"
                                                onclick="filterGMI('all', this)">All
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-success filter-btn"
                                                onclick="filterGMI('Good', this)">Good
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-warning filter-btn"
                                                onclick="filterGMI('Warning', this)">Warning
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-danger filter-btn"
                                                onclick="filterGMI('High', this)">High
                                        </button>
                                    </div>
                                </div>

                                <div class="col-md-auto ms-md-auto">
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text border-0 bg-transparent"><i
                                                class="mdi mdi-calendar"></i></span>
                                        <input type="text" id="date-range-filter"
                                               class="form-control form-control-sm border rounded"
                                               placeholder="Filter by Start Date...">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover table-bordered dt-responsive nowrap w-100 yajra-datatable align-middle" id="csvTable">
                            <thead class="bg-light text-uppercase table-light">
                            <tr>
                                <th style="width: 25%">File Name</th>
                                <th>Start Date</th>
                                <th>End Date</th>
                                <th>GMI (Est. A1c)</th>
                                <th>Actions</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php $__currentLoopData = $files; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $file): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr>
                                    <td>

                                        <div class="text-truncate" style="max-width: 180px;" data-bs-toggle="tooltip"
                                             title="<?php echo e(basename($file->csv_file_path)); ?>">
                                            <span
                                                class="fw-medium text-dark"><?php echo e(basename($file->csv_file_path)); ?></span>
                                        </div>

                                    </td>
                                    <td><?php echo e(\Carbon\Carbon::parse($file->start_time)->format('Y-m-d H:i')); ?></td>
                                    <td><?php echo e(\Carbon\Carbon::parse($file->end_time)->format('Y-m-d H:i')); ?></td>
                                    <td data-gmi="<?php echo e($file->gmi); ?>">
                                        <div class="d-flex align-items-center">
                                            <span class="fw-bold me-2"><?php echo e($file->gmi); ?>%</span>
                                            <?php if($file->gmi < 7): ?>
                                                <span class="badge badge-soft-success status-badge">Good</span>
                                            <?php elseif($file->gmi >= 7 && $file->gmi <= 8): ?>
                                                <span class="badge badge-soft-warning status-badge">Warning</span>
                                            <?php else: ?>
                                                <span class="badge badge-soft-danger status-badge">High</span>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <a href="<?php echo e(route('viewCsv', ['csvId' => $file->id, 'patientId' => $patient->id])); ?>"
                                               target="_blank" class="btn btn-sm btn-soft-primary"
                                               data-bs-toggle="tooltip" title="View Analysis">
                                                <i class="mdi mdi-chart-box-outline font-size-18"></i>
                                            </a>

                                            <?php
                                                $isDoc = auth()->user()->role->name == 'Doctor';
                                                $targetId = $isDoc ? auth()->user()->id : (auth()->user()->patient->doctor_id ?? 0);
                                            ?>

                                            <?php if($targetId != 0): ?>
                                                <a href="#feedbackModal"
                                                   onclick="openFeedbackModal(<?php echo e($file->id); ?>, <?php echo e($isDoc ? 'true' : 'false'); ?>, <?php echo e($targetId); ?>)"
                                                   data-bs-toggle="modal" class="btn btn-sm btn-soft-info"
                                                   >
                                                    <i class="mdi mdi-comment-text-outline font-size-15" data-bs-toggle="tooltip" title="Feedback"></i>
                                                </a>
                                            <?php endif; ?>

                                            <a href="#csvDelete" id="<?php echo e($file->id); ?>" onclick="deleteCsv(this.id)"
                                               data-bs-toggle="modal" class="btn btn-sm btn-soft-danger"
                                               >
                                                <i class="mdi mdi-trash-can-outline font-size-15" data-bs-toggle="tooltip" title="Delete"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="forecastingModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-soft-primary">
                    <h5 class="modal-title text-primary"><i class="mdi mdi-access-point-network me-2"></i>Live Real-Time
                        Monitor</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="<?php echo e(route('monitor.view')); ?>" method="POST" target="_blank">
                    <?php echo csrf_field(); ?>
                    <div class="modal-body p-4">
                        <div class="text-center mb-4">
                            <p class="text-muted">Enter Dexcom credentials for <strong><?php echo e($patient->name); ?></strong>.
                            </p>
                        </div>
                        <input type="hidden" name="patient_id" value="<?php echo e($patient->id); ?>">
                        <div class="form-floating mb-3">
                            <input type="text" class="form-control" id="dexcom_user" name="username"
                                   placeholder="Username" required>
                            <label>Dexcom Username / Email</label>
                        </div>
                        <div class="form-floating mb-3">
                            <input type="password" class="form-control" id="dexcom_pass" name="password"
                                   placeholder="Password" required>
                            <label>Dexcom Password</label>
                        </div>
                    </div>
                    <div class="modal-footer border-0">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary px-4">Launch</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="feedbackModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header"><h5 class="modal-title">Feedback</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="feedbackForm" method="POST" action="<?php echo e(route('saveFeedback')); ?>">
                        <?php echo csrf_field(); ?>
                        <input type="hidden" id="csvFileId" name="file_id"><input type="hidden" id="doctorId"
                                                                                  name="doctor_id">
                        <div class="mb-3">
                            <label class="form-label">Doctor:</label>
                            <textarea id="feedbackTextDoctor" name="feedbackDoctor" class="form-control" rows="3"
                                      <?php if(auth()->user()->role->name=="Patient"): ?> readonly <?php endif; ?>></textarea>
                        </div>
                        <div class="mb-3" id="labelPatient">
                            <label class="form-label">Patient:</label>
                            <textarea id="feedbackTextPatient" name="feedbackPatient" class="form-control" rows="3"
                                      <?php if(auth()->user()->role->name=="Doctor"): ?> readonly <?php endif; ?>></textarea>
                        </div>
                        <div class="d-flex justify-content-center">
                            <button type="submit" id="saveFeedbackButton" class="btn btn-primary">Send</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="csvDelete" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content">
                <div class="modal-body px-4 py-5 text-center">
                    <button type="button" class="btn-close position-absolute end-0 top-0 m-3"
                            data-bs-dismiss="modal"></button>
                    <div class="avatar-sm mb-4 mx-auto">
                        <div class="avatar-title bg-warning text-warning bg-opacity-10 font-size-20 rounded-3"><i
                                class="mdi mdi-trash-can-outline"></i></div>
                    </div>
                    <p class="text-muted font-size-16 mb-4">Delete CSV file?</p>
                    <form action="<?php echo e(route('deleteCsv')); ?>" method="POST"><?php echo csrf_field(); ?><input type="hidden" name="csv_id"
                                                                                      id="boxDelete">
                        <div class="hstack gap-2 justify-content-center mb-0">
                            <button type="submit" class="btn btn-danger">Delete</button>
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

<?php $__env->stopSection(); ?>

<?php $__env->startSection('script'); ?>
    <script src="https://cdn.datatables.net/1.13.1/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.1/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

    <?php if(!$patient->has_trained_model): ?>
        <script>
            // Controlla ogni 5 secondi se il modello è pronto nel DB
            let modelInterval = setInterval(function() {
                fetch("/api/check-model-status/<?php echo e($patient->id); ?>")
                    .then(response => response.json())
                    .then(data => {
                        if (data.ready) {
                            clearInterval(modelInterval);
                            // Notifica carina e ricarica
                            alert("AI Model Training Completed! The Live Monitor is now available.");
                            location.reload();
                        }
                    });
            }, 5000);
        </script>
    <?php endif; ?>

    <script>
        // --- 1. CONFIGURAZIONE DATATABLE E FILTRI ---
        $(document).ready(function () {
            // Inizializza DataTable
            var table = $('#csvTable').DataTable({
                order: [[1, "desc"]], // Ordina per Start Date
                columnDefs: [{orderable: false, targets: -1}],
                language: {search: "", searchPlaceholder: "Search files..."},
                dom: 'rtip' // Nascondiamo la barra di ricerca default, la gestiamo noi o lasciamo pulito
            });

            // --- FILTRO GMI PERSONALIZZATO ---
            $.fn.dataTable.ext.search.push(
                function (settings, data, dataIndex) {
                    var status = $('#selectedGMI').val(); // Valore dal bottone cliccato
                    var rowStatus = $(table.row(dataIndex).node()).find('.status-badge').text().trim();

                    if (status === 'all' || status === '') {
                        return true;
                    }
                    return rowStatus === status;
                }
            );

            // --- FILTRO DATE PERSONALIZZATO ---
            $.fn.dataTable.ext.search.push(
                function (settings, data, dataIndex) {
                    var min = $('#date-start').val();
                    var max = $('#date-end').val();
                    // Colonna 1: Start Date (Assicurati che l'indice sia corretto)
                    var dateStr = data[1];

                    if (!min && !max) return true;

                    // Conversione semplice per confronto (formato YYYY-MM-DD HH:mm)
                    var date = new Date(dateStr);
                    var minDate = min ? new Date(min) : null;
                    var maxDate = max ? new Date(max) : null;

                    if (
                        (minDate === null && maxDate === null) ||
                        (minDate === null && date <= maxDate) ||
                        (minDate <= date && maxDate === null) ||
                        (minDate <= date && date <= maxDate)
                    ) {
                        return true;
                    }
                    return false;
                }
            );

            // Datepicker (Flatpickr)
            flatpickr("#date-range-filter", {
                mode: "range",
                dateFormat: "Y-m-d",
                onClose: function (selectedDates, dateStr, instance) {
                    // Logica un po' spartana per demo: prendiamo il range e lo dividiamo
                    if (selectedDates.length === 2) {
                        // Creiamo campi hidden virtuali per il filtro
                        $('#date-start').val(selectedDates[0].toISOString());
                        $('#date-end').val(selectedDates[1].toISOString());
                    } else {
                        $('#date-start').val('');
                        $('#date-end').val('');
                    }
                    table.draw();
                }
            });

            // Campi hidden per le date
            $('<input>').attr({type: 'hidden', id: 'date-start'}).appendTo('body');
            $('<input>').attr({type: 'hidden', id: 'date-end'}).appendTo('body');
            $('<input>').attr({type: 'hidden', id: 'selectedGMI', value: 'all'}).appendTo('body');
        });

        // Funzione chiamata dai bottoni GMI
        function filterGMI(status, btn) {
            // Aggiorna stile bottoni
            $('.filter-btn').removeClass('active');
            $(btn).addClass('active');

            // Imposta valore filtro e ridisegna
            $('#selectedGMI').val(status);
            $('#csvTable').DataTable().draw();
        }

        // --- 2. FUNZIONI MODALI ---
        function openForecastingModal() {
            var myModal = new bootstrap.Modal(document.getElementById('forecastingModal'));
            myModal.show();
        }

        function deleteCsv(id) {
            document.getElementById('boxDelete').value = id;
        }

        function openFeedbackModal(csvId, isDoctor, doctorId) {
            // ... (Logica Feedback mantenuta identica a prima) ...
            document.getElementById('csvFileId').value = csvId;
            document.getElementById('doctorId').value = doctorId;
            let fbDoc = document.getElementById('feedbackTextDoctor');
            let fbPat = document.getElementById('feedbackTextPatient');
            let btn = document.getElementById('saveFeedbackButton');

            fbDoc.value = "Loading...";
            fbPat.value = "Loading...";

            fetch(`/get-feedback/${csvId}/${doctorId}`)
                .then(r => r.ok ? r.json() : Promise.reject("Error"))
                .then(d => {
                    fbDoc.value = d.message_doctor || "";
                    fbPat.value = d.message_patient || "";
                    if (isDoctor) {
                        fbDoc.readOnly = fbPat.value.trim() !== "";
                        btn.style.display = fbPat.value.trim() ? "none" : "block";
                    } else {
                        fbPat.readOnly = false;
                        fbDoc.readOnly = true;
                        btn.style.display = fbDoc.value.trim() ? "block" : "none";
                    }
                    document.getElementById('labelPatient').style.display = fbDoc.value.trim() ? "block" : "none";
                })
                .catch(e => {
                    console.error(e);
                    fbDoc.value = "";
                    fbPat.value = "";
                });
        }

        $(document).ready(function () {
            $('[data-bs-toggle="tooltip"]').tooltip();
        });
    </script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.master', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Users/lorenzotucceri/Progetti/ISEQL/laravel-iseql/resources/views/csvPatient.blade.php ENDPATH**/ ?>