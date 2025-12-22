<?php $__env->startSection('title'); ?> Operator Management <?php $__env->stopSection(); ?>

<?php $__env->startSection('css'); ?>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css"/>
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.2.2/css/buttons.dataTables.min.css">
    <style>
        .badge-soft-primary { color: #556ee6; background-color: rgba(85,110,230,.18); }
        .badge-soft-success { color: #34c38f; background-color: rgba(52,195,143,.18); }
        .cursor-pointer { cursor: pointer; }
    </style>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>

    <?php $__env->startComponent('components.breadcrumb'); ?>
        <?php $__env->slot('li_1'); ?> Operators <?php $__env->endSlot(); ?>
        <?php $__env->slot('title'); ?> Operator Management <?php $__env->endSlot(); ?>
    <?php echo $__env->renderComponent(); ?>

    <div class="row">
        <div class="col-lg-12">

            
            <?php if(\Session::has('success')): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="mdi mdi-check-all me-2"></i> <?php echo e(Session::get('success')); ?>

                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
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

            <div class="card">
                <div class="card-body">

                    
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h4 class="card-title">
                            <?php if(\Session::has('updateUser')): ?>
                                <i class="mdi mdi-account-edit-outline me-1"></i> Update Operator
                            <?php else: ?>
                                <i class="mdi mdi-account-plus-outline me-1"></i> New Operator
                            <?php endif; ?>
                        </h4>
                        <?php if(\Session::has('updateUser')): ?>
                            <a href="<?php echo e(url()->current()); ?>" class="btn btn-sm btn-light">
                                <i class="mdi mdi-close me-1"></i> Cancel Edit
                            </a>
                        <?php endif; ?>
                    </div>

                    <form method="post" action="<?php echo e(\Session::has('updateUser') ? route('updateUser') : route('newProfile')); ?>">
                        <?php echo csrf_field(); ?>
                        <?php if(\Session::has('updateUser')): ?>
                            <input type="hidden" name="id" value="<?php echo e(Session::get('updateUser')->id); ?>">
                        <?php endif; ?>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">First Name</label>
                                <input type="text" class="form-control <?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                       id="name" name="name" required
                                       placeholder="Enter first name"
                                       value="<?php echo e(\Session::has('updateUser') ? Session::get('updateUser')->name : old('name')); ?>">
                                <?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="invalid-feedback"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Last Name</label>
                                <input type="text" class="form-control <?php $__errorArgs = ['surname'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                       id="surname" name="surname" required
                                       placeholder="Enter last name"
                                       value="<?php echo e(\Session::has('updateUser') ? Session::get('updateUser')->surname : old('surname')); ?>">
                                <?php $__errorArgs = ['surname'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="invalid-feedback"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Email</label>
                                <input id="email" name="email" type="email" class="form-control <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                       placeholder="Enter email" required
                                       value="<?php echo e(\Session::has('updateUser') ? Session::get('updateUser')->email : old('email')); ?>">
                                <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="invalid-feedback"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Role</label>
                                <select id="role" name="role" class="form-select <?php $__errorArgs = ['role'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" required>
                                    <option value="">Select role</option>
                                    <option value="Admin" <?php echo e((\Session::has('updateUser') && Session::get('updateUser')->role->name == 'Admin') ? 'selected' : ''); ?>>Admin</option>
                                    <option value="Doctor" <?php echo e((\Session::has('updateUser') && Session::get('updateUser')->role->name == 'Doctor') ? 'selected' : ''); ?>>Doctor</option>
                                </select>
                                <?php $__errorArgs = ['role'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="invalid-feedback"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>
                        </div>

                        <hr class="my-3">

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">New Password</label>
                                <div class="input-group auth-pass-inputgroup">
                                    <input type="password" class="form-control <?php $__errorArgs = ['newPassword'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                           id="newPassword" placeholder="Enter new password"
                                           name="newPassword"
                                        <?php echo e(!(\Session::has('updateUser')) ? 'required' : ''); ?>>
                                    <button class="btn btn-light border" type="button" onclick="togglePassword('newPassword', 'iconPass1')">
                                        <i class="mdi mdi-eye-outline" id="iconPass1"></i>
                                    </button>
                                    <?php $__errorArgs = ['newPassword'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="invalid-feedback d-block"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                </div>
                                <?php if(\Session::has('updateUser')): ?>
                                    <div class="form-text text-muted">Leave blank to keep current password.</div>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Confirm Password</label>
                                <div class="input-group auth-pass-inputgroup">
                                    <input type="password" class="form-control <?php $__errorArgs = ['confirmPassword'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                           id="confirmPassword" placeholder="Confirm password"
                                           name="confirmPassword"
                                        <?php echo e(!(\Session::has('updateUser')) ? 'required' : ''); ?>>
                                    <button class="btn btn-light border" type="button" onclick="togglePassword('confirmPassword', 'iconPass2')">
                                        <i class="mdi mdi-eye-outline" id="iconPass2"></i>
                                    </button>
                                    <?php $__errorArgs = ['confirmPassword'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="invalid-feedback d-block"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                </div>
                            </div>
                        </div>

                        <div class="row justify-content-end mt-2">
                            <div class="col-auto">
                                <button type="submit" class="btn btn-primary px-4">
                                    <?php if(\Session::has('updateUser')): ?> <i class="bx bx-save me-1"></i> Update <?php else: ?> <i class="bx bx-plus me-1"></i> Add Operator <?php endif; ?>
                                </button>
                            </div>
                        </div>
                    </form>

                    <hr class="mt-4 mb-4">

                    <h4 class="card-title mb-3">Manage Operators</h4>
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover dt-responsive nowrap w-100 yajra-datatable align-middle">
                            <thead class="table-light">
                            <tr>
                                <th>Email</th>
                                <th>First Name</th>
                                <th>Last Name</th>
                                <th>Role</th>
                                <th style="width: 100px;">Actions</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php $__currentLoopData = \App\Models\User::all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                
                                <?php if(Auth::user()->id != $user->id && $user->id != 1 && $user->role->name != "Patient"): ?>
                                    <tr>
                                        <td><?php echo e($user->email); ?></td>
                                        <td><?php echo e($user->name); ?></td>
                                        <td><?php echo e($user->surname); ?></td>
                                        <td>
                                            <?php if($user->role->name == 'Admin'): ?>
                                                <span class="badge badge-soft-primary font-size-12">Admin</span>
                                            <?php else: ?>
                                                <span class="badge badge-soft-success font-size-12">Doctor</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <ul class="list-unstyled hstack gap-1 mb-0">
                                                <li data-bs-toggle="tooltip" data-bs-placement="top" title="Edit">
                                                    <a href="<?php echo e(route('searchUser', $user->id)); ?>" class="btn btn-sm btn-soft-warning">
                                                        <i class="mdi mdi-pencil-outline font-size-14"></i>
                                                    </a>
                                                </li>
                                                <li data-bs-toggle="tooltip" data-bs-placement="top" title="Delete">
                                                    <a href="#userDelete" id="<?php echo e($user->id); ?>" onclick="deleteUser(this.id)"
                                                       data-bs-toggle="modal" class="btn btn-sm btn-soft-danger">
                                                        <i class="mdi mdi-delete-outline font-size-14"></i>
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
    </div>

    <div class="modal fade" id="userDelete" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content">
                <div class="modal-body px-4 py-5 text-center">
                    <button type="button" class="btn-close position-absolute end-0 top-0 m-3" data-bs-dismiss="modal" aria-label="Close"></button>
                    <div class="avatar-sm mb-4 mx-auto">
                        <div class="avatar-title bg-warning-subtle text-warning font-size-20 rounded-3">
                            <i class="mdi mdi-trash-can-outline"></i>
                        </div>
                    </div>
                    <p class="text-muted font-size-16 mb-4">Are you sure you want to delete this operator?</p>
                    <form action="<?php echo e(route('deleteUser')); ?>" method="post">
                        <?php echo csrf_field(); ?>
                        <input type="hidden" name="user" id="boxDelete">
                        <div class="d-flex gap-2 justify-content-center">
                            <button type="submit" class="btn btn-danger">Delete</button>
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

<?php $__env->stopSection(); ?>

<?php $__env->startSection('script'); ?>
    <script>
        function deleteUser(id){
            document.getElementById('boxDelete').value = id;
        }

        // Funzione unificata per mostrare/nascondere la password
        function togglePassword(inputId, iconId) {
            const input = document.getElementById(inputId);
            const icon = document.getElementById(iconId);

            if (input.type === "password") {
                input.type = "text";
                icon.className = "mdi mdi-eye-off-outline";
            } else {
                input.type = "password";
                icon.className = "mdi mdi-eye-outline";
            }
        }

        $(document).ready(function () {
            // Init Tooltips
            $('[data-bs-toggle="tooltip"]').tooltip();

            // Init DataTable
            $('.yajra-datatable').DataTable({
                responsive: true,
                order: [[0, "asc"]],
                language: {
                    paginate: { next: '>', previous: '<' },
                    search: "Search:",
                    lengthMenu: "Show _MENU_ entries"
                },
                columnDefs: [
                    { orderable: false, targets: -1 } // Disable sorting on Actions column
                ]
            });
        });
    </script>

    <script src="https://cdn.datatables.net/1.13.1/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.1/js/dataTables.bootstrap5.min.js"></script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.master', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Users/lorenzotucceri/Progetti/ISEQL/laravel-iseql/resources/views/userManagement.blade.php ENDPATH**/ ?>