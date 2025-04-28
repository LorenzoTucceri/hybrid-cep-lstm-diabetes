<?php $__env->startSection('title'); ?> Operator Management <?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>

    <?php $__env->startComponent('components.breadcrumb'); ?>
        <?php $__env->slot('li_1'); ?> Operators <?php $__env->endSlot(); ?>
        <?php $__env->slot('title'); ?> Operator Management <?php $__env->endSlot(); ?>
    <?php echo $__env->renderComponent(); ?>
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title mb-4"><?php if(\Session::has('updateUser')): ?> Update Operator <?php else: ?> New Operator <?php endif; ?></h4>
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
                    <form method="post" <?php if(\Session::has('updateUser')): ?> action="<?php echo e(route('updateUser')); ?>" <?php else: ?> action="<?php echo e(route('newProfile')); ?>" <?php endif; ?>> <?php echo csrf_field(); ?>
                        <?php if(\Session::has('updateUser')): ?>
                            <input type="hidden" name="id" value="<?php echo e(Session::get('updateUser')->id); ?>">
                        <?php endif; ?>

                        <div class="row mb-4">
                            <label for="email" class="col-form-label col-lg-2">Email</label>
                            <div class="col-lg-10">
                                <input id="email" name="email" type="email" class="form-control <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                       placeholder="Enter email" required <?php if(\Session::has('updateUser')): ?> value="<?php echo e(Session::get("updateUser")->email); ?>" <?php endif; ?>>
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
                            <label for="name" class="col-form-label col-lg-2">First Name</label>
                            <div class="col-lg-10">
                                <input type="text" class="form-control <?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                       id="name" name="name" autofocus required
                                       placeholder="Enter first name" <?php if(\Session::has('updateUser')): ?> value="<?php echo e(Session::get("updateUser")->name); ?>" <?php endif; ?>>
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
                        <div class="row mb-4">
                            <label for="surname" class="col-form-label col-lg-2">Last Name</label>
                            <div class="col-lg-10">
                                <input type="text" class="form-control <?php $__errorArgs = ['surname'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                       id="surname" name="surname" autofocus required
                                       placeholder="Enter last name" <?php if(\Session::has('updateUser')): ?> value="<?php echo e(Session::get("updateUser")->surname); ?>" <?php endif; ?>>
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
                        <div class="row mb-4">
                            <label for="role" class="col-form-label col-lg-2">Role</label>
                            <div class="col-lg-10">
                                <select id="role" name="role" class="form-control <?php $__errorArgs = ['role'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" required>
                                    <option value="">Select role</option>
                                    <option value="Operator" <?php if(\Session::has('updateUser') && Session::get('updateUser')->role->name == 'Operator'): ?> selected <?php endif; ?>>Operator</option>
                                    <option value="Admin" <?php if(\Session::has('updateUser') && Session::get('updateUser')->role->name == 'Admin'): ?> selected <?php endif; ?>>Admin</option>
                                </select>
                                <?php $__errorArgs = ['role'];
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
                            <label for="newPassword" class="col-form-label col-lg-2">New Password</label>
                            <div class="col-lg-10">
                                <div class="input-group auth-pass-inputgroup <?php $__errorArgs = ['newPassword'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                                    <input type="password"
                                           class="form-control <?php $__errorArgs = ['newPassword'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                           id="newPassword" placeholder="Enter new password"
                                           aria-label="Password" name="newPassword" autofocus aria-describedby="password-addon" <?php if(!(\Session::has('updateUser'))): ?> required <?php endif; ?>>
                                    <button class="btn btn-light" type="button" id="password-addon"><i
                                            class="mdi mdi-eye-outline"></i></button></div>

                                <?php $__errorArgs = ['newPassword'];
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
                            <label for="confirmPassword" class="col-form-label col-lg-2">Confirm Password</label>
                            <div class="col-lg-10">
                                <div class="input-group auth-pass-inputgroup <?php $__errorArgs = ['confirmPassword'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                                    <input type="password"
                                           class="form-control <?php $__errorArgs = ['confirmPassword'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                           id="confirmPassword" placeholder="Confirm password"
                                           aria-label="Password1" name="confirmPassword" autofocus aria-describedby="password-addon1" <?php if(!(\Session::has('updateUser'))): ?> required <?php endif; ?>>
                                    <button class="btn btn-light" type="button" onclick="changeTypeBox()"><i
                                            class="mdi mdi-eye-outline" id="changeEye"></i></button></div>
                                <?php $__errorArgs = ['confirmPassword'];
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

                        <div class="row justify-content-end">
                            <div class="col-lg-10">
                                <button type="submit" class="btn btn-primary"> <?php if(\Session::has('updateUser')): ?> Update <?php else: ?> Add <?php endif; ?> </button>
                            </div>
                        </div>
                    </form><br>
                    <h4 class="card-title mb-4">Manage Operators</h4>
                    <table id="datatable" class="table table-bordered dt-responsive nowrap w-100">
                        <thead>
                        <tr>
                            <th>Email</th>
                            <th>First Name</th>
                            <th>Last Name</th>
                            <th>Role</th>
                            <th>Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php $__currentLoopData = \App\Models\User::all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php if(Auth::user()->id!=$user->id || $user->id !=1): ?>
                                <tr>
                                    <td><?php echo e($user->email); ?></td>
                                    <td><?php echo e($user->name); ?></td>
                                    <td><?php echo e($user->surname); ?></td>
                                    <td><?php echo e($user->role->name); ?></td>


                                    <td><ul class="list-unstyled hstack gap-1 mb-0">
                                            <li data-bs-toggle="tooltip" data-bs-placement="top" title="Edit">
                                                <a href="<?php echo e(route('searchUser', $user->id)); ?>"  class="btn btn-sm btn-soft-danger"><i class="mdi mdi-pencil-outline  font-size-15"></i></a>
                                            </li>
                                            <li data-bs-toggle="tooltip" data-bs-placement="top" title="Delete">
                                                <a href="#userDelete" id="<?php echo e($user->id); ?>" onclick="deleteUser(this.id)" data-bs-toggle="modal" class="btn btn-sm btn-soft-danger"><i class="mdi mdi-delete-outline font-size-15"></i></a>
                                            </li>

                                        </ul></td>
                                </tr>

                            <?php endif; ?>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="userDelete" tabindex="-1" aria-labelledby="jobDeleteLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content">
                <div class="modal-body px-4 py-5 text-center">
                    <button type="button" class="btn-close position-absolute end-0 top-0 m-3" data-bs-dismiss="modal" aria-label="Close"></button>
                    <div class="avatar-sm mb-4 mx-auto">
                        <div class="avatar-title bg-warning text-warning bg-opacity-10 font-size-20 rounded-3">
                            <i class="mdi mdi-trash-can-outline"></i>
                        </div>
                    </div>
                    <p class="text-muted font-size-16 mb-4">Are you sure you want to delete this operator?</p>
                    <form action="<?php echo e(route('deleteUser')); ?>" method="post"> <?php echo csrf_field(); ?>
                        <input type="hidden" name="user" id="boxDelete">
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
        function deleteUser(id){
            document.getElementById('boxDelete').value =id;
        }

        function changeTypeBox(){
            if(document.getElementById("confirmPassword").type=="password"){
                document.getElementById("confirmPassword").type = "text"
                document.getElementById("changeEye").className="mdi mdi-eye-off-outline"
            }
            else{
                document.getElementById("confirmPassword").type = "password"
                document.getElementById("changeEye").className="mdi mdi-eye-outline"

            }
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

<?php echo $__env->make('layouts.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /Users/lorenzotucceri/Library/Mobile Documents/com~apple~CloudDocs/Università/Magistrale/1° anno/Secondo semestre/Intelligent knowledge/Progetto/ISEQL-Glucose Analyzer/Web/resources/views/userManagement.blade.php ENDPATH**/ ?>