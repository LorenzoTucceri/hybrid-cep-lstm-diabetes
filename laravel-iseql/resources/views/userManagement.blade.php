@extends('layouts.master')

@section('title') Operator Management @endsection

@section('css')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css"/>
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.2.2/css/buttons.dataTables.min.css">
    <style>
        .badge-soft-primary { color: #556ee6; background-color: rgba(85,110,230,.18); }
        .badge-soft-success { color: #34c38f; background-color: rgba(52,195,143,.18); }
        .cursor-pointer { cursor: pointer; }
    </style>
@endsection

@section('content')

    @component('components.breadcrumb')
        @slot('li_1') Operators @endslot
        @slot('title') Operator Management @endslot
    @endcomponent

    <div class="row">
        <div class="col-lg-12">

            {{-- Messaggi di feedback --}}
            @if (\Session::has('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="mdi mdi-check-all me-2"></i> {{Session::get('success')}}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
            @error("error")
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="mdi mdi-block-helper me-2"></i> {{$message}}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            @enderror

            <div class="card">
                <div class="card-body">

                    {{-- HEADER DEL FORM: Cambia titolo in base allo stato --}}
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h4 class="card-title">
                            @if (\Session::has('updateUser'))
                                <i class="mdi mdi-account-edit-outline me-1"></i> Update Operator
                            @else
                                <i class="mdi mdi-account-plus-outline me-1"></i> New Operator
                            @endif
                        </h4>
                        @if (\Session::has('updateUser'))
                            <a href="{{ url()->current() }}" class="btn btn-sm btn-light">
                                <i class="mdi mdi-close me-1"></i> Cancel Edit
                            </a>
                        @endif
                    </div>

                    <form method="post" action="{{ \Session::has('updateUser') ? route('updateUser') : route('newProfile') }}">
                        @csrf
                        @if (\Session::has('updateUser'))
                            <input type="hidden" name="id" value="{{Session::get('updateUser')->id}}">
                        @endif

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">First Name</label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror"
                                       id="name" name="name" required
                                       placeholder="Enter first name"
                                       value="{{ \Session::has('updateUser') ? Session::get('updateUser')->name : old('name') }}">
                                @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Last Name</label>
                                <input type="text" class="form-control @error('surname') is-invalid @enderror"
                                       id="surname" name="surname" required
                                       placeholder="Enter last name"
                                       value="{{ \Session::has('updateUser') ? Session::get('updateUser')->surname : old('surname') }}">
                                @error('surname') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Email</label>
                                <input id="email" name="email" type="email" class="form-control @error('email') is-invalid @enderror"
                                       placeholder="Enter email" required
                                       value="{{ \Session::has('updateUser') ? Session::get('updateUser')->email : old('email') }}">
                                @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Role</label>
                                <select id="role" name="role" class="form-select @error('role') is-invalid @enderror" required>
                                    <option value="">Select role</option>
                                    <option value="Admin" {{ (\Session::has('updateUser') && Session::get('updateUser')->role->name == 'Admin') ? 'selected' : '' }}>Admin</option>
                                    <option value="Doctor" {{ (\Session::has('updateUser') && Session::get('updateUser')->role->name == 'Doctor') ? 'selected' : '' }}>Doctor</option>
                                </select>
                                @error('role') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <hr class="my-3">

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">New Password</label>
                                <div class="input-group auth-pass-inputgroup">
                                    <input type="password" class="form-control @error('newPassword') is-invalid @enderror"
                                           id="newPassword" placeholder="Enter new password"
                                           name="newPassword"
                                        {{ !(\Session::has('updateUser')) ? 'required' : '' }}>
                                    <button class="btn btn-light border" type="button" onclick="togglePassword('newPassword', 'iconPass1')">
                                        <i class="mdi mdi-eye-outline" id="iconPass1"></i>
                                    </button>
                                    @error('newPassword') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                </div>
                                @if(\Session::has('updateUser'))
                                    <div class="form-text text-muted">Leave blank to keep current password.</div>
                                @endif
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Confirm Password</label>
                                <div class="input-group auth-pass-inputgroup">
                                    <input type="password" class="form-control @error('confirmPassword') is-invalid @enderror"
                                           id="confirmPassword" placeholder="Confirm password"
                                           name="confirmPassword"
                                        {{ !(\Session::has('updateUser')) ? 'required' : '' }}>
                                    <button class="btn btn-light border" type="button" onclick="togglePassword('confirmPassword', 'iconPass2')">
                                        <i class="mdi mdi-eye-outline" id="iconPass2"></i>
                                    </button>
                                    @error('confirmPassword') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row justify-content-end mt-2">
                            <div class="col-auto">
                                <button type="submit" class="btn btn-primary px-4">
                                    @if (\Session::has('updateUser')) <i class="bx bx-save me-1"></i> Update @else <i class="bx bx-plus me-1"></i> Add Operator @endif
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
                            @foreach(\App\Models\User::all() as $user)
                                {{-- Exclude self, superadmin (id 1) and Patients --}}
                                @if(Auth::user()->id != $user->id && $user->id != 1 && $user->role->name != "Patient")
                                    <tr>
                                        <td>{{$user->email}}</td>
                                        <td>{{$user->name}}</td>
                                        <td>{{$user->surname}}</td>
                                        <td>
                                            @if($user->role->name == 'Admin')
                                                <span class="badge badge-soft-primary font-size-12">Admin</span>
                                            @else
                                                <span class="badge badge-soft-success font-size-12">Doctor</span>
                                            @endif
                                        </td>
                                        <td>
                                            <ul class="list-unstyled hstack gap-1 mb-0">
                                                <li data-bs-toggle="tooltip" data-bs-placement="top" title="Edit">
                                                    <a href="{{route('searchUser', $user->id)}}" class="btn btn-sm btn-soft-warning">
                                                        <i class="mdi mdi-pencil-outline font-size-14"></i>
                                                    </a>
                                                </li>
                                                <li data-bs-toggle="tooltip" data-bs-placement="top" title="Delete">
                                                    <a href="#userDelete" id="{{$user->id}}" onclick="deleteUser(this.id)"
                                                       data-bs-toggle="modal" class="btn btn-sm btn-soft-danger">
                                                        <i class="mdi mdi-delete-outline font-size-14"></i>
                                                    </a>
                                                </li>
                                            </ul>
                                        </td>
                                    </tr>
                                @endif
                            @endforeach
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
                    <form action="{{route('deleteUser')}}" method="post">
                        @csrf
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

@endsection

@section('script')
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
@endsection
