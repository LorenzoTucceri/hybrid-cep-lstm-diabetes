@extends('layouts.master-without-nav')

@section('title')
    Reimposta la password
@endsection

@section('body')

    <body>
    @endsection

    @section('content')
        <div class="account-pages my-5 pt-sm-5">
            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-md-8 col-lg-6 col-xl-5">
                        <div class="card overflow-hidden">
                            <div class="bg-primary bg-soft">
                                <div class="row">
                                    <div class="col-7">
                                        <div class="text-primary p-4">
                                            <h5 class="text-primary"> Recupera la password</h5>
                                            <p>Reimposta la password.</p>
                                        </div>
                                    </div>
                                    <div class="col-5 align-self-end">
                                        <img src="{{ URL::asset('/assets/images/profile-img.png') }}" alt=""
                                             class="img-fluid">
                                    </div>
                                </div>
                            </div>
                            <div class="card-body pt-0">
                                <div>
                                    <a href="{{route("login")}}">
                                        <div class="avatar-md profile-user-wid mb-4">
                                            <span class="avatar-title rounded-circle bg-light">
                                                 <img src="{{ URL::asset('/assets/images/logo.svg') }}" alt=""
                                                      class="rounded-circle" height="34">
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
                                            <input type="email"  class="form-control @error('email') is-invalid @enderror"
                                                   id="useremail" name="email" placeholder="Inserisci l'email"
                                                   value="{{ $email ?? old('email') }}" id="email">
                                            @error('email')
                                            <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>

                                        <div class="mb-3">
                                            <label for="name">Password</label>
                                            <div class="input-group auth-pass-inputgroup @error('password') is-invalid @enderror">
                                                <input type="password"
                                                       class="form-control  @error('password') is-invalid @enderror"
                                                       id="password"  placeholder="Inserisci la password"
                                                       aria-label="Password1" name="password" autofocus aria-describedby="password-addon1" required >
                                                <button class="btn btn-light " type="button"  onclick="changeTypeBox()"><i
                                                        class="mdi mdi-eye-outline" id="changeEye"></i></button></div>
                                            @error('password')
                                            <span class="invalid-feedback" role="alert">
                                         <strong>{{ $message }}</strong>
                                    </span>
                                            @enderror
                                        </div>

                                        <div class="mb-3">
                                            <label for="name">Conferma password</label>
                                            <div class="input-group auth-pass-inputgroup @error('password_confirmation') is-invalid @enderror">
                                                <input type="password"
                                                       class="form-control  @error('password_confirmation') is-invalid @enderror"
                                                       id="newPassword"  placeholder="Inserisci il conferma password"
                                                       aria-label="Password" name="password_confirmation" autofocus aria-describedby="password-addon" required
                                                >
                                                <button class="btn btn-light " type="button"  onclick="changeTypeBox1()"><i
                                                        class="mdi mdi-eye-outline" id="changeEye1"></i></button></div>
                                            @error('newPassword')
                                            <span class="invalid-feedback" role="alert">
                                   <strong>{{ $message }}</strong>
                                </span>
                                            @enderror
                                        </div>

                                        <div class="text-end">
                                            <button class="btn btn-primary w-md waves-effect waves-light"
                                                    type="submit">Reset</button>
                                        </div>

                                    </form>
                                </div>

                            </div>
                        </div>

                        <div class="mt-5 text-center">
                            <p><a href="{{ url('login') }}" class="fw-medium text-primary">Login</a>
                            </p>
                            <p>© <script>
                                    document.write(new Date().getFullYear())

                                </script> ISEQL-Glucose Analyzer.
                            </p>
                        </div>


                    </div>
                </div>
            </div>
        </div>

    @endsection
    @section('script')

        <script>


            function changeTypeBox1(){
                if(document.getElementById("newPassword").type=="password"){
                    document.getElementById("newPassword").type = "text"
                    document.getElementById("changeEye1").className="mdi mdi-eye-off-outline"
                }
                else{
                    document.getElementById("newPassword").type = "password"
                    document.getElementById("changeEye1").className="mdi mdi-eye-outline"
                }
            }

            function changeTypeBox(){
                if(document.getElementById("password").type=="password"){
                    document.getElementById("password").type = "text"
                    document.getElementById("changeEye").className="mdi mdi-eye-off-outline"
                }
                else{
                    document.getElementById("password").type = "password"
                    document.getElementById("changeEye").className="mdi mdi-eye-outline"
                }
            }
        </script>
@endsection
