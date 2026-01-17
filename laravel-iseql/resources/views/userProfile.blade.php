@extends('layouts.master')

@section('title') Personal Profile @endsection

@section('css')
    <link href="{{URL::asset('/assets/libs/bootstrap-datepicker/bootstrap-datepicker.min.css')}}" rel="stylesheet">
    {{-- Dropzone non sembra essere usato qui per ora, ma lo lascio se serve per upload futuri --}}
    <link href="{{ URL::asset('/assets/libs/dropzone/dropzone.min.css') }}" rel="stylesheet" type="text/css" />
@endsection

@section('content')

    @component('components.breadcrumb')
        @slot('li_1') Profile @endslot
        @slot('title') Personal Profile @endslot
    @endcomponent

    <div class="row">
        <div class="col-xl-4">
            <div class="card overflow-hidden">
                <div class="bg-primary-subtle bg-primary bg-soft">
                    <div class="row">
                        <div class="col-7">
                            <div class="text-primary p-3">
                                <h5 class="text-primary">Welcome Back!</h5>
                                <p>{{ Auth::user()->name }}</p>
                            </div>
                        </div>
                        <div class="col-5 align-self-end">
                            <i class="bx bx-pulse text-primary" style="font-size: 6rem; opacity: 0.3; margin-right: 10px; margin-bottom: -10px;"></i>                        </div>
                    </div>
                </div>
                <div class="card-body pt-0">
                    <div class="row">
                        <div class="col-sm-12">
                            <div class="avatar-md profile-user-wid mb-4">
                                <img src="/images/avatar-default.jpeg" alt="" class="img-thumbnail rounded-circle">

                            </div>
                            <h5 class="font-size-15 text-truncate">{{ Auth::user()->name }} {{ Auth::user()->surname }}</h5>
                            <p class="text-muted mb-0 text-truncate">{{ Auth::user()->role->name }}</p> {{-- Ruolo statico o dinamico --}}
                        </div>
                    </div>
                </div>
            </div>

            {{-- Card Informativa Extra (Opzionale) --}}
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title mb-4">Personal Info</h4>
                    <div class="table-responsive">
                        <table class="table table-nowrap mb-0">
                            <tbody>
                            <tr>
                                <th scope="row">Full Name :</th>
                                <td>{{ Auth::user()->name }} {{ Auth::user()->surname }}</td>
                            </tr>
                            <tr>
                                <th scope="row">E-mail :</th>
                                <td>{{ Auth::user()->email }}</td>
                            </tr>

                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-8">

            {{-- CARD 1: UPDATE PROFILE --}}
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title mb-4">Edit Details</h4>

                    @if (\Session::has('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="mdi mdi-check-all me-2"></i>
                            {{Session::get('success')}}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    @error("update")
                    <div class="alert alert-danger" role="alert">{{$message}}</div>
                    @enderror

                    <form method="post" action="{{route('updateProfile')}}">
                        @csrf
                        @method('PUT')

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="name" class="form-label">First Name</label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror"
                                           value="{{ Auth::user()->name }}" id="name" name="name" required
                                           placeholder="Enter first name">
                                    @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="surname" class="form-label">Last Name</label>
                                    <input type="text" class="form-control @error('surname') is-invalid @enderror"
                                           value="{{ Auth::user()->surname }}" id="surname" name="surname" required
                                           placeholder="Enter last name">
                                    @error('surname')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">Email Address</label>
                            <input id="email" name="email" value="{{Auth::user()->email}}" type="email"
                                   class="form-control @error('email') is-invalid @enderror"
                                   placeholder="Enter email">
                            @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="text-end">
                            <button type="submit" class="btn btn-primary w-md">Save Changes</button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- CARD 2: UPDATE PASSWORD --}}
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title mb-4">Security / Change Password</h4>

                    @if (\Session::has('successPasswordUpdate'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="mdi mdi-check-all me-2"></i>
                            {{Session::get('successPasswordUpdate')}}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    @error("errorPasswordUpdate")
                    <div class="alert alert-danger" role="alert">{{$message}}</div>
                    @enderror

                    <form method="post" action="{{route('updatePassword')}}">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label for="oldPassword" class="form-label">Current Password</label>
                            <div class="input-group auth-pass-inputgroup">
                                <input type="password" class="form-control @error('oldPassword') is-invalid @enderror"
                                       id="oldPassword" name="oldPassword" placeholder="Enter current password" required>
                                <button class="btn btn-light ms-0" type="button" onclick="togglePassword('oldPassword', this)">
                                    <i class="mdi mdi-eye-outline"></i>
                                </button>
                                @error('oldPassword')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="newPassword" class="form-label">New Password</label>
                                    <div class="input-group auth-pass-inputgroup">
                                        <input type="password" class="form-control @error('newPassword') is-invalid @enderror"
                                               id="newPassword" name="newPassword" placeholder="Enter new password" required>
                                        <button class="btn btn-light ms-0" type="button" onclick="togglePassword('newPassword', this)">
                                            <i class="mdi mdi-eye-outline"></i>
                                        </button>
                                        @error('newPassword')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="confirmPassword" class="form-label">Confirm Password</label>
                                    <div class="input-group auth-pass-inputgroup">
                                        <input type="password" class="form-control @error('confirmPassword') is-invalid @enderror"
                                               id="confirmPassword" name="confirmPassword" placeholder="Confirm password" required>
                                        <button class="btn btn-light ms-0" type="button" onclick="togglePassword('confirmPassword', this)">
                                            <i class="mdi mdi-eye-outline"></i>
                                        </button>
                                        @error('confirmPassword')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="text-end">
                            <button type="submit" class="btn btn-danger w-md">Update Password</button>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>
@endsection

@section('script')
    <script src="{{ URL::asset('/assets/libs/bootstrap-datepicker/bootstrap-datepicker.min.js') }}"></script>
    {{-- Dropzone mantenuto se serve, altrimenti rimuovilo --}}
    <script src="{{ URL::asset('/assets/libs/dropzone/dropzone.min.js') }}"></script>

    <script>
        /**
         * Gestisce la visibilità della password.
         * @param {string} inputId - L'ID del campo input
         * @param {HTMLElement} btnElement - L'elemento bottone cliccato (this)
         */
        function togglePassword(inputId, btnElement) {
            var input = document.getElementById(inputId);
            // Trova l'icona dentro il bottone cliccato
            var icon = btnElement.querySelector('i');

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
