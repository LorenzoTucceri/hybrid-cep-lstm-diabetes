<?php $__env->startSection('title'); ?> Dashboard <?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>

    <?php $__env->startComponent('components.breadcrumb'); ?>
        <?php $__env->slot('li_1'); ?> Dashboard <?php $__env->endSlot(); ?>
        <?php $__env->slot('title'); ?> Dashboard <?php $__env->endSlot(); ?>
    <?php echo $__env->renderComponent(); ?>

    <div class="row">
        <div class="col-xl-4">
            <div class="card overflow-hidden">
                <div class="bg-primary bg-soft">
                    <div class="row">
                        <div class="col-7">
                            <div class="text-primary p-3">
                                <h5 class="text-primary">Welcome Back!</h5>
                                <!-- <p>Dashboard Name</p> -->
                            </div>
                        </div>
                        <div class="col-5 align-self-end">
                            <img src="<?php echo e(URL::asset('/assets/images/profile-img.png')); ?>" alt="" class="img-fluid">
                        </div>
                    </div>
                </div>
                <div class="card-body pt-0">
                    <div class="row">
                        <div class="col-sm-4">
                            <div class="avatar-md profile-user-wid mb-4">
                                <img src="/images/avatar-default.jpeg" alt="" class="img-thumbnail rounded-circle">
                            </div>
                            <h5 class="font-size-15"><?php echo e(Str::ucfirst(Auth::user()->name)." ".Str::ucfirst(Auth::user()->surname)); ?></h5>
                            <p class="text-muted mb-0 text-truncate"><?php echo e(Str::ucfirst(Auth::user()->role->name)); ?></p>
                        </div>

                        <div class="col-sm-8">
                            <div class="pt-4">
                                <div class="row">
                                    <div class="col-md-12">
                                        <h5 class="font-size-12"><?php echo e(Str::ucfirst(Auth::user()->email)); ?></h5>
                                        <p class="text-muted mb-0">Email</p>
                                    </div>
                                </div>
                            </div>
                            <div class="mt-4">
                                <a href="<?php echo e(Route('userProfile')); ?>" class="btn btn-primary waves-effect waves-light btn-sm">View Profile <i class="mdi mdi-arrow-right ms-1"></i></a>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title mb-4">What is ISEQL-Glucose Analyzer?</h4>
                    <p>This project focuses on the analysis of glucose data to model various glucose conditions, using a multi-phase process for event detection and pattern recognition. The goal is to improve diabetes management and support personalized treatment strategies.</p>
                    <div class="row">
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-8">
            <div class="row">
                <div class="col-md-4">
                    <div class="card mini-stats-wid">
                        <div class="card-body">
                            <div class="d-flex">
                                <div class="flex-grow-1">
                                    <p class="text-muted fw-medium">Operators</p>
                                    <h4 class="mb-0"><?php echo e(\App\Models\User::count()); ?></h4>
                                </div>

                                <div class="flex-shrink-0 align-self-center">
                                    <div class="mini-stat-icon avatar-sm rounded-circle bg-primary">
                                    <span class="avatar-title rounded-circle bg-primary">
                                         <i class="bx bx-user-circle font-size-24"></i>
                                    </span>
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
                                    <p class="text-muted fw-medium">Patients</p>
                                    <h4 class="mb-0"><?php echo e(\App\Models\Patient::count()); ?></h4>
                                </div>

                                <div class="flex-shrink-0 align-self-center">
                                    <div class="avatar-sm rounded-circle bg-primary mini-stat-icon">
                                    <span class="avatar-title rounded-circle bg-primary">
                                         <i class="bx bxs-user-detail font-size-24"></i>
                                    </span>
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
                                    <p class="text-muted fw-medium">CSV Files</p>
                                    <h4 class="mb-0"><?php echo e(\App\Models\Patient::count()); ?></h4>
                                </div>

                                <div class="flex-shrink-0 align-self-center">
                                    <div class="avatar-sm rounded-circle bg-primary mini-stat-icon">
                                    <span class="avatar-title rounded-circle bg-primary">
                                            <i class="bx bx-copy-alt font-size-24"></i>
                                    </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- end row -->

            <div class="card">
                <div class="card-body">
                    <h4 class="card-title mb-4">Latest Added Patients</h4>
                    <div class="table-responsive">
                        <table id="datatable" class="table table-bordered dt-responsive nowrap w-100">
                            <thead>
                            <tr>
                                <th style="max-width: 40%">Full Name</th>
                                <th style="max-width: 40%">CSV</th>
                                <th>Actions</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php $__currentLoopData = \App\Models\Patient::all()->sortByDesc('id'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $patient): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr>
                                    <td><?php echo e($patient->name); ?> <?php echo e($patient->surname); ?></td>
                                    <td>
                                        <?php if($patient->csv_file_path): ?>
                                            <?php echo e($patient->csv_file_path); ?>

                                        <?php else: ?>
                                            No file uploaded
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <ul class="list-unstyled hstack gap-1 mb-0">
                                            <li data-bs-toggle="tooltip" data-bs-placement="top" title="View">
                                                <a href="<?php echo e(route('detailsPatient', $patient->id)); ?>" target="_blank" class="btn btn-sm btn-soft-primary"><i class="mdi mdi-eye-outline font-size-15"></i></a>
                                            </li>
                                        </ul>
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
    <!-- end row -->
<?php $__env->stopSection(); ?>
<?php $__env->startSection('script'); ?>
    <script>
        function deletePatient(id){
            document.getElementById('boxDelete').value =id;
        }

        $(document).ready(function () {

            var table = $('#datatable').DataTable({
                "length": true,
                "lengthMenu": [4]
            });
            table.buttons().container().appendTo('#datatable-buttons_wrapper .col-md-6:eq(0)');
            $(".dataTables_length select").addClass('form-select form-select-sm');

        });

    </script>
    <!-- apexcharts -->
    <!-- dashboard init -->
    <script src="<?php echo e(URL::asset('assets/js/pages/dashboard.init.js')); ?>"></script>


    <script src="<?php echo e(URL::asset('/assets/libs/apexcharts/apexcharts.min.js')); ?>"></script>

    <!-- project-overview init -->
    <script src="<?php echo e(URL::asset('/assets/js/pages/project-overview.init.js')); ?>"></script>

    <!-- bootstrap datepicker -->
    <script src="<?php echo e(URL::asset('/assets/libs/bootstrap-datepicker/bootstrap-datepicker.min.js')); ?>"></script>
    <!-- dropzone plugin -->
    <script src="<?php echo e(URL::asset('/assets/libs/dropzone/dropzone.min.js')); ?>"></script>

    <!-- Required datatable js -->
    <script src="<?php echo e(URL::asset('/assets/libs/datatables/datatables.min.js')); ?>"></script>
    <script src="<?php echo e(URL::asset('/assets/libs/jszip/jszip.min.js')); ?>"></script>
    <script src="<?php echo e(URL::asset('/assets/libs/pdfmake/pdfmake.min.js')); ?>"></script>
    <!-- Datatable init js -->
    <script src="<?php echo e(URL::asset('/assets/js/pages/datatables.init.js')); ?>"></script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /Users/lorenzotucceri/Library/Mobile Documents/com~apple~CloudDocs/Università/Magistrale/1° anno/Secondo semestre/Intelligent knowledge/Progetto/ISEQL-Glucose Analyzer/Web/resources/views/index.blade.php ENDPATH**/ ?>