@php use Illuminate\Support\Facades\Auth; @endphp
@extends('layouts.master')

@section('title')
    Patient Management
@endsection

@section('css')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css"/>
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.2.2/css/buttons.dataTables.min.css">

    <style>
        /* --- STILI GENERALI E CARD --- */
        .card {
            border: none;
            box-shadow: 0 0.75rem 1.5rem rgba(18, 38, 63, 0.03);
            border-radius: 12px;
        }


        /* --- NUOVO STILE BOTTONE ADD PATIENT --- */
        /* Blu solido, testo bianco, pulito */
        .btn-add-patient {
            background-color: #556ee6 !important; /* Punto e virgola spostato alla fine */
            border-color: #556ee6 !important;     /* Punto e virgola spostato alla fine */
            color: #ffffff !important;
            font-weight: 500;
            box-shadow: 0 2px 6px 0 rgba(85, 110, 230, 0.5);
            transition: all 0.3s;
        }

        .btn-add-patient:hover {
            background-color: #485ec4 !important; /* Aggiunto !important anche qui per sicurezza */
            border-color: #485ec4 !important;
            color: #ffffff !important;
            transform: translateY(-1px);
        }

        /* --- MODALI --- */
        .modal-content {
            border: none;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }

        .modal-header {
            border-bottom: 1px solid #eff2f7;
            background-color: #f8f9fa;
            border-radius: 12px 12px 0 0;
            padding: 1.5rem;
        }

        .modal-footer {
            border-top: 1px solid #eff2f7;
            padding: 1.5rem;
        }

        .form-label {
            font-weight: 500;
            color: #495057;
        }

        .form-control, .form-select {
            border-radius: 6px;
            border: 1px solid #ced4da;
            padding: 0.6rem 0.9rem;
        }

        .form-control:focus, .form-select:focus {
            border-color: #556ee6;
            box-shadow: 0 0 0 0.15rem rgba(85, 110, 230, 0.1);
        }
    </style>
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

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">

                    @error("error")
                    <div class="alert alert-danger border-0 shadow-sm" role="alert">
                        <i class="mdi mdi-block-helper me-2"></i> {{$message}}
                    </div>
                    @enderror
                    @if (\Session::has('success'))
                        <div class="alert alert-success border-0 shadow-sm" role="alert">
                            <i class="mdi mdi-check-circle-outline me-2"></i> {{Session::get('success')}}
                        </div>
                    @endif

                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div>
                            <h4 class="card-title mb-1">Patient List</h4>
                            <p class="text-muted mb-0">Manage your patients, view details and edit information.</p>
                        </div>
                        <button type="button" class="btn btn-add-patient rounded-pill px-4 py-2"
                                data-bs-toggle="modal" data-bs-target=".bs-add-patient-modal-xl">
                            <i class="mdi mdi-plus me-1"></i> Add Patient
                        </button>
                    </div>

                    <div class="table-responsive">
                        <table
                            class="table table-hover table-bordered dt-responsive nowrap w-100 yajra-datatable align-middle"
                            id="csvTable">
                            <thead class="bg-light text-uppercase table-light">
                            <tr>
                                <th>Full Name</th>
                                <th>Email</th>
                                <th>Address</th>
                                <th>Actions</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach(\App\Models\Patient::all() as $patient)
                                @php
                                    // Logica di visualizzazione basata sul ruolo
                                    $showRow = false;
                                    if(auth()->user()->role->name == "Doctor") {
                                        if($patient->doctor_id == auth()->user()->id) $showRow = true;
                                    } else {
                                        $showRow = true;
                                    }
                                @endphp

                                @if($showRow)
                                    <tr>
                                        <td>
                                            <div>
                                                <h5 class="text-dark font-size-14 mb-0">{{ $patient->name }} {{ $patient->surname }}</h5>
                                                @if($patient->telephone_number)
                                                    <small class="text-muted"><i
                                                            class="mdi mdi-phone me-1"></i>{{ $patient->telephone_number }}
                                                    </small>
                                                @endif
                                            </div>
                                        </td>
                                        <td>{{ $patient->email }}</td>
                                        <td>
                                            <i class="mdi mdi-map-marker-outline text-muted me-1"></i>
                                            {{ \Illuminate\Support\Str::limit($patient->address, 30) }}
                                        </td>
                                        <td class="text-end">
                                            <div class="d-flex align-items-center gap-2">

                                                <a href="{{ route('showCsvPatient', $patient->id) }}" target="_blank"
                                                   class="btn btn-soft-primary action-btn" data-bs-toggle="tooltip"
                                                   title="View Details">
                                                    <i class="mdi mdi-eye-outline font-size-15"></i>
                                                </a>

                                                @if (\App\Models\User::where('patient_id', $patient->id)->exists())
                                                    <button class="btn btn-soft-success action-btn"
                                                          >
                                                        <i class="mdi mdi-check-circle-outline font-size-15"   data-bs-toggle="tooltip" title="Registration Completed"></i>
                                                    </button>
                                                @else
                                                    <a href="{{ route('sendRegistration', ['patientId' => $patient->id]) }}"
                                                       class="btn btn-soft-info action-btn"
                                                       data-bs-toggle="tooltip" title="Send Invitation">
                                                        <i class="mdi mdi-email-send-outline font-size-15"></i>
                                                    </a>
                                                @endif

                                                <button type="button" class="btn btn-soft-warning action-btn"
                                                        data-bs-toggle="modal" data-bs-target="#editPatientModal"
                                                        data-id="{{ $patient->id }}"
                                                        data-name="{{ $patient->name }}"
                                                        data-surname="{{ $patient->surname }}"
                                                        data-email="{{ $patient->email }}"
                                                        data-telephone="{{ $patient->telephone_number }}"
                                                        data-address="{{ $patient->address }}"
                                                        data-birth="{{ $patient->birth }}"
                                                        data-gender="{{ $patient->gender }}"
                                                        data-doctor="{{ $patient->doctor_id }}"
                                                        title="Edit">
                                                    <i class="mdi mdi-pencil-outline font-size-15"   data-bs-toggle="tooltip" title="Edit"></i>
                                                </button>

                                                <a href="#patientDelete" id="{{ $patient->id }}"
                                                   onclick="deletePatient(this.id)"
                                                   data-bs-toggle="modal" class="btn btn-soft-danger action-btn"
                                                   title="">
                                                    <i class="mdi mdi-trash-can-outline font-size-15"   data-bs-toggle="tooltip" title="Delete"></i>
                                                </a>
                                            </div>
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

    <div class="modal fade bs-add-patient-modal-xl" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-light">
                    <h5 class="modal-title text-primary"><i class="mdi mdi-account-plus-outline me-2"></i>Add New
                        Patient</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <form method="post" action="{{ route('addPatient') }}" enctype="multipart/form-data">
                        @csrf

                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label">First Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror"
                                       name="name" placeholder="Ex: John" value="{{ old('name') }}" required>
                                @error('name')
                                <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Last Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('surname') is-invalid @enderror"
                                       name="surname" placeholder="Ex: Doe" value="{{ old('surname') }}" required>
                                @error('surname')
                                <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Email Address <span class="text-danger">*</span></label>
                                <input type="email" class="form-control @error('email') is-invalid @enderror"
                                       name="email" placeholder="john.doe@example.com" value="{{ old('email') }}"
                                       required>
                                @error('email')
                                <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Phone Number</label>
                                <input type="text" class="form-control @error('telephone_number') is-invalid @enderror"
                                       name="telephone_number" placeholder="+1 234 567 890"
                                       value="{{ old('telephone_number') }}">
                            </div>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Date of Birth <span class="text-danger">*</span></label>
                                <input type="date" class="form-control @error('birth') is-invalid @enderror"
                                       name="birth" value="{{ old('birth') }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Gender <span class="text-danger">*</span></label>
                                <select class="form-select @error('gender') is-invalid @enderror" name="gender"
                                        required>
                                    <option value="Male" @if(old('gender') == 'Male') selected @endif>Male</option>
                                    <option value="Female" @if(old('gender') == 'Female') selected @endif>Female
                                    </option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Full Address <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('address') is-invalid @enderror"
                                   name="address" placeholder="Street, City, Zip Code" value="{{ old('address') }}"
                                   required>
                        </div>

                        @php $user = Auth::user(); @endphp
                        @if($user->role_id == 1)
                            <div class="mb-3">
                                <label class="form-label">Assign Doctor <span class="text-danger">*</span></label>
                                <select class="form-select @error('doctor') is-invalid @enderror" name="doctor">
                                    @php $doctors = \App\Models\User::where("role_id", "3")->get(); @endphp
                                    <option value="" disabled selected>Select a doctor</option>
                                    @foreach($doctors as $doctor)
                                        <option value="{{ $doctor->id }}"
                                                @if(old('doctor') == $doctor->id) selected @endif>{{ $doctor->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @else
                            <input type="hidden" name="doctor" value="{{ $user->id }}">
                        @endif

                        <div class="modal-footer pb-0 px-0 mt-4 border-0">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary px-4">Save Patient</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="editPatientModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-light">
                    <h5 class="modal-title text-warning"><i class="mdi mdi-pencil-box-outline me-2"></i>Edit Patient
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <form id="editPatientForm" method="POST" action="{{ route('updatePatient') }}">
                        @csrf
                        <input type="hidden" id="patient_id" name="patient_id">

                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label">First Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="edit-name" name="name" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Last Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="edit-surname" name="surname" required>
                            </div>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Email <span class="text-danger">*</span></label>
                                <input type="email" class="form-control" id="edit-email" name="email" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Phone</label>
                                <input type="text" class="form-control" id="edit-telephone_number"
                                       name="telephone_number">
                            </div>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Date of Birth <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="edit-birth" name="birth" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Gender <span class="text-danger">*</span></label>
                                <select class="form-select" id="edit-gender" name="gender" required>
                                    <option value="Male">Male</option>
                                    <option value="Female">Female</option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Address <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="edit-address" name="address" required>
                        </div>

                        @if($user->role_id == 1)
                            <div class="mb-3">
                                <label class="form-label">Assigned Doctor</label>
                                <select class="form-select" name="doctor" id="edit-doctor">
                                    @foreach($doctors as $doctor)
                                        <option value="{{ $doctor->id }}">{{ $doctor->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @else
                            <input type="hidden" name="doctor" value="{{ $user->id }}">
                        @endif

                        <div class="modal-footer pb-0 px-0 mt-4 border-0">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-warning text-white px-4">Update Changes</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="patientDelete" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-body px-4 py-5 text-center">
                    <button type="button" class="btn-close position-absolute end-0 top-0 m-3"
                            data-bs-dismiss="modal"></button>
                    <div class="avatar-sm mb-4 mx-auto">
                        <div class="avatar-title bg-danger bg-opacity-10 text-danger font-size-24 rounded-circle">
                            <i class="mdi mdi-trash-can-outline"></i>
                        </div>
                    </div>
                    <h5 class="mb-3">Confirm Delete?</h5>
                    <p class="text-muted mb-4">Are you sure you want to delete this patient? This action cannot be
                        undone.</p>

                    <form action="{{route('deletePatient')}}" method="post">
                        @csrf
                        <input type="hidden" name="patient" id="boxDelete">
                        <div class="d-flex gap-2 justify-content-center">
                            <button type="button" class="btn btn-light w-50" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-danger w-50 shadow-sm">Delete</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

@endsection

@section('script')
    <script src="https://cdn.datatables.net/1.13.1/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.1/js/dataTables.bootstrap5.min.js"></script>

    <script>
        function deletePatient(id) {
            document.getElementById('boxDelete').value = id;
        }

        $('#editPatientModal').on('show.bs.modal', function (event) {
            var button = $(event.relatedTarget);

            // Popolamento campi
            $('#patient_id').val(button.data('id'));
            $('#edit-name').val(button.data('name'));
            $('#edit-surname').val(button.data('surname'));
            $('#edit-email').val(button.data('email'));
            $('#edit-birth').val(button.data('birth'));
            $('#edit-gender').val(button.data('gender'));
            $('#edit-address').val(button.data('address'));
            $('#edit-telephone_number').val(button.data('telephone'));
            $('#edit-doctor').val(button.data('doctor'));
        });

        $(document).ready(function () {
            // Inizializza Tooltips
            $('[data-bs-toggle="tooltip"]').tooltip();


            // DataTable Config
            var table = $('#csvTable').DataTable({
                order: [[0, "desc"]], // Ordina per Start Date
                columnDefs: [{orderable: false, targets: -1}],
                language: {search: "", searchPlaceholder: "Search files..."},
                dom: 'rtip' // Nascondiamo la barra di ricerca default, la gestiamo noi o lasciamo pulito
            });

        });
    </script>
@endsection
