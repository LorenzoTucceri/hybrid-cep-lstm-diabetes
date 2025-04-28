@extends('layouts.master')

@section('title') Personal Profile @endsection

@section('css')
    <!-- bootstrap datepicker -->
    <link href="{{URL::asset('/assets/libs/bootstrap-datepicker/bootstrap-datepicker.min.css')}}" rel="stylesheet">

    <!-- dropzone css -->
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
                    <form method="post" action="{{route('updateProfile')}}" enctype="multipart/form-data"> @csrf
                        <div class="row mb-4">
                            <label for="email" class="col-form-label col-lg-2">Email</label>
                            <div class="col-lg-10">
                                <input id="email" name="email" value="{{Auth::user()->email}}" type="email" class="form-control @error('email') is-invalid @enderror"
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
                                       value="{{ Auth::user()->name }}" id="name" name="name" autofocus required
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
                                       value="{{ Auth::user()->surname }}" id="surname" name="surname" autofocus required
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
                                <button type="submit" class="btn btn-primary">Update</button>
                            </div>
                        </div>
                    </form><br>
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
                    <form method="post" action="{{route('updatePassword')}}"> @csrf
                        <div class="row mb-4">
                            <label for="oldPassword" class="col-form-label col-lg-2">Current Password</label>
                            <div class="col-lg-10">
                                <div class="input-group auth-pass-inputgroup @error('oldPassword') is-invalid @enderror">
                                    <input type="password"
                                           class="form-control  @error('oldPassword') is-invalid @enderror"
                                           id="oldPassword"  placeholder="Enter current password"
                                           aria-label="Password" name="oldPassword" autofocus aria-describedby="password-addon"  required>
                                    <button class="btn btn-light " type="button"  onclick="changeTypeBox0()"><i
                                            class="mdi mdi-eye-outline" id="changeEye0"></i></button></div>
                                @error('oldPassword')
                                <span class="invalid-feedback" role="alert">
                                   <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-4">
                            <label for="newPassword" class="col-form-label col-lg-2">New Password</label>
                            <div class="col-lg-10">
                                <div class="input-group auth-pass-inputgroup @error('newPassword') is-invalid @enderror">
                                    <input type="password"
                                           class="form-control  @error('newPassword') is-invalid @enderror"
                                           id="newPassword"  placeholder="Enter new password"
                                           aria-label="Password" name="newPassword" autofocus aria-describedby="password-addon" required >
                                    <button class="btn btn-light " type="button"  onclick="changeTypeBox1()"><i
                                            class="mdi mdi-eye-outline" id="changeEye1"></i></button></div>
                                @error('newPassword')
                                <span class="invalid-feedback" role="alert">
                                   <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>
                        </div>
                        <div class="row mb-4">
                            <label for="confirmPassword" class="col-form-label col-lg-2">Confirm Password</label>
                            <div class="col-lg-10">
                                <div class="input-group auth-pass-inputgroup @error('confirmPassword') is-invalid @enderror">
                                    <input type="password"
                                           class="form-control  @error('confirmPassword') is-invalid @enderror"
                                           id="confirmPassword"  placeholder="Confirm password"
                                           aria-label="Password1" name="confirmPassword" autofocus aria-describedby="password-addon1" required >
                                    <button class="btn btn-light " type="button"  onclick="changeTypeBox()"><i
                                            class="mdi mdi-eye-outline" id="changeEye"></i></button></div>
                                @error('confirmPassword')
                                <span class="invalid-feedback" role="alert">
                                         <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>
                        <div class="row justify-content-end">
                            <div class="col-lg-10">
                                <button type="submit" class="btn btn-primary">Update</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <!-- end row -->
@endsection
@section('script')
    <!-- bootstrap datepicker -->
    <script src="{{ URL::asset('/assets/libs/bootstrap-datepicker/bootstrap-datepicker.min.js') }}"></script>
    <!-- dropzone plugin -->
    <script src="{{ URL::asset('/assets/libs/dropzone/dropzone.min.js') }}"></script>

    <script>

        function changeTypeBox0(){
            if(document.getElementById("oldPassword").type=="password"){
                document.getElementById("oldPassword").type = "text"
                document.getElementById("changeEye0").className="mdi mdi-eye-off-outline"
            }
            else{
                document.getElementById("oldPassword").type = "password"
                document.getElementById("changeEye0").className="mdi mdi-eye-outline"
            }
        }

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
@endsection
