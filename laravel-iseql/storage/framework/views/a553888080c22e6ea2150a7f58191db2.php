<?php $__env->startSection('title'); ?> Patient Management <?php $__env->stopSection(); ?>

<?php $__env->startSection('css'); ?>
    <!-- bootstrap datepicker -->
    <link href="<?php echo e(URL::asset('/assets/libs/bootstrap-datepicker/bootstrap-datepicker.min.css')); ?>" rel="stylesheet">

    <!-- dropzone css -->
    <link href="<?php echo e(URL::asset('/assets/libs/dropzone/dropzone.min.css')); ?>" rel="stylesheet" type="text/css" />
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>

    <?php $__env->startComponent('components.breadcrumb'); ?>
        <?php $__env->slot('li_1'); ?> Patients <?php $__env->endSlot(); ?>
        <?php $__env->slot('title'); ?> Patient Management <?php $__env->endSlot(); ?>
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
                    <div class="alert alert-danger" role="alert">
                        <?php echo e($message); ?>

                    </div>
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    <?php if(\Session::has('success')): ?>
                        <div class="alert alert-success" role="alert">
                            <?php echo e(Session::get('success')); ?>

                        </div>
                    <?php endif; ?>

                    <h4 class="card-title mb-4">Patient List</h4>
                    <table id="datatable" class="table table-bordered dt-responsive nowrap w-100">
                        <thead>
                        <tr>
                            <th>Full name</th>
                            <th>Email</th>
                            <th>CSV</th>
                            <th>Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php $__currentLoopData = \App\Models\Patient::all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $patient): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr>
                                <td><?php echo e($patient->name); ?> <?php echo e($patient->surname); ?></td>
                                <td><?php echo e($patient->email); ?></td>
                                <td>
                                    <?php if($patient->csv_file_path!=null): ?>
                                        <?php echo e($patient->csv_file_path); ?>

                                    <?php else: ?>
                                        No file uploaded
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <ul class="list-unstyled hstack gap-1 mb-0">
                                        <li data-bs-toggle="tooltip" data-bs-placement="top" title="View">
                                            <a href="<?php echo e(route('detailsPatient', $patient->id)); ?>" target="_blank" class="btn btn-sm btn-soft-primary">
                                                <i class="mdi mdi-eye-outline font-size-15"></i>
                                            </a>
                                        </li>
                                        <li data-bs-toggle="tooltip" data-bs-placement="top" title="Edit">
                                            <a href="<?php echo e(route('searchPatient', $patient->id)); ?>" class="btn btn-sm btn-soft-danger">
                                                <i class="mdi mdi-pencil-outline font-size-15"></i>
                                            </a>
                                        </li>
                                        <li data-bs-toggle="tooltip" data-bs-placement="top" title="Delete">
                                            <a href="#patientDelete" id="<?php echo e($patient->id); ?>" onclick="deletePatient(this.id)" data-bs-toggle="modal" class="btn btn-sm btn-soft-danger">
                                                <i class="mdi mdi-delete-outline font-size-15"></i>
                                            </a>
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

    <div class="modal fade" id="patientDelete" tabindex="-1" aria-labelledby="jobDeleteLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content">
                <div class="modal-body px-4 py-5 text-center">
                    <button type="button" class="btn-close position-absolute end-0 top-0 m-3" data-bs-dismiss="modal" aria-label="Close"></button>
                    <div class="avatar-sm mb-4 mx-auto">
                        <div class="avatar-title bg-warning text-warning bg-opacity-10 font-size-20 rounded-3">
                            <i class="mdi mdi-trash-can-outline"></i>
                        </div>
                    </div>
                    <p class="text-muted font-size-16 mb-4">Are you sure you want to delete the patient?</p>
                    <form action="<?php echo e(route('deletePatient')); ?>" method="post"> <?php echo csrf_field(); ?>
                        <input type="hidden" name="patient" id="boxDelete">
                        <div class="hstack gap-2 justify-content-center mb-0">
                            <button type="submit" class="btn btn-danger">Delete</button>
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- end row -->
<?php $__env->stopSection(); ?>
<?php $__env->startSection('script'); ?>

    <script>
        function deletePatient(id){
            document.getElementById('boxDelete').value = id;
        }
    </script>

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

<?php echo $__env->make('layouts.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /Users/lorenzotucceri/Library/Mobile Documents/com~apple~CloudDocs/Università/Magistrale/1° anno/Secondo semestre/Intelligent knowledge/Progetto/ISEQL-Glucose Analyzer/Web/resources/views/patientManagement.blade.php ENDPATH**/ ?>