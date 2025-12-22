@extends('layouts.master')

@section('title') Personal Profile @endsection

@section('css')
    <link href="{{URL::asset('/assets/libs/bootstrap-datepicker/bootstrap-datepicker.min.css')}}" rel="stylesheet">
    <link href="{{ URL::asset('/assets/libs/dropzone/dropzone.min.css') }}" rel="stylesheet" type="text/css" />
@endsection

@section('content')

    @component('components.breadcrumb')
        @slot('li_1') Profile @endslot
        @slot('title') Personal Profile @endslot
    @endcomponent

    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-body">

                    {{-- SEZIONE DATI PERSONALI --}}
                    <h4 class="card-title mb-4">Update Profile</h4>

                    @error("update")
                    <div class="alert alert-danger" role="alert">
                        {{$message}}
                    </div>
                    @enderror

                    @if (\Session::has('success'))
                        <div class="alert alert-success" role="alert">
                            {{Session::get('success')}}
                        </div>
                    @endif

                    <form method="post" action="{{route('updateProfile')}}">
                        @csrf
                        @method('PUT') {{-- Metodo standard per update --}}

                        <div class="row mb-4">
                            <label for="email" class="col-form-label col-lg-2">Email</label>
                            <div class="col-lg-10">
                                <input id="email" name="email" value="{{Auth::user()->email}}" type="email"
                                       class="form-control @error('email') is-invalid @enderror"
                                       placeholder="Enter email">
                                @error('email')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-4">
                            <label for="name" class="col-form-label col-lg-2">First Name</label>
                            <div class="col-lg-10">
                                <input type="text" class="form-control @error('name') is-invalid @enderror"
                                       value="{{ Auth::user()->name }}" id="name" name="name" required
                                       placeholder="Enter first name">
                                @error('name')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-4">
                            <label for="surname" class="col-form-label col-lg-2">Last Name</label>
                            <div class="col-lg-10">
                                <input type="text" class="form-control @error('surname') is-invalid @enderror"
                                       value="{{ Auth::user()->surname }}" id="surname" name="surname" required
                                       placeholder="Enter last name">
                                @error('surname')
                                <span class="invalid-feedback" role="alert">
                                   <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row justify-content-end">
                            <div class="col-lg-10">
                                <button type="submit" class="btn btn-primary">Update Profile</button>
                            </div>
                        </div>
                    </form>

                    <hr class="mt-4 mb-4"> {{-- Separatore visivo --}}

                    {{-- SEZIONE PASSWORD --}}
                    <h4 class="card-title mb-4">Update Password</h4>

                    @error("errorPasswordUpdate")
                    <div class="alert alert-danger" role="alert">
                        {{$message}}
                    </div>
                    @enderror

                    @if (\Session::has('successPasswordUpdate'))
                        <div class="alert alert-success" role="alert">
                            {{Session::get('successPasswordUpdate')}}
                        </div>
                    @endif

                    <form method="post" action="{{route('updatePassword')}}">
                        @csrf
                        @method('PUT')

                        <div class="row mb-4">
                            <label for="oldPassword" class="col-form-label col-lg-2">Current Password</label>
                            <div class="col-lg-10">
                                <div class="input-group auth-pass-inputgroup">
                                    <input type="password"
                                           class="form-control @error('oldPassword') is-invalid @enderror"
                                           id="oldPassword" placeholder="Enter current password"
                                           aria-label="Password" name="oldPassword" aria-describedby="password-addon" required>
                                    <button class="btn btn-light" type="button" onclick="togglePassword('oldPassword', 'changeEye0')">
                                        <i class="mdi mdi-eye-outline" id="changeEye0"></i>
                                    </button>
                                    @error('oldPassword')
                                    <span class="invalid-feedback" role="alert">
                                       <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row mb-4">
                            <label for="newPassword" class="col-form-label col-lg-2">New Password</label>
                            <div class="col-lg-10">
                                <div class="input-group auth-pass-inputgroup">
                                    <input type="password"
                                           class="form-control @error('newPassword') is-invalid @enderror"
                                           id="newPassword" placeholder="Enter new password"
                                           aria-label="Password" name="newPassword" aria-describedby="password-addon" required>
                                    <button class="btn btn-light" type="button" onclick="togglePassword('newPassword', 'changeEye1')">
                                        <i class="mdi mdi-eye-outline" id="changeEye1"></i>
                                    </button>
                                    @error('newPassword')
                                    <span class="invalid-feedback" role="alert">
                                       <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row mb-4">
                            <label for="confirmPassword" class="col-form-label col-lg-2">Confirm Password</label>
                            <div class="col-lg-10">
                                <div class="input-group auth-pass-inputgroup">
                                    <input type="password"
                                           class="form-control @error('confirmPassword') is-invalid @enderror"
                                           id="confirmPassword" placeholder="Confirm password"
                                           aria-label="Password1" name="confirmPassword" aria-describedby="password-addon1" required>
                                    <button class="btn btn-light" type="button" onclick="togglePassword('confirmPassword', 'changeEye2')">
                                        <i class="mdi mdi-eye-outline" id="changeEye2"></i>
                                    </button>
                                    @error('confirmPassword')
                                    <span class="invalid-feedback" role="alert">
                                         <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row justify-content-end">
                            <div class="col-lg-10">
                                <button type="submit" class="btn btn-primary">Update Password</button>
                            </div>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script src="{{ URL::asset('/assets/libs/bootstrap-datepicker/bootstrap-datepicker.min.js') }}"></script>
    <script src="{{ URL::asset('/assets/libs/dropzone/dropzone.min.js') }}"></script>

    <script>
        // Funzione unica e riutilizzabile per mostrare/nascondere la password
        function togglePassword(inputId, iconId) {
            var input = document.getElementById(inputId);
            var icon = document.getElementById(iconId);

            if (input.type === "password") {
                input.type = "text";
                icon.className = "mdi mdi-eye-off-outline";
            } else {
                input.type = "password";
                icon.className = "mdi mdi-eye-outline";
            }
        }
    </script>
@endsection
