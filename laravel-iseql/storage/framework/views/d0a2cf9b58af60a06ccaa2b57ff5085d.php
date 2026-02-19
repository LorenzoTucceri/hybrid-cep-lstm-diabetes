<?php $__env->startSection('title'); ?>
    Dashboard
<?php $__env->stopSection(); ?>

<?php $__env->startSection("css"); ?>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css"/>
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.2.2/css/buttons.dataTables.min.css">

    <style>
        /* Modern Card Styling */
        .card {
            border: none;
            box-shadow: 0 0.75rem 1.5rem rgba(18, 38, 63, 0.03);
            border-radius: 0.75rem;
            transition: all 0.3s ease;
            margin-bottom: 24px;
        }
        .card:hover {
            box-shadow: 0 1rem 3rem rgba(18, 38, 63, 0.08);
        }

        /* Stat Widgets */
        .mini-stats-wid .mini-stat-icon {
            width: 50px;
            height: 50px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            font-size: 24px;
        }

        /* Soft Backgrounds */
        .bg-soft-primary { background-color: rgba(85, 110, 230, 0.1) !important; color: #556ee6 !important; }
        .bg-soft-success { background-color: rgba(52, 195, 143, 0.1) !important; color: #34c38f !important; }
        .bg-soft-warning { background-color: rgba(241, 180, 76, 0.1) !important; color: #f1b44c !important; }
        .bg-soft-danger { background-color: rgba(244, 106, 106, 0.1) !important; color: #f46a6a !important; }
        .bg-soft-info { background-color: rgba(80, 165, 241, 0.1) !important; color: #50a5f1 !important; }

        /* Profile Card specifics */
        .profile-user-wid {
            margin-top: -26px;
        }
        .profile-user-wid img {
            border: 3px solid #fff;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        /* Table Actions */
        .btn-soft-primary { background-color: rgba(85, 110, 230, 0.1); color: #556ee6; border: none; }
        .btn-soft-primary:hover { background-color: #556ee6; color: #fff; }

        .btn-soft-info { background-color: rgba(80, 165, 241, 0.1); color: #50a5f1; border: none; }
        .btn-soft-info:hover { background-color: #50a5f1; color: #fff; }

        .btn-soft-danger { background-color: rgba(244, 106, 106, 0.1); color: #f46a6a; border: none; }
        .btn-soft-danger:hover { background-color: #f46a6a; color: #fff; }
    </style>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>

    <?php $__env->startComponent('components.breadcrumb'); ?>
        <?php $__env->slot('li_1'); ?> Dashboard <?php $__env->endSlot(); ?>
        <?php $__env->slot('title'); ?> Dashboard <?php $__env->endSlot(); ?>
    <?php echo $__env->renderComponent(); ?>

    <div class="row">
        <div class="col-12">
            <?php $__errorArgs = ["error"];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="mdi mdi-block-helper me-2"></i> <?php echo e($message); ?>

                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            <?php if(\Session::has('success')): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="mdi mdi-check-all me-2"></i> <?php echo e(Session::get('success')); ?>

                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
        </div>

        <div class="col-xl-4">
            <div class="card overflow-hidden">
                <div class="bg-primary bg-soft">
                    <div class="row">
                        <div class="col-7">
                            <div class="text-primary p-3">
                                <h5 class="text-primary">Welcome Back!</h5>
                                <p class="mb-0">Glucose Analysis </p><br>
                            </div>
                        </div>
                        <div class="col-5 align-self-end text-end">
                            <i class="bx bx-pulse text-primary" style="font-size: 6rem; opacity: 0.3; margin-right: 10px; margin-bottom: -10px;"></i>
                        </div>
                    </div>
                </div>
                <div class="card-body pt-0">
                    <div class="row">
                        <div class="col-sm-12">
                            <div class="d-flex align-items-end">
                                <div class="avatar-md profile-user-wid mb-2 me-3">
                                    <img src="/images/avatar-default.jpeg" alt="" class="img-thumbnail rounded-circle">
                                </div>
                                <div class="mb-3">
                                    <h5 class="font-size-15 mb-1"><?php echo e(Str::ucfirst(Auth::user()->name)." ".Str::ucfirst(Auth::user()->surname)); ?></h5>
                                    <p class="text-muted mb-0 font-size-12 badge badge-soft-primary"><?php echo e(Str::ucfirst(Auth::user()->role->name)); ?></p>
                                </div>
                            </div>
                        </div>

                        <div class="col-sm-12">
                            <div class="pt-3 border-top">
                                <div class="row">
                                    <div class="col-12">
                                        <p class="text-muted mb-1 font-size-12"><i class="mdi mdi-email-outline me-1"></i> Email</p>
                                        <h6 class="font-size-14"><?php echo e(Str::ucfirst(Auth::user()->email)); ?></h6>
                                    </div>
                                </div>
                                <div class="mt-3 d-grid">
                                    <a href="<?php echo e(Route('userProfile')); ?>" class="btn btn-primary waves-effect waves-light btn-sm">
                                        View Full Profile <i class="mdi mdi-arrow-right ms-1"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <div class="avatar-sm me-3">
                            <span class="avatar-title bg-soft-info text-info rounded-circle font-size-18">
                                <i class="bx bx-bulb"></i>
                            </span>
                        </div>
                        <h5 class="card-title mb-0">Project Overview</h5>
                    </div>
                    <p class="text-muted mb-0">
                        This project focuses on the analysis of glucose data to model various glucose conditions, using a
                        multi-phase process for event detection and pattern recognition. The goal is to improve diabetes
                        management and support personalized treatment strategies.
                    </p>
                </div>
            </div>
        </div>

        <div class="col-xl-8">
            <?php if(auth()->user()->role->name == "Admin"): ?>
                <div class="row">
                    <div class="col-md-4">
                        <div class="card mini-stats-wid">
                            <div class="card-body">
                                <div class="d-flex">
                                    <div class="flex-grow-1">
                                        <p class="text-muted fw-medium mb-2">Doctors</p>
                                        <h4 class="mb-0"><?php echo e(\App\Models\User::where("role_id","3")->count()); ?></h4>
                                    </div>
                                    <div class="flex-shrink-0 align-self-center">
                                        <div class="mini-stat-icon avatar-sm rounded-circle bg-soft-primary text-primary">
                                            <i class="fas fa-user-md font-size-22"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card mini-stats-wid">
                            <div class="card-body">
                                <div class="d-flex">
                                    <div class="flex-grow-1">
                                        <p class="text-muted fw-medium mb-2">Patients</p>
                                        <h4 class="mb-0"><?php echo e(\App\Models\Patient::count()); ?></h4>
                                    </div>
                                    <div class="flex-shrink-0 align-self-center">
                                        <div class="mini-stat-icon avatar-sm rounded-circle bg-soft-success text-success">
                                            <i class="bx bxs-user-detail font-size-22"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card mini-stats-wid">
                            <div class="card-body">
                                <div class="d-flex">
                                    <div class="flex-grow-1">
                                        <p class="text-muted fw-medium mb-2">CSV Files</p>
                                        <h4 class="mb-0"><?php echo e(\App\Models\File::count()); ?></h4>
                                    </div>
                                    <div class="flex-shrink-0 align-self-center">
                                        <div class="mini-stat-icon avatar-sm rounded-circle bg-soft-warning text-warning">
                                            <i class="bx bx-copy-alt font-size-22"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <div class="row">
                    <?php if(auth()->user()->role->name != "Patient"): ?>
                        <div class="col-md-6">
                            <div class="card mini-stats-wid">
                                <div class="card-body">
                                    <div class="d-flex">
                                        <div class="flex-grow-1">
                                            <p class="text-muted fw-medium mb-2">My Patients</p>
                                            <h4 class="mb-0"><?php echo e(\App\Models\Patient::where('doctor_id', Auth::user()->id)->count()); ?></h4>
                                        </div>
                                        <div class="flex-shrink-0 align-self-center">
                                            <div class="mini-stat-icon avatar-sm rounded-circle bg-soft-primary text-primary">
                                                <i class="bx bxs-user-detail font-size-22"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card mini-stats-wid">
                                <div class="card-body">
                                    <div class="d-flex">
                                        <div class="flex-grow-1">
                                            <p class="text-muted fw-medium mb-2">Patient Files</p>
                                            <h4 class="mb-0">
                                                <?php echo e(\App\Models\File::whereIn('patient_id', \App\Models\Patient::where('doctor_id', Auth::user()->id)->pluck('id'))->count()); ?>

                                            </h4>
                                        </div>
                                        <div class="flex-shrink-0 align-self-center">
                                            <div class="mini-stat-icon avatar-sm rounded-circle bg-soft-warning text-warning">
                                                <i class="bx bx-copy-alt font-size-22"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="col-md-6">
                            <div class="card mini-stats-wid">
                                <div class="card-body">
                                    <div class="d-flex">
                                        <div class="flex-grow-1">
                                            <p class="text-muted fw-medium mb-2">Feedback Received</p>
                                            <h4 class="mb-0">
                                                <?php echo e(\App\Models\Feedback::whereHas('file', function($query) {
                                                    $query->where('patient_id', auth()->user()->patient_id);
                                                })->count()); ?>

                                            </h4>
                                        </div>
                                        <div class="flex-shrink-0 align-self-center">
                                            <div class="mini-stat-icon avatar-sm rounded-circle bg-soft-info text-info">
                                                <i class="bx bxs-message-rounded font-size-22"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card mini-stats-wid">
                                <div class="card-body">
                                    <div class="d-flex">
                                        <div class="flex-grow-1">
                                            <p class="text-muted fw-medium mb-2">My CSV Files</p>
                                            <h4 class="mb-0"><?php echo e(\App\Models\File::where("patient_id", Auth::user()->patient->id)->count()); ?></h4>
                                        </div>
                                        <div class="flex-shrink-0 align-self-center">
                                            <div class="mini-stat-icon avatar-sm rounded-circle bg-soft-warning text-warning">
                                                <i class="bx bx-copy-alt font-size-22"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <div class="card">
                <div class="card-body">

                    <?php if(auth()->user()->role->name == "Patient"): ?>
                        <?php
                            $files = \App\Models\File::where("patient_id", auth()->user()->patient_id)->orderBy('id', 'desc')->get();
                        ?>

                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h4 class="card-title mb-0">Latest CSV Uploads</h4>
                        </div>

                        <div class="table-responsive">
                            <table
                                class="table table-hover table-bordered dt-responsive nowrap w-100 yajra-datatable-file align-middle"
                                id="csvTable">
                                <thead class="bg-light text-uppercase table-light">
                                <tr>
                                    <th>File Name</th>
                                    <th>Start Time</th>
                                    <th>End Time</th>
                                    <th class="text-center">Actions</th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php $__currentLoopData = $files; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $file): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="avatar-xs me-2">
                                                    <span class="avatar-title rounded-circle bg-soft-success text-success font-size-16">
                                                        <i class="bx bx-file"></i>
                                                    </span>
                                                </div>
                                                <span class="text-truncate" style="max-width: 200px;" title="<?php echo e($file->csv_file_path); ?>"><?php echo e(basename($file->csv_file_path)); ?></span>
                                            </div>
                                        </td>
                                        <td><?php echo e(\Carbon\Carbon::parse($file->start_time)->format('d M Y, H:i')); ?></td>
                                        <td><?php echo e(\Carbon\Carbon::parse($file->end_time)->format('d M Y, H:i')); ?></td>
                                        <td class="text-center">
                                            <div class="d-flex justify-content-center gap-2">
                                                <a href="<?php echo e(route('viewCsv', ['csvId' => $file->id, 'patientId' => auth()->user()->patient_id])); ?>" target="_blank" class="btn btn-sm btn-soft-primary" data-bs-toggle="tooltip" title="Analyze">
                                                    <i class="mdi mdi-eye-outline font-size-14"></i>
                                                </a>
                                                <a href="javascript:void(0);" onclick="openFeedbackModal(<?php echo e($file->id); ?>, <?php echo e(auth()->user()->patient->doctor_id); ?>)" data-bs-toggle="modal" data-bs-target="#feedbackModal" class="btn btn-sm btn-soft-info" data-bs-toggle="tooltip" title="Feedback">
                                                    <i class="mdi mdi-comment-eye-outline font-size-14"></i>
                                                </a>
                                                <a href="javascript:void(0);" onclick="deleteCsv(<?php echo e($file->id); ?>)" data-bs-toggle="modal" data-bs-target="#csvDelete" class="btn btn-sm btn-soft-danger" data-bs-toggle="tooltip" title="Delete">
                                                    <i class="mdi mdi-delete-outline font-size-14"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </tbody>
                            </table>
                        </div>

                    <?php else: ?>
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h4 class="card-title mb-0">Latest Added Patients</h4>
                        </div>

                        <div class="table-responsive">
                            <table
                                class="table table-hover table-bordered dt-responsive nowrap w-100 yajra-datatable-patient align-middle"
                                id="csvTable">
                                <thead class="bg-light text-uppercase table-light">
                                <tr>
                                    <th>Patient Name</th>
                                    <th>Email</th>
                                    <th class="text-center">Actions</th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php $__currentLoopData = \App\Models\Patient::all()->sortByDesc('id'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $patient): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <?php if(auth()->user()->role->name == "Doctor"): ?>
                                        <?php if($patient->doctor_id == auth()->user()->id): ?>
                                            <tr>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <div class="avatar-xs me-3">
                                                            <span class="avatar-title rounded-circle bg-soft-primary text-primary font-size-16">
                                                                <?php echo e(strtoupper(substr($patient->name, 0, 1))); ?>

                                                            </span>
                                                        </div>
                                                        <h5 class="font-size-14 mb-0"><?php echo e($patient->name); ?> <?php echo e($patient->surname); ?></h5>
                                                    </div>
                                                </td>
                                                <td><?php echo e($patient->email); ?></td>
                                                <td class="text-center">
                                                    <a href="<?php echo e(route('showCsvPatient', $patient->id)); ?>" class="btn btn-sm btn-soft-primary" data-bs-toggle="tooltip" title="View Files">
                                                        <i class="mdi mdi-folder-open-outline font-size-14 me-1"></i> View Files
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="avatar-xs me-3">
                                                        <span class="avatar-title rounded-circle bg-soft-info text-info font-size-16">
                                                            <?php echo e(strtoupper(substr($patient->name, 0, 1))); ?>

                                                        </span>
                                                    </div>
                                                    <h5 class="font-size-14 mb-0"><?php echo e($patient->name); ?> <?php echo e($patient->surname); ?></h5>
                                                </div>
                                            </td>
                                            <td><?php echo e($patient->email); ?></td>
                                            <td class="text-center">
                                                <a href="<?php echo e(route('showCsvPatient', $patient->id)); ?>" class="btn btn-sm btn-soft-primary" data-bs-toggle="tooltip" title="View Files">
                                                    <i class="mdi mdi-folder-open-outline font-size-14 me-1"></i> View Files
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <?php if(auth()->user()->role->name == "Patient"): ?>
        <div class="modal fade" id="feedbackModal" tabindex="-1" aria-labelledby="feedbackModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="feedbackModalLabel">Medical Feedback</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="feedbackText" class="form-label">
                                <strong>Doctor:</strong> <?php echo e(auth()->user()->patient->doctor->name." ".auth()->user()->patient->doctor->surname); ?>

                            </label>
                            <textarea id="feedbackText" name="feedbackText" class="form-control" rows="5" readonly placeholder="Loading feedback..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="csvDelete" tabindex="-1" aria-labelledby="jobDeleteLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-sm">
                <div class="modal-content">
                    <div class="modal-body px-4 py-5 text-center">
                        <button type="button" class="btn-close position-absolute end-0 top-0 m-3" data-bs-dismiss="modal" aria-label="Close"></button>
                        <div class="avatar-sm mb-4 mx-auto">
                            <div class="avatar-title bg-soft-warning text-warning font-size-24 rounded-circle">
                                <i class="mdi mdi-trash-can-outline"></i>
                            </div>
                        </div>
                        <p class="text-muted font-size-16 mb-4">Are you sure you want to delete this CSV file?</p>
                        <form action="<?php echo e(route('deleteCsv')); ?>" method="POST">
                            <?php echo csrf_field(); ?>
                            <input type="hidden" name="csv_id" id="boxDelete">
                            <div class="hstack gap-2 justify-content-center mb-0">
                                <button type="submit" class="btn btn-danger">Delete</button>
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

<?php $__env->stopSection(); ?>

<?php $__env->startSection('script'); ?>
    <script>
        function deleteCsv(id) {
            document.getElementById('boxDelete').value = id;
        }

        function openFeedbackModal(csvId, doctorId) {
            let feedbackText = document.getElementById('feedbackText');
            feedbackText.value = "Loading feedback...";

            // Retrieve feedback
            fetch(`/get-feedback/${csvId}/${doctorId}`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error("Request error");
                    }
                    return response.json();
                })
                .then(data => {
                    feedbackText.value = data.feedback ?? "No feedback provided by the doctor yet.";
                })
                .catch(error => {
                    console.error("Error fetching feedback:", error);
                    feedbackText.value = "Error loading feedback.";
                });
        }

        $(document).ready(function () {
            $('.yajra-datatable-file').DataTable({
                order: [[0, "desc"]],
                columnDefs: [
                    {orderable: false, targets: -1}
                ],
                pageLength: 5,
                lengthMenu: [5, 10, 20],
                language: {search: "", searchPlaceholder: "Search files..."},
                dom: 'rtip',
                drawCallback: function () {
                    // Aggiunge lo stile ai bottoni.
                    // NOTA: 'pagination-rounded' li rende arrotondati.
                    // Se li vuoi perfettamente quadrati, rimuovi .addClass('pagination-rounded')
                    $('.dataTables_paginate > .pagination').addClass('pagination-rounded');
                }
            });

            $('.yajra-datatable-patient').DataTable({
                order: [[0, "desc"]],
                columnDefs: [
                    {orderable: false, targets: -1}
                ],
                pageLength: 5,
                lengthMenu: [5, 10, 20],
                language: {search: "", searchPlaceholder: "Search files..."},
                dom: 'rtip'
            });

            // Initialize Tooltips
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
            var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl)
            });
        });    </script>

    <script src="https://cdn.datatables.net/1.13.1/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.1/js/dataTables.bootstrap5.min.js"></script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.master', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Users/lorenzotucceri/Progetti/ISEQL/laravel-iseql/resources/views/index.blade.php ENDPATH**/ ?>