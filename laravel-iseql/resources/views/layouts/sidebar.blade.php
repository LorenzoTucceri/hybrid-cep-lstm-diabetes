<!-- ========== Left Sidebar Start ========== -->
<div class="vertical-menu">

    <div data-simplebar class="h-100">

        <!--- Sidemenu -->
        <div id="sidebar-menu">
            <!-- Left Menu Start -->
            <ul class="metismenu list-unstyled" id="side-menu">
                <li class="menu-title" key="t-menu">@lang('translation.Menu')</li>
                <li>
                    <a href="{{route('root')}}">
                        <i class="bx bx-home-circle"></i>
                        <span key="t-dashboards">Dashboard</span>
                    </a>
                </li>
                @if(auth()->user()->role->id=="1")
                    <li>
                        <a href="javascript: void(0);" class="has-arrow waves-effect">
                            <i class="bx bx-user-circle"></i>
                            <span key="t-authentication">Operators</span>
                        </a>
                        <ul class="sub-menu" aria-expanded="false">
                            <li><a href="{{route('userProfile')}}" key="t-user">Personal Profile</a></li>
                            @if (auth()->user()->role->sku=="admin")
                                <li><a href="{{route('userManagement')}}" key="t-users">User Management</a></li>
                            @endif
                        </ul>
                    </li>
                @endif
                @if(auth()->user()->role->id!="1")
                    <li><a href="{{route('userProfile')}}" key="t-user"><i class="bx bx-user-circle"></i>Personal Profile </a>

                    </li>
                @endif

                @if(auth()->user()->role->id!="2")
                    <li>
                        <a href="{{route('patientManagement')}}">
                            <i class="bx bxs-user-detail"></i>
                            <span key="t-clients">Patient</span>
                        </a>
                    </li>
                @endif

                @if(auth()->user()->role->id=="2")
                    <li>
                        <a href="{{route('showCsvPatient', auth()->user()->patient_id)}}">
                            <i class="bx bxs-user-detail"></i>
                            <span key="t-clients">Details</span>
                        </a>
                    </li>
                @endif


            </ul>
        </div>
    </div>
</div>
