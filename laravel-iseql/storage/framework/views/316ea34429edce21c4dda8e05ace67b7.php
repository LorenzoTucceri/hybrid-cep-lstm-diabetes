<?php $__env->startSection('title'); ?> Patient Details <?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>

    <?php $__env->startComponent('components.breadcrumb'); ?>
        <?php $__env->slot('li_1'); ?> Patient <?php $__env->endSlot(); ?>
        <?php $__env->slot('title'); ?> Patient Details <?php $__env->endSlot(); ?>
    <?php echo $__env->renderComponent(); ?>

    <div class="row">
        <div class="col-lg-12">
            <!-- Card for Patient Info -->
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h4 class="card-title mb-0">Patient: <?php echo e($client->name." ".$client->surname); ?></h4>
                        <a href="<?php echo e(route('download.pdf', $client->id)); ?>" class="btn btn-primary">Download PDF</a>
                    </div>
                    <div class="table-responsive">
                        <table class="table align-middle table-nowrap">
                            <tbody>
                            <tr><td>Email:</td><td><?php echo e($client->email); ?></td></tr>
                            <tr><td>Date of Birth:</td><td><?php echo e($client->birth); ?></td></tr>
                            <tr><td>Phone Number:</td><td><?php echo e($client->telephone_number); ?></td></tr>
                            <tr><td>Address:</td><td><?php echo e($client->address ?: 'Not Specified'); ?></td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="card mt-4">
                <div class="card-body">
                    <h4 class="card-title mb-3 text-center" style="font-size: 1.3rem;">Analysis Summary</h4>

                    <!-- Totals and Durations Section -->
                    <h4 class="card-title mb-3 text-left" style="font-size: 1.0rem;">Totals and Durations</h4>
                    <div class="row">
                        <?php $__currentLoopData = ['extremely_high', 'high', 'low', 'extremely_low', 'normal']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="col-md-4 mb-3">
                                <div class="card border h-100">
                                    <div class="card-body">
                                        <h5 class="card-title">Total <?php echo e(ucfirst($category)); ?> Glucose</h5>
                                        <p class="card-text">
                                            <?php echo e($data['totals_and_durations']['totals'][$category] ?? 'N/A'); ?>

                                        </p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <div class="card border h-100">
                                    <div class="card-body">
                                        <h5 class="card-title">Duration <?php echo e(ucfirst($category)); ?> (hours)</h5>
                                        <p class="card-text">
                                            <?php echo e($data['totals_and_durations']['durations_hhmm'][$category] ?? 'N/A'); ?>

                                        </p>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div><br>

                    <!-- Glycemic Swings Stats Section -->
                    <h4 class="card-title mb-3 text-left" style="font-size: 1.0rem;">Glycemic Swings Stats</h4>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <div class="card border h-100">
                                <div class="card-body">
                                    <h5 class="card-title">Total Glycemic Swings</h5>
                                    <p class="card-text">
                                        <?php echo e($data['totals_and_durations']['glycemic_swings_stats']['total_glycemic_swings'] ?? 'N/A'); ?>

                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="card border h-100">
                                <div class="card-body">
                                    <h5 class="card-title">Total Glycemic Swings Followed by Anomalous Duration</h5>
                                    <p class="card-text">
                                        <?php echo e($data['totals_and_durations']['glycemic_swings_stats']['total_glycemic_swings_followed_by_anomalous_duration'] ?? 'N/A'); ?>

                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="card border h-100">
                                <div class="card-body">
                                    <h5 class="card-title">Percentage Swings with Anomalous Duration</h5>
                                    <p class="card-text">
                                        <?php echo e(number_format($data['totals_and_durations']['glycemic_swings_stats']['percentage_swings_with_anomalous_duration'], 2)); ?>%
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div><br>

                    <!-- Anomalous Frequency and Duration Stats Section -->
                    <h4 class="card-title mb-3 text-left" style="font-size: 1.0rem;">Anomalous Frequency and Duration Stats</h4>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <div class="card border h-100">
                                <div class="card-body">
                                    <h5 class="card-title">Percentage Anomalous Frequency Extremely High/Low</h5>
                                    <p class="card-text">
                                        <?php echo e(number_format($data['totals_and_durations']['anomalous_frequency_stats']['percentage_anomalous_frequency_extremely_high_low'], 2)); ?>%
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="card border h-100">
                                <div class="card-body">
                                    <h5 class="card-title">Total Anomalous Duration Extremely High/Low</h5>
                                    <p class="card-text">
                                        <?php echo e($data['totals_and_durations']['anomalous_frequency_stats']['total_anomalous_duration_extremely_high_low'] ?? 'N/A'); ?>

                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="card border h-100">
                                <div class="card-body">
                                    <h5 class="card-title">Percentage Anomalous Duration Extremely High/Low</h5>
                                    <p class="card-text">
                                        <?php echo e(number_format($data['totals_and_durations']['anomalous_frequency_stats']['percentage_anomalous_duration_extremely_high_low'], 2)); ?>%
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mt-4">
                    <div class="card-body">
                        <h4 class="card-title mb-3 text-center" style="font-size: 1.3rem;">Max Anomalous Day Details</h4>
                        <!-- Max Anomalous Day Details -->
                                        <table class="table table-bordered">
                                            <thead>
                                            <tr>
                                                <th>Detail</th>
                                                <th>Value</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            <tr>
                                                <td>Date</td>
                                                <td><?php echo e($data['totals_and_durations']['max_anomalous_day']['date'] ?? 'N/A'); ?></td>
                                            </tr>
                                            <tr>
                                                <td>Extremely High Count</td>
                                                <td><?php echo e($data['totals_and_durations']['max_anomalous_day']['details']['Extremely High Count'] ?? 'N/A'); ?></td>
                                            </tr>
                                            <tr>
                                                <td>Extremely Low Count</td>
                                                <td><?php echo e($data['totals_and_durations']['max_anomalous_day']['details']['Extremely Low Count'] ?? 'N/A'); ?></td>
                                            </tr>
                                            <tr>
                                                <td>High Count</td>
                                                <td><?php echo e($data['totals_and_durations']['max_anomalous_day']['details']['High Count'] ?? 'N/A'); ?></td>
                                            </tr>
                                            <tr>
                                                <td>Low Count</td>
                                                <td><?php echo e($data['totals_and_durations']['max_anomalous_day']['details']['Low Count'] ?? 'N/A'); ?></td>
                                            </tr>
                                            <tr>
                                                <td>Total Count</td>
                                                <td><?php echo e($data['totals_and_durations']['max_anomalous_day']['details']['Total Count'] ?? 'N/A'); ?></td>
                                            </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

            <div class="card mt-4">
                <div class="card-body">
                    <h4 class="card-title mb-3 text-center" style="font-size: 1.3rem;">Analysis Results</h4>
                    <h5 class="card-title mb-4">Anomalous Duration</h5>
                    <?php if(isset($data['anomalous_duration']) && count($data['anomalous_duration']) > 0): ?>
                        <div class="table-responsive">
                            <table id="datatable-anomalous-duration" class="table table-bordered dt-responsive nowrap w-100">
                                <thead>
                                <tr>
                                    <th>Day</th>
                                    <th>Event</th>
                                    <th>Start Time</th>
                                    <th>End Time</th>
                                    <th>Duration (h)</th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php $__currentLoopData = $data['anomalous_duration']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $interval): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <tr>
                                        <td><?php echo e($interval['day'] ?? 'N/A'); ?></td>
                                        <td><?php echo e($interval['event'] ?? 'N/A'); ?></td>
                                        <td><?php echo e($interval['start_time'] ?? 'N/A'); ?></td>
                                        <td><?php echo e($interval['end_time'] ?? 'N/A'); ?></td>
                                        <td><?php echo e($interval['duration'] ?? 'N/A'); ?></td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p class="text-muted">Nessun dato trovato per anomalous duration.</p>
                    <?php endif; ?>

                    <!-- Anomalous Frequency -->
                    <br><h5 class="card-title mb-4">Anomalous Frequency</h5>
                    <?php if(isset($data['anomalous_frequency']) && count($data['anomalous_frequency']) > 0): ?>
                        <div class="table-responsive">
                            <table id="datatable-anomalous-frequency" class="table table-bordered dt-responsive nowrap w-100">
                                <thead>
                                <tr>
                                    <th>Day</th>
                                    <th>High Count</th>
                                    <th>Low Count</th>
                                    <th>Extremely High Count</th>
                                    <th>Extremely Low Count</th>
                                    <th>Total Count</th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php $__currentLoopData = $data['anomalous_frequency']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $frequency): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <tr>
                                        <td><?php echo e($frequency['day'] ?? 'N/A'); ?></td>
                                        <td><?php echo e($frequency['high_count'] ?? 'N/A'); ?></td>
                                        <td><?php echo e($frequency['low_count'] ?? 'N/A'); ?></td>
                                        <td><?php echo e($frequency['extremely_high_count'] ?? 'N/A'); ?></td>
                                        <td><?php echo e($frequency['extremely_low_count'] ?? 'N/A'); ?></td>
                                        <td><?php echo e($frequency['total_count'] ?? 'N/A'); ?></td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p class="text-muted">Nessun dato trovato per anomalous frequency.</p>
                    <?php endif; ?>

                    <!-- Glycemic Swings -->
                    <br><h5 class="card-title mb-4">Glycemic Swings</h5>
                    <?php if(isset($data['glycemic_swings']) && count($data['glycemic_swings']) > 0): ?>
                        <div class="table-responsive">
                            <table id="datatable-glycemic-swings" class="table table-bordered dt-responsive nowrap w-100">
                                <thead>
                                <tr>
                                    <th>Day</th>
                                    <th>First Event</th>
                                    <th>Second Event</th>
                                    <th>Duration Time Swing (h)</th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php $__currentLoopData = $data['glycemic_swings']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $swing): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <tr>
                                        <td><?php echo e($swing['day'] ?? 'N/A'); ?></td>
                                        <td><?php echo e($swing['first_event'] ?? 'N/A'); ?></td>
                                        <td><?php echo e($swing['second_event'] ?? 'N/A'); ?></td>
                                        <td><?php echo e($swing['duration_time_swing'] ?? 'N/A'); ?></td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p class="text-muted">Nessun dato trovato per glycemic swings.</p>
                    <?php endif; ?>

                    <!-- Swings Followed By Anomalous Duration -->
                    <br><h5 class="card-title mb-4">Swings Followed By Anomalous Duration</h5>
                    <?php if(isset($data['swings_followed_by_duration']) && count($data['swings_followed_by_duration']) > 0): ?>
                        <div class="table-responsive">
                            <table id="datatable-swings-followed-by-duration" class="table table-bordered dt-responsive nowrap w-100">
                                <thead>
                                <tr>
                                    <th>Day</th>
                                    <th>First Swing Event</th>
                                    <th>Second Swing Event</th>
                                    <th>Duration Time Swing (h)</th>
                                    <th>Anomalous Durations (h)</th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php $__currentLoopData = $data['swings_followed_by_duration']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $swing): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <tr>
                                        <td><?php echo e($swing['day'] ?? 'N/A'); ?></td>
                                        <td><?php echo e($swing['first_event'] ?? 'N/A'); ?></td>
                                        <td><?php echo e($swing['second_event'] ?? 'N/A'); ?></td>
                                        <td><?php echo e($swing['duration_time_swing'] ?? 'N/A'); ?></td>
                                        <td><?php echo e($swing['anomalous_durations'] ?? 'N/A'); ?></td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p class="text-muted">Nessun dato trovato per swings followed by anomalous duration.</p>
                    <?php endif; ?>

                    <!-- Swings Followed By Anomalous Frequency -->
                    <br><h5 class="card-title mb-4">Swings Followed By Anomalous Frequency</h5>
                    <?php if(isset($data['swings_followed_by_frequency']) && count($data['swings_followed_by_frequency']) > 0): ?>
                        <div class="table-responsive">
                            <table id="datatable-swings-followed-by-frequency" class="table table-bordered dt-responsive nowrap w-100">
                                <thead>
                                <tr>
                                    <th>Day</th>
                                    <th>First Swing Event</th>
                                    <th>Second Swing Event</th>
                                    <th>Duration Time Swing (h)</th>
                                    <th>Anomalous Frequency</th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php $__currentLoopData = $data['swings_followed_by_frequency']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $swing): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <tr>
                                        <td><?php echo e($swing['day'] ?? 'N/A'); ?></td>
                                        <td><?php echo e($swing['first_event'] ?? 'N/A'); ?></td>
                                        <td><?php echo e($swing['second_event'] ?? 'N/A'); ?></td>
                                        <td><?php echo e($swing['duration_time_swing'] ?? 'N/A'); ?></td>
                                        <td><?php echo e($swing['anomalous_frequency'] ?? 'N/A'); ?></td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p class="text-muted">Nessun dato trovato per swings followed by anomalous frequency.</p>
                    <?php endif; ?>

                    <!-- Anomalous Duration for Extremely High/Low -->
                    <br><h5 class="card-title mb-4">Anomalous Duration for Extremely High/Low</h5>
                    <?php if(isset($data['anomalies_for_extremely_high_low_duration']) && count($data['anomalies_for_extremely_high_low_duration']) > 0): ?>
                        <div class="table-responsive">
                            <table id="datatable-anomalies-extremely-high-low-duration" class="table table-bordered dt-responsive nowrap w-100">
                                <thead>
                                <tr>
                                    <th>Event</th>
                                    <th>Start Time</th>
                                    <th>End Time</th>
                                    <th>Duration (h)</th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php $__currentLoopData = $data['anomalies_for_extremely_high_low_duration']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $duration): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <tr>
                                        <td><?php echo e($duration['event'] ?? 'N/A'); ?></td>
                                        <td><?php echo e($duration['start_time'] ?? 'N/A'); ?></td>
                                        <td><?php echo e($duration['end_time'] ?? 'N/A'); ?></td>
                                        <td><?php echo e($duration['duration'] ?? 'N/A'); ?></td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p class="text-muted">Nessun dato trovato per anomalous duration for extremely high/low.</p>
                    <?php endif; ?>

                    <!-- Anomalous Frequency for Extremely High/Low -->
                    <br><h5 class="card-title mb-4">Anomalous Frequency for Extremely High/Low</h5>
                    <?php if(isset($data['anomalies_for_extremely_high_low_frequency']) && count($data['anomalies_for_extremely_high_low_frequency']) > 0): ?>
                        <div class="table-responsive">
                            <table id="datatable-anomalies-extremely-high-low-frequency" class="table table-bordered dt-responsive nowrap w-100">
                                <thead>
                                <tr>
                                    <th>Day</th>
                                    <th>Extremely High Count</th>
                                    <th>Extremely Low Count</th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php $__currentLoopData = $data['anomalies_for_extremely_high_low_frequency']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $frequency): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <?php
                                        // Inizializza valori predefiniti
                                        $extremelyHighCount = 'N/A';
                                        $extremelyLowCount = 'N/A';

                                        // Verifica se ci sono dettagli di frequenza
                                        if (isset($frequency['anomalous_frequency']) && $frequency['anomalous_frequency'] != 'N/A') {
                                            // Esplodi i dettagli
                                            $details = explode(', ', $frequency['anomalous_frequency']);

                                            foreach ($details as $detail) {
                                                $parts = explode(' ', $detail);
                                                $type = $parts[0] . ' ' . $parts[1];
                                                $count = $parts[2] ?? 'N/A';

                                                if ($type === 'Extremely high') {
                                                    $extremelyHighCount = $count;
                                                } elseif ($type === 'Extremely low') {
                                                    $extremelyLowCount = $count;
                                                }
                                            }
                                        }
                                    ?>
                                    <tr>
                                        <td><?php echo e($frequency['day'] ?? 'N/A'); ?></td>
                                        <td><?php echo e($extremelyHighCount); ?></td>
                                        <td><?php echo e($extremelyLowCount); ?></td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>


<?php $__env->stopSection(); ?>

<?php $__env->startSection('script'); ?>
    <!-- apexcharts -->
    <script>
            $(document).ready(function() {
            $('#datatable-anomalous-duration').DataTable();
            $('#datatable-anomalous-frequency').DataTable();
            $('#datatable-glycemic-swings').DataTable();
            $('#datatable-swings-followed-by-duration').DataTable();
            $('#datatable-swings-followed-by-frequency').DataTable();
            $('#datatable-anomalies-extremely-high-low-duration').DataTable();
            $('#datatable-anomalies-extremely-high-low-frequency').DataTable();
        });

    </script>
    <script src="<?php echo e(URL::asset('/assets/libs/apexcharts/apexcharts.min.js')); ?>"></script>

    <!-- project-overview init -->
    <script src="<?php echo e(URL::asset('/assets/js/pages/project-overview.init.js')); ?>"></script>

    <!-- bootstrap datepicker -->
    <script src="<?php echo e(URL::asset('/assets/libs/bootstrap-datepicker/bootstrap-datepicker.min.js')); ?>"></script>
    <!-- dropzone plugin -->
    <script src="<?php echo e(URL::asset('/assets/libs/dropzone/dropzone.min.js')); ?>"></script>

    <!-- Required datatable js -->
    <script src="<?php echo e(URL::asset('/assets/libs/datatables/datatables.min.js')); ?>"></script>
    <script src="<?php echo e(URL::asset('/assets/libs/jszip/jszip.min.js')); ?>"></script>
    <script src="<?php echo e(URL::asset('/assets/libs/pdfmake/pdfmake.min.js')); ?>"></script>
    <!-- Datatable init js -->
    <script src="<?php echo e(URL::asset('/assets/js/pages/datatables.init.js')); ?>"></script>

<?php $__env->stopSection(); ?>


<?php echo $__env->make('layouts.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /Users/lorenzotucceri/Library/Mobile Documents/com~apple~CloudDocs/Università/Magistrale/1° anno/Secondo semestre/Intelligent knowledge/Progetto/ISEQL-Glucose Analyzer/Web/resources/views/patientDetails.blade.php ENDPATH**/ ?>
