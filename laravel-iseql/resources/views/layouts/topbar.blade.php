@php use App\Models\Notification; @endphp
<header id="page-topbar">
    <div class="navbar-header">
        <div class="d-flex">
            <!-- LOGO -->
            <div class="navbar-brand-box"><br><br>
                <a href="{{route("root")}}" class="logo logo-dark">
                    <span class="logo-sm">
                        <img src="{{ URL::asset('/assets/images/logo.svg') }}" alt="" height="22">
                    </span>
                    <span class="logo-lg">
                        <img src="{{ URL::asset('/assets/images/logo-dark.png') }}" alt="" height="17">
                    </span>
                </a>

                <a href="{{route("root")}}" class="logo logo-light">
                    <span class="logo-sm">
                        <img src="{{ URL::asset('/assets/images/logo.svg') }}" alt="" height="22">
                    </span>
                    <span class="logo-lg">
                        <img src="{{ URL::asset('/assets/images/logo.png') }}" alt="" height="100"
                             style="margin-top: 35px">
                    </span>
                </a>
            </div>


            <!-- App Search-->
            <!--    <form class="app-search d-none d-lg-block">
                <div class="position-relative">
                     <input type="text" class="form-control" placeholder="@lang('translation.Search')">
                    <span class="bx bx-search-alt"></span>
                </div>
            </form> -->
        </div>
        <div class="d-flex">

            <div class="dropdown d-inline-block d-lg-none ms-2">
                <button type="button" class="btn header-item noti-icon waves-effect" id="page-header-search-dropdown"
                        data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <i class="mdi mdi-magnify"></i>
                </button>
                <div class="dropdown-menu dropdown-menu-lg dropdown-menu-end p-0"
                     aria-labelledby="page-header-search-dropdown">

                    <form class="p-3">
                        <div class="form-group m-0">
                            <div class="input-group">
                                <input type="text" class="form-control" placeholder="@lang('translation.Search')"
                                       aria-label="Search input">

                                <button class="btn btn-primary" type="submit"><i class="mdi mdi-magnify"></i></button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>


            <div class="dropdown d-inline-block">
                <button type="button" class="btn header-item waves-effect" id="page-header-user-dropdown"
                        data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <img class="rounded-circle header-profile-user"
                         src="{{ isset(Auth::user()->avatar) ? asset(Auth::user()->avatar) : asset('/images/avatar-default.jpeg') }}"
                         alt="Header Avatar">
                    <span class="d-none d-xl-inline-block ms-1" key="t-henry">{{ucfirst(Auth::user()->name)}}</span>
                    <i class="mdi mdi-chevron-down d-none d-xl-inline-block"></i>
                </button>
                <div class="dropdown-menu dropdown-menu-end">
                    <!-- item-->
                    <a class="dropdown-item" href="{{Route('userProfile')}}"><i
                            class="bx bx-user font-size-16 align-middle me-1"></i> <span
                            key="t-profile">@lang('translation.Profile')</span></a>
                    <div class="dropdown-divider"></div>
                    <a class="dropdown-item text-danger" href="javascript:void();"
                       onclick="event.preventDefault(); document.getElementById('logout-form').submit();"><i
                            class="bx bx-power-off font-size-16 align-middle me-1 text-danger"></i> <span
                            key="t-logout">@lang('translation.Logout')</span></a>
                    <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                        @csrf
                    </form>
                </div>
            </div>

            @php
                $notifications = Notification::where('user_id', auth()->user()->id)
                  ->orderBy("created_at", "desc")
                   ->get();

            @endphp

            <div class="dropdown d-inline-block">
                <button type="button" class="btn header-item noti-icon waves-effect"
                        id="page-header-notifications-dropdown"
                        data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <i class="bx bx-bell"></i> <!-- Icona campana -->
                    <!-- Contatore notifiche, rosso se ci sono notifiche non lette -->
                    <span class="badge bg-danger rounded-pill" id="notification-count">
    {{ $notifications->where('status','unread')->count() }} <!-- Contatore notifiche -->
</span>
                </button>
                <div class="dropdown-menu dropdown-menu-lg dropdown-menu-end p-0"
                     aria-labelledby="page-header-notifications-dropdown">
                    <div class="p-3">
                        <div class="row align-items-center">
                            <div class="p-3 d-flex justify-content-between align-items-center">
                                <h6 class="m-0">Notifiche</h6>
                                <!-- Icona cestino per svuotare tutte le notifiche -->
                                <button class="btn btn-sm btn-light text-danger shadow" id="delete-all-notification-btn"
                                        onclick="deleteAllNotifications()"
                                    {{ $notifications->isEmpty() ? 'disabled' : '' }}>
                                    <i class="bx bx-trash font-size-18"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Modal di Dettagli Notifica -->

                    <div id="notification-list" class="p-2" style="max-height: 300px; overflow-y: auto;">
                        @if($notifications->isEmpty())
                            <p class="text-muted text-center">No notifications available</p>
                        @else
                            @foreach($notifications as $notification)
                                <a href="#" class="dropdown-item notify-item"
                                   onclick="handleNotificationClick(
                                           `{{ addslashes($notification->title) }}`,
                                           `{{ addslashes($notification->message) }}`,
                                           `{{ $notification->id }}`,
                                           `{{ addslashes($notification->link) }}`
                                       )">
                                    <div class="d-flex align-items-center">
                                        <div class="flex-shrink-0 me-3">
                                            <i class="bx bx-message-square"></i> <!-- Message icon -->
                                        </div>
                                        <div class="flex-grow-1">

                                            <h6 class="text-truncate">{{ Str::limit($notification->title, 30, '...') }}
                                                @if($notification->file_id)
                                                    @if($notification->file->gmi < 7)
                                                        <span style="display:inline-block; width:10px; height:10px; background-color:green; border-radius:50%; margin-left:5px;"></span>
                                                    @elseif($notification->file->gmi >= 7 && $notification->file->gmi <= 8)
                                                        <span style="display:inline-block; width:10px; height:10px; background-color:orange; border-radius:50%; margin-left:5px;"></span>
                                                    @else
                                                        <span style="display:inline-block; width:10px; height:10px; background-color:red; border-radius:50%; margin-left:5px;"></span>
                                                    @endif
                                                @endif
                                            </h6>
                                            <p class="text-muted mb-0">{{ Str::limit($notification->message, 30, '...') }}</p>
                                        </div>
                                    </div>
                                </a>
                            @endforeach
                        @endif
                    </div>


                </div>
            </div>
        </div>
    </div>


    <meta name="csrf-token" content="{{ csrf_token() }}">

</header>

<div class="modal fade" id="notificationModal" tabindex="-1" aria-labelledby="notificationModalLabel"
     aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="notificationModalLabel">Notification Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <h5 id="notification-title"></h5>
                <p id="notification-message"></p>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-danger" id="delete-notification-btn"
                        onclick="deleteNotification()">Delete Notification
                </button>
            </div>
        </div>
    </div>
</div>


<script>
    var csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    // Funzione per gestire il clic sulla notifica
    function handleNotificationClick(title, message, notificationId) {

        // Imposta il contenuto del modal
        document.getElementById('notification-title').innerText = title;
        document.getElementById('notification-message').innerText = message;

        // Memorizza l'ID della notifica da eliminare
        document.getElementById('delete-notification-btn').setAttribute('data-notification-id', notificationId);

        // Mostra il modal
        var myModal = new bootstrap.Modal(document.getElementById('notificationModal'));
        myModal.show();
    }

    // Funzione per eliminare la notifica
    function deleteNotification() {
        var notificationId = document.getElementById('delete-notification-btn').getAttribute('data-notification-id');
        if (!notificationId) {
            alert("Errore: ID della notifica non trovato.");
            return;
        }


        // Fai una richiesta al server per eliminare la notifica
        fetch('/notifications/delete/' + notificationId, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            }
        })
            .then(response => {
                if (response.ok) {
                    alert('Notifica eliminata con successo');
                    location.reload(); // Ricarica la pagina per rimuovere la notifica dalla lista
                } else {
                    alert('Errore nell\'eliminazione della notifica');
                }
            })
            .catch(error => {
                console.error('Errore:', error);
                alert('Errore nell\'eliminazione della notifica');
            });

    }
    function deleteAllNotifications() {
        if (confirm('Sei sicuro di voler eliminare tutte le notifiche?')) {


            // Fai una richiesta al server per eliminare la notifica
            fetch('/notifications/delete/all/' + {{auth::user()->id}}, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                }
            })
                .then(response => {
                    if (response.ok) {
                        alert('Notifiche eliminate con successo');
                        location.reload(); // Ricarica la pagina per rimuovere la notifica dalla lista
                    } else {
                        alert('Errore nell\'eliminazione delle notifiche');
                    }
                })
                .catch(error => {
                    console.error('Errore:', error);
                    alert('Errore nell\'eliminazione dellae notifiche');
                });

        }
    }


    // Aggiungi un ascoltatore per l'evento di apertura del dropdown delle notifiche
    document.getElementById('page-header-notifications-dropdown').addEventListener('click', function (event) {
        // Verifica se il dropdown è già stato aperto
        var dropdown = document.getElementById('page-header-notifications-dropdown');
        if (dropdown.getAttribute('aria-expanded') === 'true') {
            markNotificationsAsRead();
        }
    });

    function markNotificationsAsRead() {
        fetch('/notifications/set-read', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
            }
        })
            .then(response => {
                // Verifica se la risposta è valida e se è in formato JSON
                if (!response.ok) {
                    throw new Error('Errore nella risposta del server');
                }
                return response.json(); // Cerca di convertire la risposta in JSON
            })
            .then(data => {
                // Assicurati che la risposta contenga il campo "success"
                if (data.success) {
                    document.getElementById('notification-count').innerText = 0;

                } else {
                    console.error('Errore nella risposta:', data);
                }
            })
            .catch(error => {
                console.error('Errore:', error);
            });
    }


</script>

<!--  Change-Password example -->
<div class="modal fade change-password" tabindex="-1" role="dialog"
     aria-labelledby="myLargeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="myLargeModalLabel">Change Password</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"
                        aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form method="POST" id="change-password">
                    @csrf
                    <input type="hidden" value="{{ Auth::user()->id }}" id="data_id">
                    <div class="mb-3">
                        <label for="current_password">Current Password</label>
                        <input id="current-password" type="password"
                               class="form-control @error('current_password') is-invalid @enderror"
                               name="current_password" autocomplete="current_password"
                               placeholder="Enter Current Password" value="{{ old('current_password') }}">
                        <div class="text-danger" id="current_passwordError" data-ajax-feedback="current_password"></div>
                    </div>

                    <div class="mb-3">
                        <label for="newpassword">New Password</label>
                        <input id="password" type="password"
                               class="form-control @error('password') is-invalid @enderror" name="password"
                               autocomplete="new_password" placeholder="Enter New Password">
                        <div class="text-danger" id="passwordError" data-ajax-feedback="password"></div>
                    </div>

                    <div class="mb-3">
                        <label for="userpassword">Confirm Password</label>
                        <input id="password-confirm" type="password" class="form-control" name="password_confirmation"
                               autocomplete="new_password" placeholder="Enter New Confirm password">
                        <div class="text-danger" id="password_confirmError" data-ajax-feedback="password-confirm"></div>
                    </div>

                    <div class="mt-3 d-grid">
                        <button class="btn btn-primary waves-effect waves-light UpdatePassword"
                                data-id="{{ Auth::user()->id }}"
                                type="submit">Update Password
                        </button>
                    </div>
                </form>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->
