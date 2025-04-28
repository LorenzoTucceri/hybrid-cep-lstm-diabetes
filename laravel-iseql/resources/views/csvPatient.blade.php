@extends('layouts.master')

@section('title')
    Csv Patient
@endsection

@section('css')
    <!--datatable css-->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css"/>
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.2.2/css/buttons.dataTables.min.css">
@endsection

@section('content')

    @component('components.breadcrumb')
        @slot('li_1')
            Csv files
        @endslot
        @slot('title')
            Csv Patient
        @endslot
    @endcomponent
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
                    <h3 class="card-title mb-3 text-xl-center">Patient {{$patient->name.' '.$patient->surname}}</h3>
                    <h4 class="card-title mb-3">Add csv file</h4>
                    <form action="{{ route('uploadCsv') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="patient_id" value="{{$patient->id}}">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <input id="csv" name="csv[]" type="file"
                                           class="form-control @error('csv') is-invalid @enderror" multiple required>
                                    @error('csv')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <button type="submit" class="btn btn-primary waves-effect waves-light w-lg">
                                        Save
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                    <br>
                    <h4 class="card-title mb-3">Csv files List</h4>
                    <table class="table table-bordered yajra-datatable">
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
                                            <a href="{{ route('viewCsv', ['csvId' => $file->id, 'patientId' => $patient->id]) }}"
                                               target="_blank" class="btn btn-sm btn-soft-primary">
                                                <i class="mdi mdi-eye-outline font-size-15"></i>
                                            </a>
                                        </li>
                                        <li data-bs-toggle="tooltip" data-bs-placement="top" title="Feedback">
                                            @if(auth()->user()->role->name  == 'Doctor')
                                                <a href="#feedbackModal"
                                                   onclick="openFeedbackModal({{ $file->id }}, true, {{auth()->user()->id}})"
                                                   data-bs-toggle="modal" class="btn btn-sm btn-soft-info">
                                                    <i class="mdi mdi-comment-edit-outline font-size-15"></i>
                                                </a>
                                            @elseif(auth()->user()->role->name == 'Patient')
                                                <a href="#feedbackModal"
                                                   onclick="openFeedbackModal({{ $file->id }}, false,  {{auth()->user()->patient->doctor_id}})"
                                                   data-bs-toggle="modal" class="btn btn-sm btn-soft-info">
                                                    <i class="mdi mdi-comment-eye-outline font-size-15"></i>
                                                </a>
                                            @endif
                                        </li>
                                        <li data-bs-toggle="tooltip" data-bs-placement="top" title="Delete">
                                            <a href="#csvDelete" id="{{ $file->id }}" onclick="deleteCsv(this.id)"
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
                                            <form id="feedbackForm" method="POST" action="{{route('saveFeedback')}}">
                                                @csrf
                                                <input type="hidden" id="csvFileId" name="file_id">
                                                <input type="hidden" id="doctorId" name="doctor_id">

                                                <div class="mb-3">
                                                    <label for="feedbackText"
                                                           class="form-label"><strong>Doctor: </strong>
                                                        @if(auth()->user()->role->name=="Patient")
                                                            {{ auth()->user()->patient->doctor->name." ".auth()->user()->patient->doctor->surname }}
                                                        @else
                                                            {{ auth()->user()->name." ".auth()->user()->surname }}
                                                        @endif
                                                    </label>
                                                    <textarea id="feedbackTextDoctor" name="feedbackDoctor"
                                                              class="form-control" rows="3"
                                                              @if(auth()->user()->role->name=="Patient") placeholder="No feedback available..."
                                                              readonly
                                                              @else placeholder="Leave a feedback..."
                                                              required @endif></textarea>
                                                </div>

                                                <div class="mb-3" id="labelPatient">
                                                    <label for="feedbackTextPatient"
                                                           class="form-label"><strong>Patient: </strong>{{$patient->name." ".$patient->surname}}
                                                    </label>
                                                    <textarea id="feedbackTextPatient" name="feedbackPatient"
                                                              class="form-control" rows="3"
                                                              @if(auth()->user()->role->name=="Doctor") placeholder="No feedback available..."
                                                              readonly
                                                              @else placeholder="Leave a feedback..."
                                                              required @endif></textarea>
                                                </div>

                                                <div class="d-flex justify-content-center">
                                                    <button type="submit" id="saveFeedbackButton"
                                                            class="btn btn-primary">
                                                        Send
                                                    </button>
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
            </div>
        </div>
    </div>

    <div class="modal fade" id="csvDelete" tabindex="-1" aria-labelledby="jobDeleteLabel" aria-hidden="true">
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
                    <p class="text-muted font-size-16 mb-4">Are you sure you want to delete the csv file?</p>
                    <form action="{{ route('deleteCsv') }}" method="POST">
                        @csrf
                        <input type="hidden" name="csv_id" id="boxDelete">
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
        function deleteCsv(id) {
            document.getElementById('boxDelete').value = id;
        }

        function openFeedbackModal(csvId, isDoctor, doctorId) {
            document.getElementById('csvFileId').value = csvId;
            document.getElementById('doctorId').value = doctorId;

            let feedbackTextPatient = document.getElementById('feedbackTextPatient');
            let feedbackTextDoctor = document.getElementById('feedbackTextDoctor');
            let labelPatient = document.getElementById('labelPatient');
            let saveButton = document.getElementById('saveFeedbackButton');

            // Imposta valori iniziali
            [feedbackTextPatient, feedbackTextDoctor].forEach(textarea => {
                textarea.value = "Loading feedback...";
                textarea.readOnly = true;
            });

            // Recupera il feedback
            fetch(`/get-feedback/${csvId}/${doctorId}`)
                .then(response => response.ok ? response.json() : Promise.reject("Request error"))
                .then(data => {
                    // Assegna i valori ricevuti
                    feedbackTextDoctor.value = data.message_doctor || "";
                    feedbackTextPatient.value = data.message_patient || "";

                    if (isDoctor) {
                        feedbackTextDoctor.readOnly = feedbackTextPatient.value.trim() !== "";
                        saveButton.style.display = feedbackTextPatient.value.trim() ? "none" : "block";
                    } else {
                        feedbackTextPatient.readOnly = false;
                        feedbackTextDoctor.readOnly = true;
                        saveButton.style.display = feedbackTextDoctor.value.trim() ? "block" : "none";
                    }

                    labelPatient.style.display = feedbackTextDoctor.value.trim() ? "block" : "none";
                })
                .catch(error => {
                    console.error("Error fetching feedback:", error);
                    feedbackTextPatient.value = "Error loading feedback.";
                    feedbackTextDoctor.value = "Error loading feedback.";
                });
        }


        $(document).ready(function () {
            $('.yajra-datatable').DataTable({
                order: [[0, "desc"]], // Ordina per la prima colonna (data)
                columnDefs: [
                    {orderable: false, targets: -1} // Disabilita ordinamento sull'ultima colonna
                ]
            });
            $('[data-bs-toggle="tooltip"]').tooltip();
        });


    </script>


    <script src="https://cdn.datatables.net/1.13.1/js/jquery.dataTables.min.js"></script>

    <script src="https://cdn.datatables.net/1.13.1/js/dataTables.bootstrap5.min.js"></script>

@endsection
