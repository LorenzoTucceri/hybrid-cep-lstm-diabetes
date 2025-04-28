@extends('layouts.master-without-nav')

@section('title') Registrazione @endsection

@section('body')
@endsection

@section('content')
    <body>
    <div class="account-pages my-5 pt-sm-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-8 col-lg-6 col-xl-5">
                    <div class="card overflow-hidden">
                        <div class="bg-soft py-1" style="background-color: white;">
                            <div class="row justify-content-center">
                                <div class="col-12 text-center">
                                    <img src="{{ URL::asset('/assets/images/logo.png') }}" alt="Logo" class="img-fluid" style="max-width: 300px; height:100px">
                                </div>
                            </div>
                        </div>
                        <div class="card-body pt-4">
                            @if (\Session::has('success'))
                                <div class="alert alert-success" role="alert">
                                    {{ \Session::get('success') }}
                                </div>
                            @endif
                            @error("error")
                            <div class="alert alert-danger" role="alert">
                                {{$message}}
                            </div>
                            @enderror

                            <div class="">
                                <form class="form-horizontal" method="POST" action="{{ route('register.token.submit', $token) }}">
                                    @csrf

                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label for="nome" class="form-label">Nome</label>
                                            <input name="name" type="text" class="form-control" id="nome"
                                                   value="{{ old('name', $name) }}" disabled>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="cognome" class="form-label">Cognome</label>
                                            <input name="surname" type="text" class="form-control" id="cognome"
                                                   value="{{ old('surname', $surname) }}" disabled>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label for="email" class="form-label">Email</label>
                                        <input name="emailF" type="email" class="form-control" id="email"
                                               value="{{ old('email', $email) }}" disabled>
                                        <input name="email" type="hidden" class="form-control" id="email"
                                               value="{{ old('email', $email) }}">
                                    </div>

                                    <div class="mb-3">
                                        <label for="password" class="form-label">Password</label>
                                        <div class="input-group auth-pass-inputgroup">
                                            <input type="password" name="newPassword"
                                                   class="form-control @error('password') is-invalid @enderror"
                                                   id="password"
                                                   placeholder="Inserisci la password" minlength="6" required>
                                            <button class="btn btn-light toggle-password" type="button" data-target="#password">
                                                <i class="mdi mdi-eye-outline"></i>
                                            </button>
                                            @error('password')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label for="password-confirm" class="form-label">Conferma Password</label>
                                        <div class="input-group auth-pass-inputgroup">
                                            <input type="password" name="confirmPassword"
                                                   class="form-control"
                                                   id="password-confirm"
                                                   placeholder="Conferma la password" minlength="6">
                                            <button class="btn btn-light toggle-password" type="button" data-target="#password-confirm" required>
                                                <i class="mdi mdi-eye-outline"></i>
                                            </button>
                                        </div>
                                    </div>

                                    <div class="mt-3 d-grid">
                                        <button class="btn btn-primary waves-effect waves-light" type="submit">Registrati</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript per abilitare il toggle della visibilità delle password -->
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            document.querySelectorAll('.toggle-password').forEach(button => {
                button.addEventListener('click', function() {
                    const target = document.querySelector(button.getAttribute('data-target'));
                    const type = target.getAttribute('type') === 'password' ? 'text' : 'password';
                    target.setAttribute('type', type);
                    button.innerHTML = `<i class="mdi mdi-eye${type === 'password' ? '-outline' : ''}"></i>`;
                });
            });
        });
    </script>
    </body>
@endsection
