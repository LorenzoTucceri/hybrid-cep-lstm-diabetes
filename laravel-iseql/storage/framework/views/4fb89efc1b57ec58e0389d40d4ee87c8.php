<!DOCTYPE html>
<html>
<head>
    <title>Patient Details</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            font-size: 14px;
            color: #333;
            background-color: #f9f9f9;
        }

        h1, h2, h3, h4 {
            margin: 0;
            padding: 10px 0;
            font-size: 18px;
            color: #333;
        }

        h2 {
            font-size: 16px;
            border-bottom: 1px solid #ddd;
            padding-bottom: 5px;
        }

        h3 {
            font-size: 14px;
            border-bottom: 1px solid #ddd;
            padding-bottom: 5px;
        }

        p {
            font-size: 14px;
            margin: 5px 0;
        }

        .section {
            margin-bottom: 20px;
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 15px;
        }

        .section-header {
            font-size: 16px;
            font-weight: bold;
            color: #333;
            margin-bottom: 10px;
        }

        .section-content {
            font-size: 14px;
        }

        .section-content ul {
            list-style-type: none;
            padding-left: 0;
        }

        .section-content ul li {
            margin-bottom: 8px;
            padding: 5px;
            border-left: 2px solid #3498db;
            background-color: #f2f2f2;
        }

        .section-content ul li:nth-child(odd) {
            background-color: #fff;
        }

        .table-responsive {
            width: 100%;
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        th, td {
            padding: 8px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        th {
            background-color: #f2f2f2;
        }

        @media print {
            body {
                background-color: #fff;
                margin: 0;
            }

            .section {
                border: none;
                border-radius: 0;
                padding: 10px;
                margin: 0;
                page-break-inside: avoid;
            }

            table {
                page-break-inside: auto;
            }

            thead {
                display: table-header-group;
            }

            tbody {
                display: table-row-group;
            }

            tfoot {
                display: table-footer-group;
            }
        }
    </style>
</head>
<body>
<h1>Patient Details</h1>


<!-- Patient Information Section -->
<div class="section" id="details" <?php if($detail!="on"): ?> style="display: none" <?php endif; ?>>
    <h2>Name: <?php echo e($client->name ?? 'Not Specified'); ?> <?php echo e($client->surname ?? 'Not Specified'); ?></h2>
    <p>Email: <?php echo e($client->email ?? 'Not Specified'); ?></p>
    <p>Date of
        Birth: <?php echo e($client->birth ? \Carbon\Carbon::parse($client->birth)->format('Y/m/d') : 'Not Specified'); ?></p>
    <p>Phone Number: <?php echo e($client->telephone_number ?? 'Not Specified'); ?></p>
    <p>Address: <?php echo e($client->address ?: 'Not Specified'); ?></p>
    <p>Gender: <?php echo e($client->gender ?? 'Not Specified'); ?></p>
    <p>Analysis date: From
        <?php echo e(request('start_date') ? \Carbon\Carbon::parse(request('start_date'))->format('Y/m/d') : ($data['start_time'] ? \Carbon\Carbon::parse($data['start_time'])->format('Y/m/d') : 'Not Specified')); ?>

        to
        <?php echo e(request('end_date') ? \Carbon\Carbon::parse(request('end_date'))->format('Y/m/d') : ($data['end_time'] ? \Carbon\Carbon::parse($data['end_time'])->format('Y/m/d') : 'Not Specified')); ?>

    </p>
</div>

<!-- Analysis Summary Section -->
<div class="section" id="summary" <?php if($summary!="on"): ?> style="display: none" <?php endif; ?>>
    <div class="section-header">Analysis Summary</div>

    <!-- Totals and Durations Section -->
    <div class="section-content">
        <h3>Glycemic Trends</h3>
        <p><strong>Average Glucose:</strong> <?php echo e(round($data['avg'],2) ?? 'N/A'); ?></p>
        <p><strong>GMI:</strong> <?php echo e(round($data['gmi'],2)."%"?? 'N/A'); ?>

            <?php if($data['gmi'] < 7): ?>
                <span
                    style="display:inline-block; width:10px; height:10px; background-color:green; border-radius:50%; margin-left:5px;"></span>
            <?php elseif($data['gmi'] >= 7 && $data['gmi'] <= 8): ?>
                <span
                    style="display:inline-block; width:10px; height:10px; background-color:orange; border-radius:50%; margin-left:5px;"></span>
            <?php else: ?>
                <span
                    style="display:inline-block; width:10px; height:10px; background-color:red; border-radius:50%; margin-left:5px;"></span>
            <?php endif; ?>
        </p>

        <h3>Totals and Durations</h3>
        <p><strong>Extremely High:</strong> <?php echo e($data['totals_and_durations']['totals']['extremely_high'] ?? 'N/A'); ?></p>
        <p><strong>Extremely Low:</strong> <?php echo e($data['totals_and_durations']['totals']['extremely_low'] ?? 'N/A'); ?></p>
        <p><strong>High:</strong> <?php echo e($data['totals_and_durations']['totals']['high'] ?? 'N/A'); ?></p>
        <p><strong>Low:</strong> <?php echo e($data['totals_and_durations']['totals']['low'] ?? 'N/A'); ?></p>
        <p><strong>Normal:</strong> <?php echo e($data['totals_and_durations']['totals']['normal'] ?? 'N/A'); ?></p>

        <p><strong>Duration (hours):</strong></p>
        <ul>
            <li><strong>Extremely
                    High:</strong> <?php echo e(number_format((float)($data['totals_and_durations']['durations_hhmm']['extremely_high'] ?? 0), 2)); ?>

            </li>
            <li><strong>Extremely
                    Low:</strong> <?php echo e(number_format((float)($data['totals_and_durations']['durations_hhmm']['extremely_low'] ?? 0), 2)); ?>

            </li>
            <li>
                <strong>High:</strong> <?php echo e(number_format((float)($data['totals_and_durations']['durations_hhmm']['high'] ?? 0), 2)); ?>

            </li>
            <li>
                <strong>Low:</strong> <?php echo e(number_format((float)($data['totals_and_durations']['durations_hhmm']['low'] ?? 0), 2)); ?>

            </li>
            <li>
                <strong>Normal:</strong> <?php echo e(number_format((float)($data['totals_and_durations']['durations_hhmm']['normal'] ?? 0), 2)); ?>

            </li>
        </ul>
    </div>

    <!-- Glycemic Swings Stats Section -->
    <div class="section-content">
        <h3>Time Swings Statistics</h3>
        <p><strong>Total Time
                Swing:</strong> <?php echo e($data['totals_and_durations']['time_swings_stats']['total_time_swings'] ?? 'N/A'); ?>

        </p>
        <p><strong>Total Time Swing With Too Long Glucose
                Anomalies:</strong> <?php echo e($data['totals_and_durations']['time_swings_stats']['total_time_swing_too_long'] ?? 'N/A'); ?>

        </p>
        <p><strong>Percentage of Time Swing With Too Long Glucose
                Anomalies:</strong> <?php echo e(number_format((float)($data['totals_and_durations']['time_swings_stats']['percentage_time_swing_too_long'] ?? 0), 2)); ?>

            %</p>
        <p><strong>Percentage of Too Frequent Time
                Swings:</strong> <?php echo e(number_format((float)($data['totals_and_durations']['time_swings_stats']['percentage_too_frequent_time_swings'] ?? 0), 2)); ?>

            %</p>
        <p><strong>Total Day of Too Frequent Time
                Swings:</strong> <?php echo e($data['totals_and_durations']['time_swings_stats']['total_too_frequent_time_swings'] ?? 'N/A'); ?>

        </p>
    </div>

    <!-- Max Anomalous Day Section -->
    <div class="section-content">
        <h3>Max Anomalous Day</h3>
        <p><strong>Date:</strong> <?php echo e($data['totals_and_durations']['max_anomalous_day']['date'] ?? 'N/A'); ?></p>
        <ul>
            <li><strong>Extremely High
                    Count:</strong> <?php echo e($data['totals_and_durations']['max_anomalous_day']['details']['Extremely High Count'] ?? 'N/A'); ?>

            </li>
            <li><strong>Extremely Low
                    Count:</strong> <?php echo e($data['totals_and_durations']['max_anomalous_day']['details']['Extremely Low Count'] ?? 'N/A'); ?>

            </li>
            <li><strong>High
                    Count:</strong> <?php echo e($data['totals_and_durations']['max_anomalous_day']['details']['High Count'] ?? 'N/A'); ?>

            </li>
            <li><strong>Low
                    Count:</strong> <?php echo e($data['totals_and_durations']['max_anomalous_day']['details']['Low Count'] ?? 'N/A'); ?>

            </li>
            <li><strong>Total
                    Count:</strong> <?php echo e($data['totals_and_durations']['max_anomalous_day']['details']['Total Count'] ?? 'N/A'); ?>

            </li>
        </ul>
    </div>
</div>

<!-- Analysis Results Section -->
<div class="section">


    <div class="section-header"
         <?php if(!$time_swing && !$too_long && !$too_frequent && !$too_frequent_time_swing && !$time_swing_too_long): ?> style="display: none" <?php endif; ?>>
        Analysis Results
    </div>

    <!-- Time Swings Table -->
    <div class="section-content" id="time_swing" <?php if(!$time_swing): ?> style="display: none" <?php endif; ?>>
        <h3>Time Swing</h3>
        <?php if(isset($data['time_swing']) && count($data['time_swing']) > 0): ?>
            <div class="table-responsive">
                <table id="datatable-glycemic-swings">
                    <thead>
                    <tr>
                        <th>Day</th>
                        <th>First Event</th>
                        <th>Second Event</th>
                        <th>Duration Time Swing (h)</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php $__currentLoopData = $data['time_swing']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $swing): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
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
            <p class="text-muted">No data found for time swings.</p>
        <?php endif; ?>
        <br>
        <h3>Time Swing Chart</h3>
        <img src="<?php echo e($imagePaths["glycemicSwingsChart"]); ?>" style="width: 100%; max-width: 600px;">
    </div>

    <!-- Time Swing Duration Table -->
    <div class="section-content" id="too_long" <?php if(!$too_long): ?> style="display: none" <?php endif; ?>>
        <h3>Too Long Glucose Anomalies</h3>
        <?php if(isset($data['too_long_glucose_anomalies']) && count($data['too_long_glucose_anomalies']) > 0): ?>
            <div class="table-responsive">
                <table id="datatable-swings-followed-by-duration">
                    <thead>
                    <tr>
                        <th>Day</th>
                        <th>Event</th>
                        <th>Start Time</th>
                        <th>End Time</th>
                        <th>Durations (h)</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php $__currentLoopData = $data['too_long_glucose_anomalies']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $swing): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr>
                            <td><?php echo e($swing['day'] ?? 'N/A'); ?></td>
                            <td><?php echo e($swing['event'] ?? 'N/A'); ?></td>
                            <td><?php echo e($swing['start_time'] ?? 'N/A'); ?></td>
                            <td><?php echo e($swing['end_time'] ?? 'N/A'); ?></td>
                            <td><?php echo e($swing['duration'] ?? 'N/A'); ?></td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <p class="text-muted">No data found for swings followed by anomalous duration.</p>
        <?php endif; ?>
        <br>
        <h3>Too Long Glucose Anomalies Chart</h3>
        <img src="<?php echo e($imagePaths["tooLongChart"]); ?>" style="width: 100%; max-width: 600px;">

    </div>

    <!-- Anomalous Frequency Table -->
    <div class="section-content" id="too_frequent" <?php if(!$too_frequent): ?> style="display: none" <?php endif; ?>>
        <h3>Too Frequent Glucose Anomalies</h3>
        <?php if(isset($data['too_frequent_glucose_anomalies']) && count($data['too_frequent_glucose_anomalies']) > 0): ?>
            <div class="table-responsive">
                <table id="datatable-anomalous-frequency">
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
                    <?php $__currentLoopData = $data['too_frequent_glucose_anomalies']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $frequency): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
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
            <p class="text-muted">No data found for anomalous frequency.</p>
        <?php endif; ?>
        <br>
        <h3>Too Frequent Glucose Anomalies Chart</h3>
        <img src="<?php echo e($imagePaths["tooFrequentChart"]); ?>" style="width: 100%; max-width: 600px;">
    </div>

    <!-- Time Swing Too Frequent Table -->
    <div class="section-content" id="too_frequent_time_swing"
         <?php if(!$too_frequent_time_swing): ?> style="display: none" <?php endif; ?>>
        <h3>Too Frequent Time Swings</h3>
        <?php if(isset($data['too_frequent_time_swings']) && count($data['too_frequent_time_swings']) > 0): ?>
            <div class="table-responsive">
                <table id="datatable-swings-followed-by-frequency">
                    <thead>
                    <tr>
                        <th>Day</th>
                        <th>First Swing Event</th>
                        <th>Second Swing Event</th>
                        <th>Duration Time Swing (h)</th>
                        <th>Frequency</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php $__currentLoopData = $data['too_frequent_time_swings']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $swing): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr>
                            <td><?php echo e($swing['Events'][0]['Day'] ?? 'N/A'); ?></td>
                            <td><?php echo e($swing['Events'][0]['First event'] ?? 'N/A'); ?></td>
                            <td><?php echo e($swing['Events'][0]['Second event'] ?? 'N/A'); ?></td>
                            <td><?php echo e($swing['Events'][0]['Duration time swing'] ?? 'N/A'); ?></td>
                            <td><?php echo e($swing['Number of Time Swings'] ?? 'N/A'); ?></td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <p class="text-muted">No data found for time swings too frequent.</p>
        <?php endif; ?>
        <br>
        <h3>Too Frequent Time Swings Chart</h3>
        <img src="<?php echo e($imagePaths["tooFrequentTimeSwingsDurationChart"]); ?>" style="width: 100%; max-width: 600px;">
        <br>
        <img src="<?php echo e($imagePaths["tooFrequentTimeSwingsFrequencyChart"]); ?>" style="width: 100%; max-width: 600px;">

    </div>

    <!-- Time Swing With Too Long Glucose Anomalies Table -->
    <div class="section-content" id="time_swing_too_long" <?php if(!$time_swing_too_long): ?> style="display: none" <?php endif; ?>>
        <h3>Time Swing With Too Long Glucose Anomalies</h3>
        <?php if(isset($data['time_swing_with_too_long_glucose_anomalies']) && count($data['time_swing_with_too_long_glucose_anomalies']) > 0): ?>
            <div class="table-responsive">
                <table id="datatable-swings-followed-by-duration">
                    <thead>
                    <tr>
                        <th>Day</th>
                        <th>First Swing Event</th>
                        <th>Second Swing Event</th>
                        <th>Duration Time Swing (h)</th>
                        <th>Durations (h)</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php $__currentLoopData = $data['time_swing_with_too_long_glucose_anomalies']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $swing): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
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
            <p class="text-muted">No data found for swings followed by anomalous duration.</p>
        <?php endif; ?>
        <br>
        <h3>Time Swing With Too Long Glucose Anomalies<Chart</h3>
        <img src="<?php echo e($imagePaths["timeSwingTooLongChart"]); ?>" style="width: 100%; max-width: 600px;">
    </div>
</div>
<?php /**PATH /Users/lorenzotucceri/Progetti/ISEQL/laravel-iseql/resources/views/pdf/patient-details.blade.php ENDPATH**/ ?>