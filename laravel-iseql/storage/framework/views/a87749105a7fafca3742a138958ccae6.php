<!-- ========== Left Sidebar Start ========== -->
<div class="vertical-menu">

    <div data-simplebar class="h-100">

        <!--- Sidemenu -->
        <div id="sidebar-menu">
            <!-- Left Menu Start -->
            <ul class="metismenu list-unstyled" id="side-menu">
                <li class="menu-title" key="t-menu"><?php echo app('translator')->get('translation.Menu'); ?></li>
                <li>
                    <a href="<?php echo e(route('root')); ?>">
                        <i class="bx bx-home-circle"></i>
                        <span key="t-dashboards">Dashboard</span>
                    </a>
                </li>
                <?php if(auth()->user()->role->id=="1"): ?>
                    <li>
                        <a href="javascript: void(0);" class="has-arrow waves-effect">
                            <i class="bx bx-user-circle"></i>
                            <span key="t-authentication">Operators</span>
                        </a>
                        <ul class="sub-menu" aria-expanded="false">
                            <li><a href="<?php echo e(route('userProfile')); ?>" key="t-user">Personal Profile</a></li>
                            <?php if(auth()->user()->role->sku=="admin"): ?>
                                <li><a href="<?php echo e(route('userManagement')); ?>" key="t-users">User Management</a></li>
                            <?php endif; ?>
                        </ul>
                    </li>
                <?php endif; ?>
                <?php if(auth()->user()->role->id!="1"): ?>
                    <li><a href="<?php echo e(route('userProfile')); ?>" key="t-user"><i class="bx bx-user-circle"></i>Personal Profile </a>

                    </li>
                <?php endif; ?>

                <?php if(auth()->user()->role->id!="2"): ?>
                    <li>
                        <a href="<?php echo e(route('patientManagement')); ?>">
                            <i class="bx bxs-user-detail"></i>
                            <span key="t-clients">Patient</span>
                        </a>
                    </li>
                <?php endif; ?>

                <?php if(auth()->user()->role->id=="2"): ?>
                    <li>
                        <a href="<?php echo e(route('showCsvPatient', auth()->user()->patient_id)); ?>">
                            <i class="bx bxs-user-detail"></i>
                            <span key="t-clients">Details</span>
                        </a>
                    </li>
                <?php endif; ?>


            </ul>
        </div>
    </div>
</div>
<?php /**PATH /Users/lorenzotucceri/Progetti/laravel-iseql/resources/views/layouts/sidebar.blade.php ENDPATH**/ ?>