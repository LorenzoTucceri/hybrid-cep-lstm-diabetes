@extends('layouts.master-without-nav')

@section('title') Reset Password @endsection

@section('body')
@endsection

@section('content')
    <style>
        /* Modern Card Styling coerente con Login/Register */
        .card {
            border: none;
            box-shadow: 0 0.75rem 1.5rem rgba(18, 38, 63, 0.03);
            border-radius: 1rem;
            overflow: hidden;
        }

        /* Soft Backgrounds */
        .bg-soft-primary { background-color: rgba(85, 110, 230, 0.1) !important; color: #556ee6 !important; }

        /* Form Controls */
        .form-control {
            border-radius: 0.5rem;
            padding: 0.6rem 1rem;
            border: 1px solid #ced4da;
        }
        .form-control:focus {
            border-color: #556ee6;
            box-shadow: 0 0 0 0.15rem rgba(85, 110, 230, 0.25);
        }

        /* Logo Container */
        .profile-user-wid {
            margin-top: -26px;
        }
        .avatar-md {
            height: 4.5rem;
            width: 4.5rem;
        }
        .avatar-title {
            box-shadow: 0 0.5rem 1rem rgba(0,0,0,0.1);
        }
    </style>

    <body>
    <div class="account-pages my-5 pt-sm-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-8 col-lg-6 col-xl-5">

                    <div class="card overflow-hidden">
                        <div class="bg-primary bg-soft">
                            <div class="row">
                                <div class="col-7">
                                    <div class="text-primary p-4">
                                        <h5 class="text-primary">Reset Password</h5>
                                        <p>Set your new password below.</p>
                                    </div>
                                </div>
                                <div class="col-5 align-self-end text-end">
                                    <i class="bx bx-pulse text-primary" style="font-size: 6rem; opacity: 0.3; margin-right: 10px; margin-bottom: -10px;"></i>
                                </div>
                            </div>
                        </div>

                        <div class="card-body pt-0">
                            <div class="auth-logo">
                                <a href="index" class="auth-logo-light">
                                    <div class="avatar-md profile-user-wid mb-4">
                                        <span class="avatar-title rounded-circle bg-light">
                                            <img src="{{ URL::asset('/assets/images/logo-light.svg') }}" alt="" class="rounded-circle" height="34">
                                        </span>
                                    </div>
                                </a>

                                <a href="index" class="auth-logo-dark">
                                    <div class="avatar-md profile-user-wid mb-4">
                                        <span class="avatar-title rounded-circle bg-white">
                                            <img src="{{ URL::asset('/assets/images/logo3.png') }}" alt="" height="50">
                                        </span>
                                    </div>
                                </a>
                            </div>

                            <div class="p-2">
                                <form class="form-horizontal" method="POST" action="{{ route('password.update') }}">
                                    @csrf
                                    <input type="hidden" name="token" value="{{ $token }}">

                                    <div class="mb-3">
                                        <label for="useremail" class="form-label">Email</label>
                                        <input type="email" class="form-control @error('email') is-invalid @enderror"
                                               id="useremail" name="email" placeholder="Enter email"
                                               value="{{ $email ?? old('email') }}" required>
                                        @error('email')
                                        <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>

                                    <div class="mb-3">
                                        <label for="password" class="form-label">New Password</label>
                                        <div class="input-group auth-pass-inputgroup @error('password') is-invalid @enderror">
                                            <input type="password" name="password"
                                                   class="form-control @error('password') is-invalid @enderror"
                                                   id="password" placeholder="Enter new password"
                                                   required>
                                            <button class="btn btn-light" type="button" onclick="togglePassword('password', 'eye-icon')">
                                                <i class="mdi mdi-eye-outline" id="eye-icon"></i>
                                            </button>
                                        </div>
                                        @error('password')
                                        <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>

                                    <div class="mb-3">
                                        <label for="password-confirm" class="form-label">Confirm Password</label>
                                        <div class="input-group auth-pass-inputgroup">
                                            <input type="password" name="password_confirmation"
                                                   class="form-control" id="password-confirm"
                                                   placeholder="Confirm new password" required>
                                            <button class="btn btn-light" type="button" onclick="togglePassword('password-confirm', 'eye-icon-confirm')">
                                                <i class="mdi mdi-eye-outline" id="eye-icon-confirm"></i>
                                            </button>
                                        </div>
                                    </div>

                                    <div class="mt-4 d-grid">
                                        <button class="btn btn-primary waves-effect waves-light" type="submit">Reset Password</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <div class="mt-5 text-center">
                        <p>Back to <a href="{{ route('login') }}" class="fw-medium text-primary"> Login </a> </p>
                        <p class="text-muted">© <script>document.write(new Date().getFullYear())</script> Glucose Analysis. Crafted with <i class="mdi mdi-heart text-danger"></i></p>
                    </div>

                </div>
            </div>
        </div>
    </div>
    @endsection

    @section('script')
        <script>
            function togglePassword(inputId, iconId) {
                const input = document.getElementById(inputId);
                const icon = document.getElementById(iconId);
                if (input.type === "password") {
                    input.type = "text";
                    icon.classList.remove("mdi-eye-outline");
                    icon.classList.add("mdi-eye-off-outline");
                } else {
                    input.type = "password";
                    icon.classList.remove("mdi-eye-off-outline");
                    icon.classList.add("mdi-eye-outline");
                }
            }
        </script>
@endsection
