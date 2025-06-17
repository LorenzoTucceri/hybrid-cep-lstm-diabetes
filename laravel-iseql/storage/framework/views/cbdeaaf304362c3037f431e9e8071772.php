<?php $__env->startSection('title'); ?> Registrazione <?php $__env->stopSection(); ?>

<?php $__env->startSection('body'); ?>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <body>
    <div class="account-pages my-5 pt-sm-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-8 col-lg-6 col-xl-5">
                    <div class="card overflow-hidden">
                        <div class="bg-soft py-1" style="background-color: white;">
                            <div class="row justify-content-center">
                                <div class="col-12 text-center">
                                    <img src="<?php echo e(URL::asset('/assets/images/logo4.png')); ?>" alt=""
                                         height="130">
                                </div>
                            </div>
                        </div>
                        <div class="card-body pt-2">
                            <?php if(\Session::has('success')): ?>
                                <div class="alert alert-success" role="alert">
                                    <?php echo e(\Session::get('success')); ?>

                                </div>
                            <?php endif; ?>
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

                            <div class="">
                                <form class="form-horizontal" method="POST" action="<?php echo e(route('register.token.submit', $token)); ?>">
                                    <?php echo csrf_field(); ?>

                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label for="nome" class="form-label">Nome</label>
                                            <input name="name" type="text" class="form-control" id="nome"
                                                   value="<?php echo e(old('name', $name)); ?>" disabled>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="cognome" class="form-label">Cognome</label>
                                            <input name="surname" type="text" class="form-control" id="cognome"
                                                   value="<?php echo e(old('surname', $surname)); ?>" disabled>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label for="email" class="form-label">Email</label>
                                        <input name="emailF" type="email" class="form-control" id="email"
                                               value="<?php echo e(old('email', $email)); ?>" disabled>
                                        <input name="email" type="hidden" class="form-control" id="email"
                                               value="<?php echo e(old('email', $email)); ?>">
                                    </div>

                                    <div class="mb-3">
                                        <label for="password" class="form-label">Password</label>
                                        <div class="input-group auth-pass-inputgroup">
                                            <input type="password" name="newPassword"
                                                   class="form-control <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                                   id="password"
                                                   placeholder="Inserisci la password" minlength="6" required>
                                            <button class="btn btn-light toggle-password" type="button" data-target="#password">
                                                <i class="mdi mdi-eye-outline"></i>
                                            </button>
                                            <?php $__errorArgs = ['password'];
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

                                    <div class="mb-3">
                                        <label for="password-confirm" class="form-label">Conferma Password</label>
                                        <div class="input-group auth-pass-inputgroup">
                                            <input type="password" name="confirmPassword"
                                                   class="form-control"
                                                   id="password-confirm"
                                                   placeholder="Conferma la password" minlength="6">
                                            <button class="btn btn-light toggle-password" type="button" data-target="#password-confirm" required>
                                                <i class="mdi mdi-eye-outline"></i>
                                            </button>
                                        </div>
                                    </div>

                                    <div class="mt-3 d-grid">
                                        <button class="btn btn-primary waves-effect waves-light" type="submit">Registrati</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript per abilitare il toggle della visibilità delle password -->
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            document.querySelectorAll('.toggle-password').forEach(button => {
                button.addEventListener('click', function() {
                    const target = document.querySelector(button.getAttribute('data-target'));
                    const type = target.getAttribute('type') === 'password' ? 'text' : 'password';
                    target.setAttribute('type', type);
                    button.innerHTML = `<i class="mdi mdi-eye${type === 'password' ? '-outline' : ''}"></i>`;
                });
            });
        });
    </script>
    </body>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.master-without-nav', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Applications/MAMP/htdocs/ISEQL/laravel-iseql/resources/views/auth/register.blade.php ENDPATH**/ ?>