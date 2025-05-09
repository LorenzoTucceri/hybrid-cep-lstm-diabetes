<?php $__env->startSection('title'); ?>
    Patient Management
<?php $__env->stopSection(); ?>

<?php $__env->startSection('css'); ?>
    <!--datatable css-->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" />
    <!-- DataTables Buttons CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.2.2/css/buttons.dataTables.min.css" />

<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>

    <?php $__env->startComponent('components.breadcrumb'); ?>
        <?php $__env->slot('li_1'); ?>
            Patients
        <?php $__env->endSlot(); ?>
        <?php $__env->slot('title'); ?>
            Patient Management
        <?php $__env->endSlot(); ?>
    <?php echo $__env->renderComponent(); ?>

    <style>
        .modal-content {
            border-radius: 8px; /* Aggiungi angoli arrotondati */
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); /* Aggiungi un'ombra leggera */
        }

        .modal-header {
            background-color: #f8f9fa;
            border-bottom: 1px solid #e0e0e0;
        }

        .modal-footer {
            display: flex;
            justify-content: flex-end;
        }

        .form-control {
            border-radius: 4px; /* Bordo più arrotondato per gli input */
        }
    </style>

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

                    <h4 class="card-title mb-4 d-flex justify-content-between">
                        Patient List
                        <button type="button" class="btn btn-primary waves-effect waves-light" data-bs-toggle="modal"
                                data-bs-target=".bs-add-patient-modal-xl">
                            Add Patient
                        </button>
                    </h4>


                    <table class="table table-bordered yajra-datatable">
                        <thead>
                        <tr>
                            <th>Full Name</th>
                            <th>Email</th>
                            <th>Address</th>
                            <th>Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php $__currentLoopData = \App\Models\Patient::all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $patient): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php if(auth()->user()->role->name=="Doctor"): ?>
                                <?php if($patient->doctor_id==auth()->user()->id): ?>
                                    <tr>
                                        <td><?php echo e($patient->name); ?> <?php echo e($patient->surname); ?></td> <!-- Nome completo -->
                                        <td><?php echo e($patient->email); ?></td> <!-- Email -->
                                        <td><?php echo e($patient->address); ?></td> <!-- Indirizzo -->
                                        <td>
                                            <ul class="list-unstyled hstack gap-1 mb-0">
                                                <li data-bs-toggle="tooltip" data-bs-placement="top" title="View">
                                                    <a href="<?php echo e(route('showCsvPatient', $patient->id)); ?>"
                                                       target="_blank" class="btn btn-sm btn-soft-primary">
                                                        <i class="mdi mdi-eye-outline font-size-15"></i>
                                                    </a>
                                                </li>
                                                <li data-bs-toggle="tooltip" data-bs-placement="top"
                                                    title="<?php echo e(\App\Models\User::where('patient_id', $patient->id)->exists() ? 'Registration completed' : 'Invite'); ?>">

                                                    <?php if(\App\Models\User::where('patient_id', $patient->id)->exists()): ?>
                                                        <!-- If the patient already has a user account -->
                                                        <button class="btn btn-sm btn-soft-success">
                                                            <i class="mdi mdi-check-circle font-size-15"></i>
                                                        </button>
                                                    <?php else: ?>
                                                        <!-- If the patient does not have a user account, show the button to send an invite -->
                                                        <a href="<?php echo e(route('sendRegistration', ['patientId' => $patient->id])); ?>"
                                                           class="btn btn-sm btn-soft-info">
                                                            <i class="mdi mdi-email-send font-size-15"></i>
                                                        </a>
                                                    <?php endif; ?>
                                                </li>
                                                <li data-bs-toggle="tooltip" data-bs-placement="top" title="Edit">
                                                    <a data-bs-toggle="modal" class="btn btn-sm btn-soft-warning"
                                                       data-bs-target="#editPatientModal" data-id="<?php echo e($patient->id); ?>"
                                                       data-name="<?php echo e($patient->name); ?>"
                                                       data-surname="<?php echo e($patient->surname); ?>"
                                                       data-email="<?php echo e($patient->email); ?>"
                                                       data-telephone="<?php echo e($patient->telephone_number); ?>"
                                                       data-address="<?php echo e($patient->address); ?>"
                                                       data-birth="<?php echo e($patient->birth); ?>"
                                                       data-gender="<?php echo e($patient->gender); ?>"
                                                       data-doctor="<?php echo e($patient->doctor_id); ?>">
                                                        <i class="mdi mdi-pencil-outline font-size-15"></i>
                                                    </a>
                                                </li>
                                                <li data-bs-toggle="tooltip" data-bs-placement="top" title="Delete">
                                                    <a href="#patientDelete" id="<?php echo e($patient->id); ?>"
                                                       onclick="deletePatient(this.id)" data-bs-toggle="modal"
                                                       class="btn btn-sm btn-soft-danger">
                                                        <i class="mdi mdi-delete-outline font-size-15"></i>
                                                    </a>
                                                </li>
                                            </ul>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            <?php else: ?>
                                <tr>
                                    <td><?php echo e($patient->name); ?> <?php echo e($patient->surname); ?></td> <!-- Nome completo -->
                                    <td><?php echo e($patient->email); ?></td> <!-- Email -->
                                    <td><?php echo e($patient->address); ?></td> <!-- Indirizzo -->
                                    <td>
                                        <ul class="list-unstyled hstack gap-1 mb-0">
                                            <li data-bs-toggle="tooltip" data-bs-placement="top" title="View">
                                                <a href="<?php echo e(route('showCsvPatient', $patient->id)); ?>"
                                                   target="_blank" class="btn btn-sm btn-soft-primary">
                                                    <i class="mdi mdi-eye-outline font-size-15"></i>
                                                </a>
                                            </li>
                                            <li data-bs-toggle="tooltip" data-bs-placement="top"
                                                title="<?php echo e(\App\Models\User::where('patient_id', $patient->id)->exists() ? 'Registration completed' : 'Invite'); ?>">

                                                <?php if(\App\Models\User::where('patient_id', $patient->id)->exists()): ?>
                                                    <!-- If the patient already has a user account -->
                                                    <button class="btn btn-sm btn-soft-success">
                                                        <i class="mdi mdi-check-circle font-size-15"></i>
                                                    </button>
                                                <?php else: ?>
                                                    <!-- If the patient does not have a user account, show the button to send an invite -->
                                                    <a href="<?php echo e(route('sendRegistration', ['patientId' => $patient->id])); ?>"
                                                       class="btn btn-sm btn-soft-info">
                                                        <i class="mdi mdi-email-send font-size-15"></i>
                                                    </a>
                                                <?php endif; ?>
                                            </li>                                            <li data-bs-toggle="tooltip" data-bs-placement="top" title="Edit">
                                                <a data-bs-toggle="modal" class="btn btn-sm btn-soft-warning"
                                                   data-bs-target="#editPatientModal" data-id="<?php echo e($patient->id); ?>"
                                                   data-name="<?php echo e($patient->name); ?>"
                                                   data-surname="<?php echo e($patient->surname); ?>"
                                                   data-email="<?php echo e($patient->email); ?>"
                                                   data-telephone="<?php echo e($patient->telephone_number); ?>"
                                                   data-address="<?php echo e($patient->address); ?>"
                                                   data-birth="<?php echo e($patient->birth); ?>"
                                                   data-gender="<?php echo e($patient->gender); ?>"
                                                   data-doctor="<?php echo e($patient->doctor_id); ?>">
                                                    <i class="mdi mdi-pencil-outline font-size-15"></i>
                                                </a>
                                            </li>
                                            <li data-bs-toggle="tooltip" data-bs-placement="top" title="Delete">
                                                <a href="#patientDelete" id="<?php echo e($patient->id); ?>"
                                                   onclick="deletePatient(this.id)" data-bs-toggle="modal"
                                                   class="btn btn-sm btn-soft-danger">
                                                    <i class="mdi mdi-delete-outline font-size-15"></i>
                                                </a>
                                            </li>
                                        </ul>
                                    </td>
                                </tr>

                            <?php endif; ?>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal -->
    <div class="modal fade bs-add-patient-modal-xl" tabindex="-1" role="dialog"
         aria-labelledby="addPatientModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg"> <!-- Cambiato modal-md a modal-lg per maggiore larghezza -->
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addClientModalLabel">Add Patient</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                </div>
                <div class="modal-body">
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

                    <form method="post" action="<?php echo e(route('addPatient')); ?>" enctype="multipart/form-data">
                        <?php echo csrf_field(); ?>

                        <!-- Nome -->
                        <div class="row mb-4">
                            <label for="name" class="col-form-label col-lg-3">*Name<span
                                    class="text-danger">*</span></label>
                            <div class="col-lg-9">
                                <input type="text"
                                       class="form-control <?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                       id="name" name="name" placeholder="Enter name"
                                       value="<?php echo e(old('name')); ?>" required>
                                <?php $__errorArgs = ['name'];
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

                        <!-- Cognome -->
                        <div class="row mb-4">
                            <label for="surname" class="col-form-label col-lg-3">*Surname<span
                                    class="text-danger">*</span></label>
                            <div class="col-lg-9">
                                <input type="text"
                                       class="form-control <?php $__errorArgs = ['surname'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                       id="surname" name="surname" placeholder="Enter surname"
                                       value="<?php echo e(old('surname')); ?>" required>
                                <?php $__errorArgs = ['surname'];
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

                        <!-- Email -->
                        <div class="row mb-4">
                            <label for="email" class="col-form-label col-lg-3">Email<span
                                    class="text-danger">*</span></label>
                            <div class="col-lg-9">
                                <input type="email"
                                       class="form-control <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                       id="email" name="email" placeholder="Enter l'email"
                                       value="<?php echo e(old('email')); ?>" required>
                                <?php $__errorArgs = ['email'];
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

                        <!-- Doctor -->
                        <div class="row mb-4">
                            <label for="doctor" class="col-form-label col-lg-3">Doctor<span
                                    class="text-danger">*</span></label>
                            <div class="col-lg-9">
                                <select class="form-select <?php $__errorArgs = ['doctor'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                        name="doctor" id="doctor">
                                    <?php
                                        $doctors = \App\Models\User::where("role_id", "3")->get();
                                    ?>

                                    <?php if($doctors->isEmpty()): ?>
                                        <option value="" disabled selected> No doctors available
                                        </option>
                                    <?php else: ?>
                                        <option value="">Select a doctor</option>
                                        <?php $__currentLoopData = $doctors; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $doctor): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <option value="<?php echo e($doctor->id); ?>"
                                                    <?php if(old('doctor') == $doctor->id): ?> selected <?php endif; ?>>
                                                <?php echo e($doctor->name); ?>

                                            </option>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    <?php endif; ?>
                                </select>
                                <?php $__errorArgs = ['doctor'];
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

                        <!-- Date of Birth -->
                        <div class="row mb-4">
                            <label for="birth" class="col-form-label col-lg-3">Date of birth<span
                                    class="text-danger">*</span></label>
                            <div class="col-lg-9">
                                <input type="date"
                                       class="form-control <?php $__errorArgs = ['birth'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                       id="birth" name="birth"
                                       value="<?php echo e(old('birth')); ?>" required>
                                <?php $__errorArgs = ['birth'];
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

                        <!-- Gender -->
                        <div class="row mb-4">
                            <label for="gender" class="col-form-label col-lg-3">Gender<span
                                    class="text-danger">*</span></label>
                            <div class="col-lg-9">
                                <select class="form-select <?php $__errorArgs = ['gender'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                        name="gender" id="gender" required>
                                    <option value="Male"
                                            <?php if(old('gender') == 'Male'): ?> selected <?php endif; ?>>Male
                                    </option>
                                    <option value="Female"
                                            <?php if(old('gender') == 'Female'): ?> selected <?php endif; ?>>Female
                                    </option>
                                </select>
                                <?php $__errorArgs = ['gender'];
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

                        <!-- Address -->
                        <div class="row mb-4">
                            <label for="address" class="col-form-label col-lg-3">Address<span
                                    class="text-danger">*</span></label>
                            <div class="col-lg-9">
                                <input type="text"
                                       class="form-control <?php $__errorArgs = ['address'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                       id="address" name="address"
                                       placeholder="Enter address"
                                       value="<?php echo e(old('address')); ?>" required>
                                <?php $__errorArgs = ['address'];
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

                        <!-- Phone Number -->
                        <div class="row mb-4">
                            <label for="telephone_number" class="col-form-label col-lg-3">Phone number</label>
                            <div class="col-lg-9">
                                <input type="text"
                                       class="form-control <?php $__errorArgs = ['telephone_number'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                       id="telephone_number" name="telephone_number"
                                       placeholder="Enter phone number"
                                       value="<?php echo e(old('telephone_number')); ?>">
                                <?php $__errorArgs = ['telephone_number'];
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

                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                Chiudi
                            </button>
                            <button type="submit" id="submitButton"
                                    class="btn btn-primary waves-effect waves-light">Aggiungi Cliente
                            </button>
                        </div>
                    </form>
                </div>
            </div><!-- /.modal-content -->
        </div><!-- /.modal-dialog -->
    </div>

    <!-- Modal per Editare Paziente -->
    <div class="modal fade bs-add-patient-modal-xl" id="editPatientModal" tabindex="-1"
         aria-labelledby="editPatientModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg"> <!-- Cambiato modal-md a modal-lg per maggiore larghezza -->
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editPatientModalLabel">Modifica Paziente</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="editPatientForm" method="POST" action="<?php echo e(route('updatePatient')); ?>">
                        <?php echo csrf_field(); ?>

                        <input type="hidden" id="patient_id" name="id">

                        <!-- Nome -->
                        <div class="row mb-4">
                            <label for="edit-name" class="col-form-label col-lg-3">*Name<span
                                    class="text-danger">*</span></label>
                            <div class="col-lg-9">
                                <input type="text" class="form-control <?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                       id="edit-name" name="name" placeholder="Enter name" required>
                                <?php $__errorArgs = ['name'];
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

                        <!-- Cognome -->
                        <div class="row mb-4">
                            <label for="edit-surname" class="col-form-label col-lg-3">*Surname<span class="text-danger">*</span></label>
                            <div class="col-lg-9">
                                <input type="text" class="form-control <?php $__errorArgs = ['surname'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                       id="edit-surname" name="surname" placeholder="Enter surname" required>
                                <?php $__errorArgs = ['surname'];
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

                        <!-- Email -->
                        <div class="row mb-4">
                            <label for="edit-email" class="col-form-label col-lg-3">Email<span
                                    class="text-danger">*</span></label>
                            <div class="col-lg-9">
                                <input type="email" class="form-control <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                       id="edit-email" name="email" placeholder="Enter email" required>
                                <?php $__errorArgs = ['email'];
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

                        <div class="row mb-4">
                            <label for="edit-doctor" class="col-form-label col-lg-3">Doctor<span
                                    class="text-danger">*</span></label>
                            <div class="col-lg-9">
                                <select class="form-select <?php $__errorArgs = ['doctor'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" name="doctor"
                                        id="edit-doctor" required>
                                    <?php $__currentLoopData = $doctors; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $doctor): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($doctor->id); ?>"><?php echo e($doctor->name); ?></option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                                <?php $__errorArgs = ['doctor'];
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


                        <!-- Data di Nascita -->
                        <div class="row mb-4">
                            <label for="edit-birth" class="col-form-label col-lg-3">Date of birth<span
                                    class="text-danger">*</span></label>
                            <div class="col-lg-9">
                                <input type="date" class="form-control <?php $__errorArgs = ['birth'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                       id="edit-birth" name="birth" required>
                                <?php $__errorArgs = ['birth'];
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

                        <!-- Sesso -->
                        <div class="row mb-4">
                            <label for="edit-gender" class="col-form-label col-lg-3">Gender<span
                                    class="text-danger">*</span></label>
                            <div class="col-lg-9">
                                <select class="form-select <?php $__errorArgs = ['gender'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" name="gender"
                                        id="edit-gender" required>
                                    <option value="Male">Male</option>
                                    <option value="Female">Female</option>
                                </select>
                                <?php $__errorArgs = ['gender'];
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

                        <!-- Indirizzo -->
                        <div class="row mb-4">
                            <label for="edit-address" class="col-form-label col-lg-3">Address<span
                                    class="text-danger">*</span></label>
                            <div class="col-lg-9">
                                <input type="text" class="form-control <?php $__errorArgs = ['address'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                       id="edit-address" name="address" placeholder="Enter address" required>
                                <?php $__errorArgs = ['address'];
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

                        <!-- Numero di telefono -->
                        <div class="row mb-4">
                            <label for="edit-telephone_number" class="col-form-label col-lg-3">Phone number</label>
                            <div class="col-lg-9">
                                <input type="text" class="form-control <?php $__errorArgs = ['telephone_number'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                       id="edit-telephone_number" name="telephone_number"
                                       placeholder="Enter phone number">
                                <?php $__errorArgs = ['telephone_number'];
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

                        <!-- Medico -->

                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-primary">Update Patient</button>
                        </div>
                    </form>
                </div><!-- /.modal-content -->
            </div><!-- /.modal-dialog -->
        </div><!-- /.modal -->
    </div>

    <div class="modal fade" id="patientDelete" tabindex="-1" aria-labelledby="jobDeleteLabel" aria-hidden="true">
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
                    <p class="text-muted font-size-16 mb-4">Are you sure you want to delete the patient?</p>
                    <form action="<?php echo e(route('deletePatient')); ?>" method="post"> <?php echo csrf_field(); ?>
                        <input type="hidden" name="patient" id="boxDelete">
                        <button type="submit" class="btn btn-danger">Delete</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

<?php $__env->stopSection(); ?>

<?php $__env->startSection('script'); ?>

    <script>

        function deletePatient(id) {
            document.getElementById('boxDelete').value = id;
        }

        $('#editPatientModal').on('show.bs.modal', function (event) {
            var button = $(event.relatedTarget); // Bottone che ha attivato il modale
            var patientId = button.data('id'); // ID del paziente
            var name = button.data('name');
            var surname = button.data('surname');
            var email = button.data('email');
            var birth = button.data('birth');
            var gender = button.data('gender');
            var address = button.data('address');
            var telephone = button.data('telephone');
            var doctor = button.data('doctor');


            // Popoliamo i campi del modale
            $('#patient_id').val(patientId);
            $('#edit-name').val(name);
            $('#edit-surname').val(surname);
            $('#edit-email').val(email);
            $('#edit-birth').val(birth);
            $('#edit-gender').val(gender);
            $('#edit-address').val(address);
            $('#edit-telephone_number').val(telephone);
            $('#edit-doctor').val(doctor);
        });



        $(document).ready(function () {
            $('[data-bs-toggle="tooltip"]').tooltip();
            $('.yajra-datatable').DataTable({
                order: [[0, "desc"]], // Ordina per la prima colonna (data)
                columnDefs: [
                    { orderable: false, targets: -1 } // Disabilita ordinamento sull'ultima colonna
                ]
            });
        });

    </script>

    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.13.1/js/jquery.dataTables.min.js"></script>

    <!-- DataTables Bootstrap 5 integration JS -->
    <script src="https://cdn.datatables.net/1.13.1/js/dataTables.bootstrap5.min.js"></script>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.master', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Applications/MAMP/htdocs/ISEQL/laravel-iseql/resources/views/patientManagement.blade.php ENDPATH**/ ?>