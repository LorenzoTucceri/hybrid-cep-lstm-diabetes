@extends('layouts.master-without-nav')

@section('title') Login @endsection

@section('body')
@endsection

@section('content')
    <body>
    <!-- style="background-color:  #556ee6" -->
    <div class="account-pages my-5 pt-sm-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-8 col-lg-6 col-xl-5">
                    <div class="card overflow-hidden">
                        <div class="bg-primary bg-soft">
                            <div class="row">
                                <div class="col-7">
                                    <div class="text-primary p-4">
                                        <h5 class="text-primary">Welcome Back!</h5>
                                        <p>Sign in to continue.</p>
                                    </div>
                                </div>
                                <div class="col-5 align-self-end">
                                    <img src="{{ URL::asset('/assets/images/profile-img.png') }}" alt=""
                                         class="img-fluid">
                                </div>
                            </div>
                        </div>
                        <div class="card-body pt-0" >
                            <div class="auth-logo" >
                                <a href="index" class="auth-logo-light">
                                    <div class="avatar-md profile-user-wid mb-4">
                                    <span class="avatar-title rounded-circle bg-light">
                                        <img src="{{ URL::asset('/assets/images/logo-light.svg') }}" alt=""
                                             class="rounded-circle" height="34">
                                    </span>
                                    </div>
                                </a>

                                <a href="index" class="auth-logo-dark" >
                                    <div class="avatar-md profile-user-wid mb-4" >
                                    <span class="avatar-title rounded-circle bg-light" >
                                        <img src="{{ URL::asset('/assets/images/logo3.png') }}" alt=""
                                             height="70">
                                    </span>
                                    </div>
                                </a>
                            </div>
                            <div class="p-2">
                                <form class="form-horizontal" method="POST" action="{{ route('login') }}">   @csrf
                                    <div class="mb-3">

                                        @error("error")
                                        <div class="alert alert-danger" role="alert">
                                            {{$message}}
                                        </div>
                                        @enderror
                                        <label for="username" class="form-label">Email</label>
                                        <input name="email" type="email"
                                               class="form-control @error('email') is-invalid @enderror"

                                               id="username"

                                               value="{{ old('email', 'admin@libero.it') }}" id="username"

                                               placeholder="Enter email" autocomplete="email" autofocus>
                                        @error('email')
                                        <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                        @enderror
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Password</label>
                                        <div
                                            class="input-group auth-pass-inputgroup @error('password') is-invalid @enderror">
                                            <input type="password" name="password"
                                                   class="form-control @error('password') is-invalid @enderror"

                                                   id="userpassword" placeholder="Enter password"

                                                   id="userpassword" value="123456" placeholder="Enter password"

                                                   aria-label="Password" aria-describedby="password-addon">
                                            <button class="btn btn-light" type="button" id="password-addon"><i
                                                    class="mdi mdi-eye-outline"></i></button>
                                            @error('password')
                                            <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="mt-3 d-grid">
                                        <button class="btn btn-primary waves-effect waves-light" type="submit">Sign In</button>
                                    </div>

                                    <div class="mt-4 text-center">
                                        @if (Route::has('password.request'))
                                            <a href="{{ route('password.request') }}" class="text-muted"><i
                                                    class="mdi mdi-lock me-1"></i> Forgot your password?</a>
                                        @endif

                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
    <!-- end account-pages -->
@endsection
