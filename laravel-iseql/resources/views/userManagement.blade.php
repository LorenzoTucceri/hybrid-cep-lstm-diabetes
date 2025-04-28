@extends('layouts.master')

@section('title') Operator Management @endsection

@section('css')

    <!--datatable css-->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css"/>
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.2.2/css/buttons.dataTables.min.css">


@endsection

@section('content')



    @component('components.breadcrumb')
        @slot('li_1') Operators @endslot
        @slot('title') Operator Management @endslot
    @endcomponent
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title mb-4">@if (\Session::has('updateUser')) Update Operator @else New Operator @endif</h4>
                    @error("error")
                    <div class="alert alert-danger" role="alert">
                        {{$message}}
                    </div>
                    @enderror
                    @if (\Session::has('success'))
                        <div class="alert alert-success" role="alert">
                            {{Session::get('success')}}
                        </div>
                    @endif
                    <form method="post" @if (\Session::has('updateUser')) action="{{route('updateUser')}}" @else action="{{route('newProfile')}}" @endif> @csrf
                        @if (\Session::has('updateUser'))
                            <input type="hidden" name="id" value="{{Session::get('updateUser')->id}}">
                        @endif

                        <div class="row mb-4">
                            <label for="email" class="col-form-label col-lg-2">Email</label>
                            <div class="col-lg-10">
                                <input id="email" name="email" type="email" class="form-control @error('email') is-invalid @enderror"
                                       placeholder="Enter email" required @if (\Session::has('updateUser')) value="{{Session::get("updateUser")->email}}" @endif>
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
                                       id="name" name="name" autofocus required
                                       placeholder="Enter first name" @if (\Session::has('updateUser')) value="{{Session::get("updateUser")->name}}" @endif>
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
                                       id="surname" name="surname" autofocus required
                                       placeholder="Enter last name" @if (\Session::has('updateUser')) value="{{Session::get("updateUser")->surname}}" @endif>
                                @error('surname')
                                <span class="invalid-feedback" role="alert">
                                   <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>
                        </div>
                        <div class="row mb-4">
                            <label for="role" class="col-form-label col-lg-2">Role</label>
                            <div class="col-lg-10">
                                <select id="role" name="role" class="form-control @error('role') is-invalid @enderror" required>
                                    <option value="">Select role</option>
                                    <option value="Admin" @if (\Session::has('updateUser') && Session::get('updateUser')->role->name == 'Admin') selected @endif>Admin</option>
                                    <option value="Doctor" @if (\Session::has('updateUser') && Session::get('updateUser')->role->name == 'Doctor') selected @endif>Doctor</option>
                                </select>
                                @error('role')
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
                                           class="form-control @error('newPassword') is-invalid @enderror"
                                           id="newPassword" placeholder="Enter new password"
                                           aria-label="Password" name="newPassword" autofocus aria-describedby="password-addon" @if (!(\Session::has('updateUser'))) required @endif>
                                    <button class="btn btn-light" type="button" id="password-addon"><i
                                            class="mdi mdi-eye-outline"></i></button></div>

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
                                           class="form-control @error('confirmPassword') is-invalid @enderror"
                                           id="confirmPassword" placeholder="Confirm password"
                                           aria-label="Password1" name="confirmPassword" autofocus aria-describedby="password-addon1" @if (!(\Session::has('updateUser'))) required @endif>
                                    <button class="btn btn-light" type="button" onclick="changeTypeBox()"><i
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
                                <button type="submit" class="btn btn-primary"> @if (\Session::has('updateUser')) Update @else Add @endif </button>
                            </div>
                        </div>
                    </form><br>
                    <h4 class="card-title mb-4">Manage Operators</h4>
                    <table class="table table-bordered yajra-datatable">
                        <thead>
                        <tr>
                            <th>Email</th>
                            <th>First Name</th>
                            <th>Last Name</th>
                            <th>Role</th>
                            <th>Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach(\App\Models\User::all() as $user)
                            @if((Auth::user()->id!=$user->id || $user->id !=1) && $user->role->name !="Patient" )
                                <tr>
                                    <td>{{$user->email}}</td>
                                    <td>{{$user->name}}</td>
                                    <td>{{$user->surname}}</td>
                                    <td>{{$user->role->name}}</td>


                                    <td><ul class="list-unstyled hstack gap-1 mb-0">
                                            <li data-bs-toggle="tooltip" data-bs-placement="top"
                                                title="Edit">
                                                <a href="{{route('searchUser', $user->id)}}"  class="btn btn-sm btn-soft-warning"><i class="mdi mdi-pencil-outline  font-size-15"></i></a>
                                            </li>
                                            <li data-bs-toggle="tooltip" data-bs-placement="top" title="Delete">
                                                <a href="#userDelete" id="{{$user->id}}" onclick="deleteUser(this.id)" data-bs-toggle="modal" class="btn btn-sm btn-soft-danger"><i class="mdi mdi-delete-outline font-size-15"></i></a>
                                            </li>

                                        </ul></td>
                                </tr>

                            @endif
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="userDelete" tabindex="-1" aria-labelledby="jobDeleteLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content">
                <div class="modal-body px-4 py-5 text-center">
                    <button type="button" class="btn-close position-absolute end-0 top-0 m-3" data-bs-dismiss="modal" aria-label="Close"></button>
                    <div class="avatar-sm mb-4 mx-auto">
                        <div class="avatar-title bg-warning text-warning bg-opacity-10 font-size-20 rounded-3">
                            <i class="mdi mdi-trash-can-outline"></i>
                        </div>
                    </div>
                    <p class="text-muted font-size-16 mb-4">Are you sure you want to delete this operator?</p>
                    <form action="{{route('deleteUser')}}" method="post"> @csrf
                        <input type="hidden" name="user" id="boxDelete">
                        <div class="hstack gap-2 justify-content-center mb-0">
                            <button type="submit" class="btn btn-danger">Delete</button>
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- end row -->
@endsection
@section('script')

    <script>
        function deleteUser(id){
            document.getElementById('boxDelete').value =id;
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




        $(document).ready(function () {
            $('[data-bs-toggle="tooltip"]').tooltip();
            $('.yajra-datatable').DataTable({
                order: [[0, "desc"]], // Ordina per la prima colonna (data)
                columnDefs: [
                    { orderable: false, targets: -1 } // Disabilita ordinamento sull'ultima colonna
                ]
            });
        });


    </script>

    <script src="https://cdn.datatables.net/1.13.1/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.1/js/dataTables.bootstrap5.min.js"></script>





@endsection
