@extends('layouts.master-without-nav')

@section('title') Register @endsection

@section('body')
@endsection

@section('content')
    <style>
        /* Modern Card Styling (Stesso CSS del Login) */
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

        /* Input Group Text */
        .btn-light {
            background-color: #eff2f7;
            border-color: #eff2f7;
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
                                        <h5 class="text-primary">Free Register</h5>
                                        <p>Get your free Doctor account now.</p>
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
                                <form class="form-horizontal" method="POST" action="{{ route('registerDoctor') }}">
                                    @csrf

                                    {{-- Nome --}}
                                    <div class="mb-3">
                                        <label for="name" class="form-label">Name</label>
                                        <input type="text" class="form-control @error('name') is-invalid @enderror"
                                               value="{{ old('name') }}" id="name" name="name" autofocus placeholder="Enter name">
                                        @error('name')
                                        <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>

                                    {{-- Cognome --}}
                                    <div class="mb-3">
                                        <label for="surname" class="form-label">Surname</label>
                                        <input type="text" class="form-control @error('surname') is-invalid @enderror"
                                               value="{{ old('surname') }}" id="surname" name="surname" placeholder="Enter surname">
                                        @error('surname')
                                        <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>

                                    {{-- Email --}}
                                    <div class="mb-3">
                                        <label for="useremail" class="form-label">Email</label>
                                        <input type="email" class="form-control @error('email') is-invalid @enderror"
                                               value="{{ old('email') }}" id="useremail" name="email" placeholder="Enter email">
                                        @error('email')
                                        <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>

                                    {{-- Password --}}
                                    <div class="mb-3">
                                        <label for="userpassword" class="form-label">Password</label>
                                        <input type="password" class="form-control @error('password') is-invalid @enderror"
                                               id="userpassword" name="password" placeholder="Enter password">
                                        @error('password')
                                        <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>

                                    {{-- Conferma Password --}}
                                    <div class="mb-3">
                                        <label for="confirmpassword" class="form-label">Confirm Password</label>
                                        <input type="password" class="form-control"
                                               id="confirmpassword" name="password_confirmation" placeholder="Confirm password">
                                    </div>

                                    <div class="mt-4 d-grid">
                                        <button class="btn btn-primary waves-effect waves-light" type="submit">Register</button>
                                    </div>

                                    <div class="mt-4 text-center">
                                        <p class="mb-0">Already have an account? <a href="{{ route('login') }}" class="text-primary fw-medium"> Login </a> </p>
                                    </div>

                                </form>
                            </div>
                        </div>
                    </div>

                    <div class="mt-5 text-center">
                        <p class="text-muted">© <script>document.write(new Date().getFullYear())</script> </p>
                    </div>

                </div>
            </div>
        </div>
    </div>
@endsection
