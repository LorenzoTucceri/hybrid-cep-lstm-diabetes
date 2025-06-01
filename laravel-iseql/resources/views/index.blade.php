@extends('layouts.master')

@section('title')
    Dashboard
@endsection

@section("css")
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css"/>
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.2.2/css/buttons.dataTables.min.css">
@endsection

@section('content')

    @component('components.breadcrumb')
        @slot('li_1')
            Dashboard
        @endslot
        @slot('title')
            Dashboard
        @endslot
    @endcomponent

    <div class="row">
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
        <div class="col-xl-4">
            <div class="card overflow-hidden">
                <div class="bg-primary bg-soft">
                    <div class="row">
                        <div class="col-7">
                            <div class="text-primary p-3">
                                <h5 class="text-primary">Welcome Back!</h5>
                                <!-- <p>Dashboard Name</p> -->
                            </div>
                        </div>
                        <div class="col-5 align-self-end">
                            <img src="{{ URL::asset('/assets/images/profile-img.png') }}" alt="" class="img-fluid">
                        </div>
                    </div>
                </div>
                <div class="card-body pt-0">
                    <div class="row">
                        <div class="col-sm-4">
                            <div class="avatar-md profile-user-wid mb-4">
                                <img src="/images/avatar-default.jpeg" alt="" class="img-thumbnail rounded-circle">
                            </div>
                            <h5 class="font-size-15">{{ Str::ucfirst(Auth::user()->name)." ".Str::ucfirst(Auth::user()->surname)}}</h5>
                            <p class="text-muted mb-0 text-truncate">{{ Str::ucfirst(Auth::user()->role->name) }}</p>
                        </div>

                        <div class="col-sm-8">
                            <div class="pt-4">
                                <div class="row">
                                    <div class="col-md-12">
                                        <h5 class="font-size-12">{{ Str::ucfirst(Auth::user()->email)}}</h5>
                                        <p class="text-muted mb-0">Email</p>
                                    </div>
                                </div>
                            </div>
                            <div class="mt-4">
                                <a href="{{Route('userProfile')}}"
                                   class="btn btn-primary waves-effect waves-light btn-sm">View Profile <i
                                        class="mdi mdi-arrow-right ms-1"></i></a>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title mb-4">Project Overview</h4>
                    <p>This project focuses on the analysis of glucose data to model various glucose conditions, using a
                        multi-phase process for event detection and pattern recognition. The goal is to improve diabetes
                        management and support personalized treatment strategies.</p>
                    <div class="row">
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-8">
            @if(auth()->user()->role->name=="Admin")
                <div class="row">
                    <div class="col-md-4">
                        <div class="card mini-stats-wid">
                            <div class="card-body">
                                <div class="d-flex">
                                    <div class="flex-grow-1">
                                        <p class="text-muted fw-medium">Doctors</p>
                                        <h4 class="mb-0">{{\App\Models\User::where("role_id","3")->count()}}</h4>
                                    </div>

                                    <div class="flex-shrink-0 align-self-center">
                                        <div class="mini-stat-icon avatar-sm rounded-circle bg-primary">
                                <span class="avatar-title rounded-circle bg-primary">
                                    <i class="fas fa-user-md font-size-24"></i>
                                </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="card mini-stats-wid">
                            <div class="card-body">
                                <div class="d-flex">
                                    <div class="flex-grow-1">
                                        <p class="text-muted fw-medium">Patients</p>
                                        <h4 class="mb-0">{{\App\Models\Patient::count()}}</h4>

                                    </div>

                                    <div class="flex-shrink-0 align-self-center">
                                        <div class="avatar-sm rounded-circle bg-primary mini-stat-icon">
                                    <span class="avatar-title rounded-circle bg-primary">
                                         <i class="bx bxs-user-detail font-size-24"></i>
                                    </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card mini-stats-wid">
                            <div class="card-body">
                                <div class="d-flex">
                                    <div class="flex-grow-1">
                                        <p class="text-muted fw-medium">CSV Files</p>
                                        <h4 class="mb-0">{{\App\Models\File::count()}}</h4>
                                    </div>

                                    <div class="flex-shrink-0 align-self-center">
                                        <div class="avatar-sm rounded-circle bg-primary mini-stat-icon">
                                    <span class="avatar-title rounded-circle bg-primary">
                                            <i class="bx bx-copy-alt font-size-24"></i>
                                    </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @else
                <div class="row">
                    @if(auth()->user()->role->name!="Patient")

                        <div class="col-md-6">
                            <div class="card mini-stats-wid">
                                <div class="card-body">
                                    <div class="d-flex">
                                        <div class="flex-grow-1">
                                            <p class="text-muted fw-medium">Patients</p>
                                            <h4 class="mb-0">{{ \App\Models\Patient::where('doctor_id', Auth::user()->id)->count() }}</h4>
                                        </div>


                                        <div class="flex-shrink-0 align-self-center">
                                            <div class="avatar-sm rounded-circle bg-primary mini-stat-icon">
                                    <span class="avatar-title rounded-circle bg-primary">
                                         <i class="bx bxs-user-detail font-size-24"></i>
                                    </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="col-md-6">
                            <div class="card mini-stats-wid">
                                <div class="card-body">
                                    <div class="d-flex">
                                        <div class="flex-grow-1">
                                            <p class="text-muted fw-medium">Feedback</p>
                                            <h4 class="mb-0">
                                                {{ \App\Models\Feedback::whereHas('file', function($query) {
                                                    $query->where('patient_id', auth()->user()->patient_id);  // Assumendo che il paziente autenticato abbia un patient_id
                                                })->count() }}
                                            </h4>
                                        </div>

                                        <div class="flex-shrink-0 align-self-center">
                                            <div class="avatar-sm rounded-circle bg-primary mini-stat-icon">
                        <span class="avatar-title rounded-circle bg-primary">
                            <i class="bx bxs-message-rounded font-size-24"></i> <!-- Icona dei feedback -->
                        </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    @endif


                    <div class="col-md-6">

                        <div class="card mini-stats-wid">
                            <div class="card-body">
                                <div class="d-flex">
                                    <div class="flex-grow-1">
                                        <p class="text-muted fw-medium">CSV Files</p>
                                        <h4 class="mb-0">{{\App\Models\File::count()}}</h4>
                                    </div>

                                    <div class="flex-shrink-0 align-self-center">
                                        <div class="avatar-sm rounded-circle bg-primary mini-stat-icon">
                                    <span class="avatar-title rounded-circle bg-primary">
                                            <i class="bx bx-copy-alt font-size-24"></i>
                                    </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
            <!-- end row -->

            <div class="card">
                <div class="card-body">
                    @if(auth()->user()->role->name=="Patient")
                        @php
                            $files = \App\Models\File::where("patient_id", auth()->user()->patient_id)->get();
                        @endphp
                        <h4 class="card-title mb-4">Latest Added Csv File</h4>
                        <div class="table-responsive">
                            <table class="table table-bordered yajra-datatable-patient">
                                <thead>
                                <tr>
                                    <th>File</th>
                                    <th>Start time</th>
                                    <th>End time</th>
                                    <th>Actions</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($files as $file)
                                    <tr>
                                        <td>{{ $file->csv_file_path }}</td>
                                        <td>{{ $file->start_time }}</td>
                                        <td>{{ $file->end_time }}</td>
                                        <td>
                                            <ul class="list-unstyled hstack gap-1 mb-0">
                                                <li data-bs-toggle="tooltip" data-bs-placement="top" title="View">
                                                    <a href="{{ route('viewCsv', ['csvId' => $file->id, 'patientId' => auth()->user()->patient_id]) }}"
                                                       target="_blank" class="btn btn-sm btn-soft-primary">
                                                        <i class="mdi mdi-eye-outline font-size-15"></i>
                                                    </a>
                                                </li>

                                                <li data-bs-toggle="tooltip" data-bs-placement="top" title="Feedback">
                                                    <a href="#feedbackModal"
                                                       onclick="openFeedbackModal({{ $file->id }}, {{auth()->user()->patient->doctor_id}})"
                                                       data-bs-toggle="modal" class="btn btn-sm btn-soft-info">
                                                        <i class="mdi mdi-comment-eye-outline font-size-15"></i>
                                                    </a>
                                                </li>
                                                <li data-bs-toggle="tooltip" data-bs-placement="top" title="Delete">
                                                    <a href="#csvDelete" id="{{ $file->id }}"
                                                       onclick="deleteCsv(this.id)"
                                                       data-bs-toggle="modal" class="btn btn-sm btn-soft-danger">
                                                        <i class="mdi mdi-delete-outline font-size-15"></i>
                                                    </a>
                                                </li>

                                            </ul>
                                        </td>
                                    </tr>

                                    <!-- Modal per il feedback -->
                                    <div class="modal fade" id="feedbackModal" tabindex="-1"
                                         aria-labelledby="feedbackModalLabel" aria-hidden="true">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="feedbackModalLabel">Feedback</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                            aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">

                                                    <div class="mb-3">
                                                        <label for="feedbackText"
                                                               class="form-label"><strong>Doctor: </strong>{{auth()->user()->patient->doctor->name." ".auth()->user()->patient->doctor->surname}}
                                                        </label>
                                                        <textarea id="feedbackText" name="feedbackText"
                                                                  class="form-control"
                                                                  rows="3"
                                                                  readonly> </textarea>
                                                    </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                                </tbody>
                            </table>

                        </div>
                        <div class="modal fade" id="csvDelete" tabindex="-1" aria-labelledby="jobDeleteLabel"
                             aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered modal-sm">
                                <div class="modal-content">
                                    <div class="modal-body px-4 py-5 text-center">
                                        <button type="button" class="btn-close position-absolute end-0 top-0 m-3"
                                                data-bs-dismiss="modal"
                                                aria-label="Close"></button>
                                        <div class="avatar-sm mb-4 mx-auto">
                                            <div
                                                class="avatar-title bg-warning text-warning bg-opacity-10 font-size-20 rounded-3">
                                                <i class="mdi mdi-trash-can-outline"></i>
                                            </div>
                                        </div>
                                        <p class="text-muted font-size-16 mb-4">Are you sure you want to delete the csv
                                            file?</p>
                                        <form action="{{ route('deleteCsv') }}" method="POST">
                                            @csrf
                                            <input type="hidden" name="csv_id" id="boxDelete">
                                            <div class="hstack gap-2 justify-content-center mb-0">
                                                <button type="submit" class="btn btn-danger">Delete</button>
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                                    Close
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>

                    @else

                        <h4 class="card-title mb-4">Latest Added Patients</h4>
                        <div class="table-responsive">
                            <table class="table table-bordered yajra-datatable">
                                <thead>
                                <tr>
                                    <th style="max-width: 40%">Full Name</th>
                                    <th style="max-width: 40%">Email</th>
                                    <th>Actions</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach(\App\Models\Patient::all()->sortByDesc('id') as $patient)
                                    @if(auth()->user()->role->name=="Doctor")
                                        @if($patient->doctor_id==auth()->user()->id)
                                            <tr>
                                                <td>{{$patient->name}} {{$patient->surname}}</td>
                                                <td>{{$patient->email}}
                                                </td>
                                                <td>
                                                    <ul class="list-unstyled hstack gap-1 mb-0">
                                                        <li data-bs-toggle="tooltip" data-bs-placement="top"
                                                            title="View">
                                                            <a href="{{ route('showCsvPatient', $patient->id) }}"
                                                               target="_blank" class="btn btn-sm btn-soft-primary">
                                                                <i class="mdi mdi-eye-outline font-size-15"></i>
                                                            </a>
                                                        </li>
                                                    </ul>
                                                </td>
                                            </tr>
                                        @endif
                                    @else
                                        <tr>
                                            <td>{{$patient->name}} {{$patient->surname}}</td>
                                            <td>{{$patient->email}}
                                            </td>
                                            <td>
                                                <ul class="list-unstyled hstack gap-1 mb-0">
                                                    <li data-bs-toggle="tooltip" data-bs-placement="top" title="View">
                                                        <a href="{{ route('showCsvPatient', $patient->id) }}"
                                                           target="_blank" class="btn btn-sm btn-soft-primary">
                                                            <i class="mdi mdi-eye-outline font-size-15"></i>
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

                    @endif
                </div>
            </div>
        </div>
    </div>
    <!-- end row -->
@endsection
@section('script')
    <script>

        function deleteCsv(id) {
            document.getElementById('boxDelete').value = id;
        }


        function openFeedbackModal(csvId, doctorId) {

            let feedbackText = document.getElementById('feedbackText');
            feedbackText.value = "Loading feedback...";


            // Recupera il feedback
            fetch(`/get-feedback/${csvId}/${doctorId}`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error("Request error");
                    }
                    return response.json();
                })
                .then(data => {
                    feedbackText.value = data.feedback ?? "No feedback available.";

                    // Se è un medico, abilita la modifica e il pulsante di salvataggio
                    feedbackText.readOnly = true;
                })
                .catch(error => {
                    console.error("Error fetching feedback:", error);
                    feedbackText.value = "Error loading feedback.";
                });
        }


        $(document).ready(function () {

            $('.yajra-datatable').DataTable({
                order: [[0, "desc"]], // Ordina per la prima colonna (data)
                columnDefs: [
                    {orderable: false, targets: -1} // Disabilita ordinamento sull'ultima colonna

                ],
                pageLength: 5, // Numero di righe per pagina
                lengthMenu: [5],
            });


            $('.yajra-datatable-patient').DataTable(
                {
                    order: [[0, "desc"]], // Ordina per la prima colonna (data)
                    columnDefs: [
                        {orderable: false, targets: -1} // Disabilita ordinamento sull'ultima colonna
                    ]
                    ,
                    pageLength: 5, // Numero di righe per pagina
                    lengthMenu: [5],
                });


            $('[data-bs-toggle="tooltip"]').tooltip();
        });


    </script>




    <script src="https://cdn.datatables.net/1.13.1/js/jquery.dataTables.min.js"></script>

    <script src="https://cdn.datatables.net/1.13.1/js/dataTables.bootstrap5.min.js"></script>

@endsection
