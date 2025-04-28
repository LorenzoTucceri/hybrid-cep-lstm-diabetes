@extends('layouts.master')

@section('title')
    Patient Management
@endsection

@section('css')
    <!--datatable css-->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" />
    <!-- DataTables Buttons CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.2.2/css/buttons.dataTables.min.css" />

@endsection

@section('content')

    @component('components.breadcrumb')
        @slot('li_1')
            Patients
        @endslot
        @slot('title')
            Patient Management
        @endslot
    @endcomponent

    <style>
        .modal-content {
            border-radius: 8px; /* Aggiungi angoli arrotondati */
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); /* Aggiungi un'ombra leggera */
        }

        .modal-header {
            background-color: #f8f9fa;
            border-bottom: 1px solid #e0e0e0;
        }

        .modal-footer {
            display: flex;
            justify-content: flex-end;
        }

        .form-control {
            border-radius: 4px; /* Bordo più arrotondato per gli input */
        }
    </style>

    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-body">
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

                    <h4 class="card-title mb-4 d-flex justify-content-between">
                        Patient List
                        <button type="button" class="btn btn-primary waves-effect waves-light" data-bs-toggle="modal"
                                data-bs-target=".bs-add-patient-modal-xl">
                            Add Patient
                        </button>
                    </h4>


                    <table class="table table-bordered yajra-datatable">
                        <thead>
                        <tr>
                            <th>Full Name</th>
                            <th>Email</th>
                            <th>Address</th>
                            <th>Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach(\App\Models\Patient::all() as $patient)
                            @if(auth()->user()->role->name=="Doctor")
                                @if($patient->doctor_id==auth()->user()->id)
                                    <tr>
                                        <td>{{ $patient->name }} {{ $patient->surname }}</td> <!-- Nome completo -->
                                        <td>{{ $patient->email }}</td> <!-- Email -->
                                        <td>{{ $patient->address }}</td> <!-- Indirizzo -->
                                        <td>
                                            <ul class="list-unstyled hstack gap-1 mb-0">
                                                <li data-bs-toggle="tooltip" data-bs-placement="top" title="View">
                                                    <a href="{{ route('showCsvPatient', $patient->id) }}"
                                                       target="_blank" class="btn btn-sm btn-soft-primary">
                                                        <i class="mdi mdi-eye-outline font-size-15"></i>
                                                    </a>
                                                </li>
                                                <li data-bs-toggle="tooltip" data-bs-placement="top"
                                                    title="{{ \App\Models\User::where('patient_id', $patient->id)->exists() ? 'Registration completed' : 'Invite' }}">

                                                    @if (\App\Models\User::where('patient_id', $patient->id)->exists())
                                                        <!-- If the patient already has a user account -->
                                                        <button class="btn btn-sm btn-soft-success">
                                                            <i class="mdi mdi-check-circle font-size-15"></i>
                                                        </button>
                                                    @else
                                                        <!-- If the patient does not have a user account, show the button to send an invite -->
                                                        <a href="{{ route('sendRegistration', ['patientId' => $patient->id]) }}"
                                                           class="btn btn-sm btn-soft-info">
                                                            <i class="mdi mdi-email-send font-size-15"></i>
                                                        </a>
                                                    @endif
                                                </li>
                                                <li data-bs-toggle="tooltip" data-bs-placement="top" title="Edit">
                                                    <a data-bs-toggle="modal" class="btn btn-sm btn-soft-warning"
                                                       data-bs-target="#editPatientModal" data-id="{{ $patient->id }}"
                                                       data-name="{{ $patient->name }}"
                                                       data-surname="{{ $patient->surname }}"
                                                       data-email="{{ $patient->email }}"
                                                       data-telephone="{{ $patient->telephone_number }}"
                                                       data-address="{{ $patient->address }}"
                                                       data-birth="{{ $patient->birth }}"
                                                       data-gender="{{ $patient->gender }}"
                                                       data-doctor="{{ $patient->doctor_id }}">
                                                        <i class="mdi mdi-pencil-outline font-size-15"></i>
                                                    </a>
                                                </li>
                                                <li data-bs-toggle="tooltip" data-bs-placement="top" title="Delete">
                                                    <a href="#patientDelete" id="{{ $patient->id }}"
                                                       onclick="deletePatient(this.id)" data-bs-toggle="modal"
                                                       class="btn btn-sm btn-soft-danger">
                                                        <i class="mdi mdi-delete-outline font-size-15"></i>
                                                    </a>
                                                </li>
                                            </ul>
                                        </td>
                                    </tr>
                                @endif
                            @else
                                <tr>
                                    <td>{{ $patient->name }} {{ $patient->surname }}</td> <!-- Nome completo -->
                                    <td>{{ $patient->email }}</td> <!-- Email -->
                                    <td>{{ $patient->address }}</td> <!-- Indirizzo -->
                                    <td>
                                        <ul class="list-unstyled hstack gap-1 mb-0">
                                            <li data-bs-toggle="tooltip" data-bs-placement="top" title="View">
                                                <a href="{{ route('showCsvPatient', $patient->id) }}"
                                                   target="_blank" class="btn btn-sm btn-soft-primary">
                                                    <i class="mdi mdi-eye-outline font-size-15"></i>
                                                </a>
                                            </li>
                                            <li data-bs-toggle="tooltip" data-bs-placement="top"
                                                title="{{ \App\Models\User::where('patient_id', $patient->id)->exists() ? 'Registration completed' : 'Invite' }}">

                                                @if (\App\Models\User::where('patient_id', $patient->id)->exists())
                                                    <!-- If the patient already has a user account -->
                                                    <button class="btn btn-sm btn-soft-success">
                                                        <i class="mdi mdi-check-circle font-size-15"></i>
                                                    </button>
                                                @else
                                                    <!-- If the patient does not have a user account, show the button to send an invite -->
                                                    <a href="{{ route('sendRegistration', ['patientId' => $patient->id]) }}"
                                                       class="btn btn-sm btn-soft-info">
                                                        <i class="mdi mdi-email-send font-size-15"></i>
                                                    </a>
                                                @endif
                                            </li>                                            <li data-bs-toggle="tooltip" data-bs-placement="top" title="Edit">
                                                <a data-bs-toggle="modal" class="btn btn-sm btn-soft-warning"
                                                   data-bs-target="#editPatientModal" data-id="{{ $patient->id }}"
                                                   data-name="{{ $patient->name }}"
                                                   data-surname="{{ $patient->surname }}"
                                                   data-email="{{ $patient->email }}"
                                                   data-telephone="{{ $patient->telephone_number }}"
                                                   data-address="{{ $patient->address }}"
                                                   data-birth="{{ $patient->birth }}"
                                                   data-gender="{{ $patient->gender }}"
                                                   data-doctor="{{ $patient->doctor_id }}">
                                                    <i class="mdi mdi-pencil-outline font-size-15"></i>
                                                </a>
                                            </li>
                                            <li data-bs-toggle="tooltip" data-bs-placement="top" title="Delete">
                                                <a href="#patientDelete" id="{{ $patient->id }}"
                                                   onclick="deletePatient(this.id)" data-bs-toggle="modal"
                                                   class="btn btn-sm btn-soft-danger">
                                                    <i class="mdi mdi-delete-outline font-size-15"></i>
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

    <!-- Modal -->
    <div class="modal fade bs-add-patient-modal-xl" tabindex="-1" role="dialog"
         aria-labelledby="addPatientModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg"> <!-- Cambiato modal-md a modal-lg per maggiore larghezza -->
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addClientModalLabel">Add Patient</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    @error("error")
                    <div class="alert alert-danger" role="alert">
                        {{$message}}
                    </div>
                    @enderror

                    <form method="post" action="{{ route('addPatient') }}" enctype="multipart/form-data">
                        @csrf

                        <!-- Nome -->
                        <div class="row mb-4">
                            <label for="name" class="col-form-label col-lg-3">*Name<span
                                    class="text-danger">*</span></label>
                            <div class="col-lg-9">
                                <input type="text"
                                       class="form-control @error('name') is-invalid @enderror"
                                       id="name" name="name" placeholder="Enter name"
                                       value="{{ old('name') }}" required>
                                @error('name')
                                <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                                @enderror
                            </div>
                        </div>

                        <!-- Cognome -->
                        <div class="row mb-4">
                            <label for="surname" class="col-form-label col-lg-3">*Surname<span
                                    class="text-danger">*</span></label>
                            <div class="col-lg-9">
                                <input type="text"
                                       class="form-control @error('surname') is-invalid @enderror"
                                       id="surname" name="surname" placeholder="Enter surname"
                                       value="{{ old('surname') }}" required>
                                @error('surname')
                                <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                                @enderror
                            </div>
                        </div>

                        <!-- Email -->
                        <div class="row mb-4">
                            <label for="email" class="col-form-label col-lg-3">Email<span
                                    class="text-danger">*</span></label>
                            <div class="col-lg-9">
                                <input type="email"
                                       class="form-control @error('email') is-invalid @enderror"
                                       id="email" name="email" placeholder="Enter l'email"
                                       value="{{ old('email') }}" required>
                                @error('email')
                                <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                                @enderror
                            </div>
                        </div>

                        <!-- Doctor -->
                        <div class="row mb-4">
                            <label for="doctor" class="col-form-label col-lg-3">Doctor<span
                                    class="text-danger">*</span></label>
                            <div class="col-lg-9">
                                <select class="form-select @error('doctor') is-invalid @enderror"
                                        name="doctor" id="doctor">
                                    @php
                                        $doctors = \App\Models\User::where("role_id", "3")->get();
                                    @endphp

                                    @if($doctors->isEmpty())
                                        <option value="" disabled selected> No doctors available
                                        </option>
                                    @else
                                        <option value="">Select a doctor</option>
                                        @foreach($doctors as $doctor)
                                            <option value="{{ $doctor->id }}"
                                                    @if(old('doctor') == $doctor->id) selected @endif>
                                                {{ $doctor->name }}
                                            </option>
                                        @endforeach
                                    @endif
                                </select>
                                @error('doctor')
                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                @enderror
                            </div>
                        </div>

                        <!-- Date of Birth -->
                        <div class="row mb-4">
                            <label for="birth" class="col-form-label col-lg-3">Date of birth<span
                                    class="text-danger">*</span></label>
                            <div class="col-lg-9">
                                <input type="date"
                                       class="form-control @error('birth') is-invalid @enderror"
                                       id="birth" name="birth"
                                       value="{{ old('birth') }}" required>
                                @error('birth')
                                <span class="invalid-feedback" role="alert">
                                                            <strong>{{ $message }}</strong>
                                                        </span>
                                @enderror
                            </div>
                        </div>

                        <!-- Gender -->
                        <div class="row mb-4">
                            <label for="gender" class="col-form-label col-lg-3">Gender<span
                                    class="text-danger">*</span></label>
                            <div class="col-lg-9">
                                <select class="form-select @error('gender') is-invalid @enderror"
                                        name="gender" id="gender" required>
                                    <option value="Male"
                                            @if(old('gender') == 'Male') selected @endif>Male
                                    </option>
                                    <option value="Female"
                                            @if(old('gender') == 'Female') selected @endif>Female
                                    </option>
                                </select>
                                @error('gender')
                                <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                                @enderror
                            </div>
                        </div>

                        <!-- Address -->
                        <div class="row mb-4">
                            <label for="address" class="col-form-label col-lg-3">Address<span
                                    class="text-danger">*</span></label>
                            <div class="col-lg-9">
                                <input type="text"
                                       class="form-control @error('address') is-invalid @enderror"
                                       id="address" name="address"
                                       placeholder="Enter address"
                                       value="{{ old('address') }}" required>
                                @error('address')
                                <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                                @enderror
                            </div>
                        </div>

                        <!-- Phone Number -->
                        <div class="row mb-4">
                            <label for="telephone_number" class="col-form-label col-lg-3">Phone number</label>
                            <div class="col-lg-9">
                                <input type="text"
                                       class="form-control @error('telephone_number') is-invalid @enderror"
                                       id="telephone_number" name="telephone_number"
                                       placeholder="Enter phone number"
                                       value="{{ old('telephone_number') }}">
                                @error('telephone_number')
                                <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                                @enderror
                            </div>
                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                Chiudi
                            </button>
                            <button type="submit" id="submitButton"
                                    class="btn btn-primary waves-effect waves-light">Aggiungi Cliente
                            </button>
                        </div>
                    </form>
                </div>
            </div><!-- /.modal-content -->
        </div><!-- /.modal-dialog -->
    </div>

    <!-- Modal per Editare Paziente -->
    <div class="modal fade bs-add-patient-modal-xl" id="editPatientModal" tabindex="-1"
         aria-labelledby="editPatientModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg"> <!-- Cambiato modal-md a modal-lg per maggiore larghezza -->
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editPatientModalLabel">Modifica Paziente</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="editPatientForm" method="POST" action="{{ route('updatePatient') }}">
                        @csrf

                        <input type="hidden" id="patient_id" name="id">

                        <!-- Nome -->
                        <div class="row mb-4">
                            <label for="edit-name" class="col-form-label col-lg-3">*Name<span
                                    class="text-danger">*</span></label>
                            <div class="col-lg-9">
                                <input type="text" class="form-control @error('name') is-invalid @enderror"
                                       id="edit-name" name="name" placeholder="Enter name" required>
                                @error('name')
                                <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                                @enderror
                            </div>
                        </div>

                        <!-- Cognome -->
                        <div class="row mb-4">
                            <label for="edit-surname" class="col-form-label col-lg-3">*Surname<span class="text-danger">*</span></label>
                            <div class="col-lg-9">
                                <input type="text" class="form-control @error('surname') is-invalid @enderror"
                                       id="edit-surname" name="surname" placeholder="Enter surname" required>
                                @error('surname')
                                <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                                @enderror
                            </div>
                        </div>

                        <!-- Email -->
                        <div class="row mb-4">
                            <label for="edit-email" class="col-form-label col-lg-3">Email<span
                                    class="text-danger">*</span></label>
                            <div class="col-lg-9">
                                <input type="email" class="form-control @error('email') is-invalid @enderror"
                                       id="edit-email" name="email" placeholder="Enter email" required>
                                @error('email')
                                <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-4">
                            <label for="edit-doctor" class="col-form-label col-lg-3">Doctor<span
                                    class="text-danger">*</span></label>
                            <div class="col-lg-9">
                                <select class="form-select @error('doctor') is-invalid @enderror" name="doctor"
                                        id="edit-doctor" required>
                                    @foreach($doctors as $doctor)
                                        <option value="{{ $doctor->id }}">{{ $doctor->name }}</option>
                                    @endforeach
                                </select>
                                @error('doctor')
                                <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                                @enderror
                            </div>
                        </div>


                        <!-- Data di Nascita -->
                        <div class="row mb-4">
                            <label for="edit-birth" class="col-form-label col-lg-3">Date of birth<span
                                    class="text-danger">*</span></label>
                            <div class="col-lg-9">
                                <input type="date" class="form-control @error('birth') is-invalid @enderror"
                                       id="edit-birth" name="birth" required>
                                @error('birth')
                                <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                                @enderror
                            </div>
                        </div>

                        <!-- Sesso -->
                        <div class="row mb-4">
                            <label for="edit-gender" class="col-form-label col-lg-3">Gender<span
                                    class="text-danger">*</span></label>
                            <div class="col-lg-9">
                                <select class="form-select @error('gender') is-invalid @enderror" name="gender"
                                        id="edit-gender" required>
                                    <option value="Male">Male</option>
                                    <option value="Female">Female</option>
                                </select>
                                @error('gender')
                                <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                                @enderror
                            </div>
                        </div>

                        <!-- Indirizzo -->
                        <div class="row mb-4">
                            <label for="edit-address" class="col-form-label col-lg-3">Address<span
                                    class="text-danger">*</span></label>
                            <div class="col-lg-9">
                                <input type="text" class="form-control @error('address') is-invalid @enderror"
                                       id="edit-address" name="address" placeholder="Enter address" required>
                                @error('address')
                                <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                                @enderror
                            </div>
                        </div>

                        <!-- Numero di telefono -->
                        <div class="row mb-4">
                            <label for="edit-telephone_number" class="col-form-label col-lg-3">Phone number</label>
                            <div class="col-lg-9">
                                <input type="text" class="form-control @error('telephone_number') is-invalid @enderror"
                                       id="edit-telephone_number" name="telephone_number"
                                       placeholder="Enter phone number">
                                @error('telephone_number')
                                <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                                @enderror
                            </div>
                        </div>

                        <!-- Medico -->

                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-primary">Update Patient</button>
                        </div>
                    </form>
                </div><!-- /.modal-content -->
            </div><!-- /.modal-dialog -->
        </div><!-- /.modal -->
    </div>

    <div class="modal fade" id="patientDelete" tabindex="-1" aria-labelledby="jobDeleteLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content">
                <div class="modal-body px-4 py-5 text-center">
                    <button type="button" class="btn-close position-absolute end-0 top-0 m-3" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                    <div class="avatar-sm mb-4 mx-auto">
                        <div class="avatar-title bg-warning text-warning bg-opacity-10 font-size-20 rounded-3">
                            <i class="mdi mdi-trash-can-outline"></i>
                        </div>
                    </div>
                    <p class="text-muted font-size-16 mb-4">Are you sure you want to delete the patient?</p>
                    <form action="{{route('deletePatient')}}" method="post"> @csrf
                        <input type="hidden" name="patient" id="boxDelete">
                        <button type="submit" class="btn btn-danger">Delete</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

@endsection

@section('script')

    <script>

        function deletePatient(id) {
            document.getElementById('boxDelete').value = id;
        }

        $('#editPatientModal').on('show.bs.modal', function (event) {
            var button = $(event.relatedTarget); // Bottone che ha attivato il modale
            var patientId = button.data('id'); // ID del paziente
            var name = button.data('name');
            var surname = button.data('surname');
            var email = button.data('email');
            var birth = button.data('birth');
            var gender = button.data('gender');
            var address = button.data('address');
            var telephone = button.data('telephone');
            var doctor = button.data('doctor');


            // Popoliamo i campi del modale
            $('#patient_id').val(patientId);
            $('#edit-name').val(name);
            $('#edit-surname').val(surname);
            $('#edit-email').val(email);
            $('#edit-birth').val(birth);
            $('#edit-gender').val(gender);
            $('#edit-address').val(address);
            $('#edit-telephone_number').val(telephone);
            $('#edit-doctor').val(doctor);
        });



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

    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.13.1/js/jquery.dataTables.min.js"></script>

    <!-- DataTables Bootstrap 5 integration JS -->
    <script src="https://cdn.datatables.net/1.13.1/js/dataTables.bootstrap5.min.js"></script>

@endsection
