<?php $__env->startSection('title'); ?>
    Csv Patient
<?php $__env->stopSection(); ?>

<?php $__env->startSection('css'); ?>
    <!--datatable css-->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css"/>
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.2.2/css/buttons.dataTables.min.css">
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>

    <?php $__env->startComponent('components.breadcrumb'); ?>
        <?php $__env->slot('li_1'); ?>
            Csv files
        <?php $__env->endSlot(); ?>
        <?php $__env->slot('title'); ?>
            Csv Patient
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
                        <input type="hidden" name="role" value="<?php echo e(auth()->user()->role->name); ?>">
                        <?php if(auth()->user()->role->name=="Patient"): ?>
                            <input type="hidden" name="doctor" value="<?php echo e(auth()->user()->patient->doctor_id); ?>">
                        <?php endif; ?>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <input id="csv" name="csv[]" type="file"
                                           class="form-control <?php $__errorArgs = ['csv'];
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
                    </form>
                    <br>
                    <h4 class="card-title mb-3">Csv files List</h4>
                    <table class="table table-bordered yajra-datatable">
                        <thead>
                        <tr>
                            <th>File</th>
                            <th>Start time</th>
                            <th>End time</th>
                            <th>GMI</th>
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
                                    <?php echo e($file->gmi); ?>%
                                    <?php if($file->gmi < 7): ?>
                                        <span style="display:inline-block; width:10px; height:10px; background-color:green; border-radius:50%; margin-left:5px;"></span>
                                    <?php elseif($file->gmi >= 7 && $file->gmi <= 8): ?>
                                        <span style="display:inline-block; width:10px; height:10px; background-color:orange; border-radius:50%; margin-left:5px;"></span>
                                    <?php else: ?>
                                        <span style="display:inline-block; width:10px; height:10px; background-color:red; border-radius:50%; margin-left:5px;"></span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <ul class="list-unstyled hstack gap-1 mb-0">
                                        <li data-bs-toggle="tooltip" data-bs-placement="top" title="View">
                                            <a href="<?php echo e(route('viewCsv', ['csvId' => $file->id, 'patientId' => $patient->id])); ?>"
                                               target="_blank" class="btn btn-sm btn-soft-primary">
                                                <i class="mdi mdi-eye-outline font-size-15"></i>
                                            </a>
                                        </li>
                                        <li data-bs-toggle="tooltip" data-bs-placement="top" title="Feedback">
                                            <?php if(auth()->user()->role->name  == 'Doctor'): ?>
                                                <a href="#feedbackModal"
                                                   onclick="openFeedbackModal(<?php echo e($file->id); ?>, true, <?php echo e(auth()->user()->id); ?>)"
                                                   data-bs-toggle="modal" class="btn btn-sm btn-soft-info">
                                                    <i class="mdi mdi-comment-edit-outline font-size-15"></i>
                                                </a>
                                            <?php elseif(auth()->user()->role->name == 'Patient'): ?>
                                                <a href="#feedbackModal"
                                                   onclick="openFeedbackModal(<?php echo e($file->id); ?>, false,  <?php echo e(auth()->user()->patient->doctor_id); ?>)"
                                                   data-bs-toggle="modal" class="btn btn-sm btn-soft-info">
                                                    <i class="mdi mdi-comment-eye-outline font-size-15"></i>
                                                </a>
                                            <?php endif; ?>
                                        </li>
                                        <li data-bs-toggle="tooltip" data-bs-placement="top" title="Delete">
                                            <a href="#csvDelete" id="<?php echo e($file->id); ?>" onclick="deleteCsv(this.id)"
                                               data-bs-toggle="modal" class="btn btn-sm btn-soft-danger">
                                                <i class="mdi mdi-delete-outline font-size-15"></i>
                                            </a>
                                        </li>

                                    </ul>
                                </td>
                            </tr>

                            <!-- Modal per il feedback -->
                            <div class="modal fade" id="feedbackModal" tabindex="-1"
                                 aria-labelledby="feedbackModalLabel" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="feedbackModalLabel">Feedback</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                    aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <form id="feedbackForm" method="POST" action="<?php echo e(route('saveFeedback')); ?>">
                                                <?php echo csrf_field(); ?>
                                                <input type="hidden" id="csvFileId" name="file_id">
                                                <input type="hidden" id="doctorId" name="doctor_id">

                                                <div class="mb-3">
                                                    <label for="feedbackText"
                                                           class="form-label"><strong>Doctor: </strong>
                                                        <?php if(auth()->user()->role->name=="Patient"): ?>
                                                            <?php echo e(auth()->user()->patient->doctor->name." ".auth()->user()->patient->doctor->surname); ?>

                                                        <?php else: ?>
                                                            <?php echo e(auth()->user()->name." ".auth()->user()->surname); ?>

                                                        <?php endif; ?>
                                                    </label>
                                                    <textarea id="feedbackTextDoctor" name="feedbackDoctor"
                                                              class="form-control" rows="3"
                                                              <?php if(auth()->user()->role->name=="Patient"): ?> placeholder="No feedback available..."
                                                              readonly
                                                              <?php else: ?> placeholder="Leave a feedback..."
                                                              required <?php endif; ?>></textarea>
                                                </div>

                                                <div class="mb-3" id="labelPatient">
                                                    <label for="feedbackTextPatient"
                                                           class="form-label"><strong>Patient: </strong><?php echo e($patient->name." ".$patient->surname); ?>

                                                    </label>
                                                    <textarea id="feedbackTextPatient" name="feedbackPatient"
                                                              class="form-control" rows="3"
                                                              <?php if(auth()->user()->role->name=="Doctor"): ?> placeholder="No feedback available..."
                                                              readonly
                                                              <?php else: ?> placeholder="Leave a feedback..."
                                                              required <?php endif; ?>></textarea>
                                                </div>

                                                <div class="d-flex justify-content-center">
                                                    <button type="submit" id="saveFeedbackButton"
                                                            class="btn btn-primary">
                                                        Send
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
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
                    <button type="button" class="btn-close position-absolute end-0 top-0 m-3" data-bs-dismiss="modal"
                            aria-label="Close"></button>
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
        function deleteCsv(id) {
            document.getElementById('boxDelete').value = id;
        }

        function openFeedbackModal(csvId, isDoctor, doctorId) {
            document.getElementById('csvFileId').value = csvId;
            document.getElementById('doctorId').value = doctorId;

            let feedbackTextPatient = document.getElementById('feedbackTextPatient');
            let feedbackTextDoctor = document.getElementById('feedbackTextDoctor');
            let labelPatient = document.getElementById('labelPatient');
            let saveButton = document.getElementById('saveFeedbackButton');

            // Imposta valori iniziali
            [feedbackTextPatient, feedbackTextDoctor].forEach(textarea => {
                textarea.value = "Loading feedback...";
                textarea.readOnly = true;
            });

            // Recupera il feedback
            fetch(`/get-feedback/${csvId}/${doctorId}`)
                .then(response => response.ok ? response.json() : Promise.reject("Request error"))
                .then(data => {
                    // Assegna i valori ricevuti
                    feedbackTextDoctor.value = data.message_doctor || "";
                    feedbackTextPatient.value = data.message_patient || "";

                    if (isDoctor) {
                        feedbackTextDoctor.readOnly = feedbackTextPatient.value.trim() !== "";
                        saveButton.style.display = feedbackTextPatient.value.trim() ? "none" : "block";
                    } else {
                        feedbackTextPatient.readOnly = false;
                        feedbackTextDoctor.readOnly = true;
                        saveButton.style.display = feedbackTextDoctor.value.trim() ? "block" : "none";
                    }

                    labelPatient.style.display = feedbackTextDoctor.value.trim() ? "block" : "none";
                })
                .catch(error => {
                    console.error("Error fetching feedback:", error);
                    feedbackTextPatient.value = "Error loading feedback.";
                    feedbackTextDoctor.value = "Error loading feedback.";
                });
        }


        $(document).ready(function () {
            $('.yajra-datatable').DataTable({
                order: [[3, "desc"]], // Ordina per la prima colonna (data)
                columnDefs: [
                    {orderable: false, targets: -1} // Disabilita ordinamento sull'ultima colonna
                ]
            });
            $('[data-bs-toggle="tooltip"]').tooltip();
        });


    </script>


    <script src="https://cdn.datatables.net/1.13.1/js/jquery.dataTables.min.js"></script>

    <script src="https://cdn.datatables.net/1.13.1/js/dataTables.bootstrap5.min.js"></script>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.master', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Users/lorenzotucceri/Progetti/ISEQL/laravel-iseql/resources/views/csvPatient.blade.php ENDPATH**/ ?>