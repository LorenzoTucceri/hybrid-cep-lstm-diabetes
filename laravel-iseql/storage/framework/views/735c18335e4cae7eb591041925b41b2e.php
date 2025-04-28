<?php $__env->startSection('title'); ?> Csv Patient <?php $__env->stopSection(); ?>

<?php $__env->startSection('css'); ?>
    <!--datatable css-->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" />
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.2.2/css/buttons.dataTables.min.css">
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>

    <?php $__env->startComponent('components.breadcrumb'); ?>
        <?php $__env->slot('li_1'); ?> Csv files <?php $__env->endSlot(); ?>
        <?php $__env->slot('title'); ?> Csv Patient <?php $__env->endSlot(); ?>
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
                    <h3 class="card-title mb-3 text-xl-center">Patient <?php echo e($patient->name.' '.$patient->surname); ?></h3>
                    <h4 class="card-title mb-3">Add csv file</h4>
                    <form action="<?php echo e(route('uploadCsv')); ?>" method="POST" enctype="multipart/form-data">
                        <?php echo csrf_field(); ?>
                        <input type="hidden" name="patient_id" value="<?php echo e($patient->id); ?>">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <input id="csv" name="csv[]" type="file" class="form-control <?php $__errorArgs = ['csv'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" multiple required>
                                    <?php $__errorArgs = ['csv'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                    <span class="invalid-feedback" role="alert">
                                        <strong><?php echo e($message); ?></strong>
                                    </span>
                                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <button type="submit" class="btn btn-primary waves-effect waves-light w-lg">
                                        Save
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form><br>
                    <h4 class="card-title mb-3">Csv files List</h4>
                    <table class="table table-bordered yajra-datatable">
                        <thead>
                        <tr>
                            <th>File</th>
                            <th>Start time</th>
                            <th>End time</th>
                            <th>Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php $__currentLoopData = $files; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $file): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr>
                            <td><?php echo e($file->csv_file_path); ?></td>
                            <td><?php echo e($file->start_time); ?></td>
                            <td><?php echo e($file->end_time); ?></td>
                            <td>
                                <ul class="list-unstyled hstack gap-1 mb-0">
                                    <li data-bs-toggle="tooltip" data-bs-placement="top" title="View">
                                        <a href="<?php echo e(route('viewCsv', ['csvId' => $file->id, 'patientId' => $patient->id])); ?>" target="_blank" class="btn btn-sm btn-soft-primary">
                                            <i class="mdi mdi-eye-outline font-size-15"></i>
                                        </a>
                                    </li>
                                    <li data-bs-toggle="tooltip" data-bs-placement="top" title="Delete">
                                        <a href="#csvDelete" id="<?php echo e($file->id); ?>" onclick="deleteCsv(this.id)" data-bs-toggle="modal" class="btn btn-sm btn-soft-danger">
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

    <div class="modal fade" id="csvDelete" tabindex="-1" aria-labelledby="jobDeleteLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content">
                <div class="modal-body px-4 py-5 text-center">
                    <button type="button" class="btn-close position-absolute end-0 top-0 m-3" data-bs-dismiss="modal" aria-label="Close"></button>
                    <div class="avatar-sm mb-4 mx-auto">
                        <div class="avatar-title bg-warning text-warning bg-opacity-10 font-size-20 rounded-3">
                            <i class="mdi mdi-trash-can-outline"></i>
                        </div>
                    </div>
                    <p class="text-muted font-size-16 mb-4">Are you sure you want to delete the csv file?</p>
                    <form action="<?php echo e(route('deleteCsv')); ?>" method="POST">
                        <?php echo csrf_field(); ?>
                        <input type="hidden" name="csv_id" id="boxDelete">
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
        function deleteCsv(id){
            document.getElementById('boxDelete').value = id;
        }

        $(function() {
            var table = $('.yajra-datatable').DataTable({
                columns: [
                    {
                        data: 'csv_file_path', // Colonna File
                        name: 'csv_file_path'
                    },
                    {
                        data: 'start_time', // Colonna Start time
                        name: 'start_time'
                    },
                    {
                        data: 'end_time', // Colonna End time
                        name: 'end_time'
                    },
                    {
                        data: 'action', // Colonna Actions (con pulsanti)
                        name: 'action',
                        orderable: false, // Disabilita l'ordinamento su questa colonna
                        searchable: false // Disabilita la ricerca su questa colonna
                    }
                ],
                pageLength: 10, // Numero di righe per pagina
                lengthMenu: [10, 25, 50, 100], // Opzioni per cambiare il numero di righe per pagina
                language: {
                    paginate: {
                        next: 'Next', // Testo pulsante "Successivo"
                        previous: 'Previous' // Testo pulsante "Precedente"
                    },
                    processing: "Loading..." // Testo durante il caricamento
                }
            });
        });

    </script>

    <script src="<?php echo e(URL::asset('/assets/libs/apexcharts/apexcharts.min.js')); ?>"></script>

    <!-- project-overview init -->
    <script src="<?php echo e(URL::asset('/assets/js/pages/project-overview.init.js')); ?>"></script>

    <!-- dropzone plugin -->
    <script src="<?php echo e(URL::asset('/assets/libs/dropzone/dropzone.min.js')); ?>"></script>


    <!-- Datatable init js -->
    <script src="https://cdn.datatables.net/1.13.1/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.1/js/dataTables.bootstrap4.min.js"></script>
    <script src="<?php echo e(URL::asset('/assets/js/pages/datatables.init.js')); ?>"></script>


<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /Users/lorenzotucceri/Library/Mobile Documents/com~apple~CloudDocs/Università/Magistrale/1° anno/Secondo semestre/Intelligent knowledge/Progetto/ISEQL-Glucose_Analyzer/Web/resources/views/csvPatient.blade.php ENDPATH**/ ?>