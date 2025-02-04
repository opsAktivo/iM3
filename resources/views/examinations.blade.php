<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Examination Results</title>
    <link href="{{ asset('css/style.css') }}" rel="stylesheet">
</head>

<body>
    <div class="container">
        <h2 class="text-center mt-4 mb-5">iM3 Examination Results</h2>

        <table class="center-table">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Examination Time</th>
                    <th>Patient ID</th>
                    <th>Patient Name</th>
                    <th>Actions</th>
                </tr>
            </thead>

            <tbody>
                @foreach ($examinationResults as $index => $result)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $result->machine_timestamp }}</td>
                        <td>{{ $result->patient_id }}</td>
                        <td>{{ $result->patient_name }}</td>
                        <td>
                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modal-{{ $result->id }}">
                                View Details
                            </button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    @foreach ($examinationResults as $index => $result)
    <div class="modal fade" id="modal-{{ $result->id }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-3">Examination Details</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    <table class="mb-4">
                        <tbody>
                            <tr>
                                <td>Examination Time:</td>
                                <th>&nbsp;&nbsp;{{ $result->machine_timestamp }}</th>
                            </tr>
                        </tbody>

                        <tbody>
                            <tr>
                                <td>Patient ID:</td>
                                <th>&nbsp;&nbsp;{{ $result->patient_id }}</th>
                            </tr>
                        </tbody>

                        <tbody>
                            <tr>
                                <td>Patient Name:</td>
                                <th>&nbsp;&nbsp;{{ $result->patient_name }}</th>
                            </tr>
                        </tbody>
                    </table>

                    <table class="table">
                        <thead>
                            <tr>
                                <th>Examination</th>
                                <th class="text-center">Result</th>
                                <th class="text-center">Normal Range</th>
                                <th class="text-center">Examination Result</th>
                            </tr>
                        </thead>

                        <tbody>
                            <tr>
                                <td>Consciousness</td>
                                <td class="text-center">{{ $result->consciousness ?? '-' }}</td>
                                <td class="text-center">-</td>
                                <td class="text-center">-</td>
                            </tr>

                            <tr>
                                <td>Oxygen</td>
                                <td class="text-center">{{ $result->oxygen ?? '-' }}</td>
                                <td class="text-center">-</td>
                                <td class="text-center">-</td>
                            </tr>

                            <tr>
                                <td>Pain</td>
                                <td class="text-center">{{ $result->pain ?? '-' }}</td>
                                <td class="text-center">-</td>
                                <td class="text-center">-</td>
                            </tr>

                            <tr>
                                <th colspan="100%">Height, Weight, and BMI</th>
                            </tr>

                            <tr>
                                <td>- Weight</td>
                                <td class="text-center">{{ $result->weight ?? '-' }} {{ $result->weight_unit ?? '' }}</td>
                                <td class="text-center">-</td>
                                <td class="text-center">-</td>
                            </tr>

                            <tr>
                                <td>- Height</td>
                                <td class="text-center">{{ $result->height ?? '-' }} {{ $result->height_unit ?? '' }}</td>
                                <td class="text-center">-</td>
                                <td class="text-center">-</td>
                            </tr>

                            <tr>
                                <td>- BMI</td>
                                <td class="text-center">{{ $result->bmi ?? '-' }}</td>
                                <td class="text-center">-</td>
                                <td class="text-center">-</td>
                            </tr>

                            <tr>
                                <th colspan="100%">Blood Oxygen (SpO2)</th>
                            </tr>

                            <tr>
                                <td>- SpO2</td>
                                <td class="text-center">{{ $result->spo2 ?? '-' }} {{ $result->spo2_unit ?? '' }}</td>
                                <td class="text-center">{{ $result->spo2_lower_range . ' -' ?? '-' }} {{ $result->spo2_upper_range ?? '' }}</td>
                                <td class="text-center">
                                    @if($result->spo2 && $result->spo2_lower_range !== null && $result->spo2_upper_range !== null)
                                        @if($result->spo2 >= $result->spo2_lower_range && $result->spo2 <= $result->spo2_upper_range)
                                            <div style="color: #43A047; font-weight: 500">Normal</div>
                                        @else
                                            <div style="color: #DC362E; font-weight: 500">Out of Range</div>
                                        @endif
                                    @else
                                        -
                                    @endif
                                </td>
                            </tr>

                            <tr>
                                <td>- SpO2 Pulse Rate</td>
                                <td class="text-center">{{ $result->spo2_pulse_rate ?? '-' }} {{ $result->spo2_pulse_rate_unit ?? '' }}</td>
                                <td class="text-center">{{ $result->spo2_pulse_rate_lower_range . ' -' ?? '-' }} {{ $result->spo2_pulse_rate_upper_range ?? '' }}</td>
                                <td class="text-center">
                                    @if($result->spo2_pulse_rate && $result->spo2_pulse_rate_lower_range !== null && $result->spo2_pulse_rate_upper_range !== null)
                                        @if($result->spo2_pulse_rate >= $result->spo2_pulse_rate_lower_range && $result->spo2_pulse_rate <= $result->spo2_pulse_rate_upper_range)
                                            <div style="color: #43A047; font-weight: 500">Normal</div>
                                        @else
                                            <div style="color: #DC362E; font-weight: 500">Out of Range</div>
                                        @endif
                                    @else
                                        -
                                    @endif
                                </td>
                            </tr>

                            <tr>
                                <td>Respiratory Rate</td>
                                <td class="text-center">{{ $result->respiratory_rate ?? '-' }} {{ $result->respiratory_rate_unit ?? '' }}</td>
                                <td class="text-center">{{ $result->respiratory_rate_lower_range . ' -' ?? '-' }} {{ $result->respiratory_rate_upper_range ?? '' }}</td>
                                <td class="text-center">
                                    @if($result->respiratory_rate && $result->respiratory_rate_lower_range !== null && $result->respiratory_rate_upper_range !== null)
                                        @if($result->respiratory_rate >= $result->respiratory_rate_lower_range && $result->respiratory_rate <= $result->respiratory_rate_upper_range)
                                            <div style="color: #43A047; font-weight: 500">Normal</div>
                                        @else
                                            <div style="color: #DC362E; font-weight: 500">Out of Range</div>
                                        @endif
                                    @else
                                        -
                                    @endif
                                </td>
                            </tr>

                            <tr>
                                <th colspan="100%">Blood Pressure</th>
                            </tr>

                            <tr>
                                <td>- Systolic Blood Pressure</td>
                                <td class="text-center">{{ $result->nibp_systolic ?? '-' }} {{ $result->nibp_systolic_unit ?? '' }}</td>
                                <td class="text-center">{{ $result->nibp_systolic_lower_range . ' -' ?? '-' }} {{ $result->nibp_systolic_upper_range ?? '' }}</td>
                                <td class="text-center">
                                    @if($result->nibp_systolic && $result->nibp_systolic_lower_range !== null && $result->nibp_systolic_upper_range !== null)
                                        @if($result->nibp_systolic >= $result->nibp_systolic_lower_range && $result->nibp_systolic <= $result->nibp_systolic_upper_range)
                                            <div style="color: #43A047; font-weight: 500">Normal</div>
                                        @else
                                            <div style="color: #DC362E; font-weight: 500">Out of Range</div>
                                        @endif
                                    @else
                                        -
                                    @endif
                                </td>
                            </tr>

                            <tr>
                                <td>- Diastolic Blood Pressure</td>
                                <td class="text-center">{{ $result->nibp_diastolic ?? '-' }} {{ $result->nibp_diastolic_unit ?? '' }}</td>
                                <td class="text-center">{{ $result->nibp_diastolic_lower_range . ' -' ?? '-' }} {{ $result->nibp_diastolic_upper_range ?? '' }}</td>
                                <td class="text-center">
                                    @if($result->nibp_diastolic && $result->nibp_diastolic_lower_range !== null && $result->nibp_diastolic_upper_range !== null)
                                        @if($result->nibp_diastolic >= $result->nibp_diastolic_lower_range && $result->nibp_diastolic <= $result->nibp_diastolic_upper_range)
                                            <div style="color: #43A047; font-weight: 500">Normal</div>
                                        @else
                                            <div style="color: #DC362E; font-weight: 500">Out of Range</div>
                                        @endif
                                    @else
                                        -
                                    @endif
                                </td>
                            </tr>

                            <tr>
                                <td>- Mean Blood Pressure</td>
                                <td class="text-center">{{ $result->nibp_mean ?? '-' }} {{ $result->nibp_mean_unit ?? '' }}</td>
                                <td class="text-center">{{ $result->nibp_mean_lower_range . ' -' ?? '-' }} {{ $result->nibp_mean_upper_range ?? '' }}</td>
                                <td class="text-center">
                                    @if($result->nibp_mean && $result->nibp_mean_lower_range !== null && $result->nibp_mean_upper_range !== null)
                                        @if($result->nibp_mean >= $result->nibp_mean_lower_range && $result->nibp_mean <= $result->nibp_mean_upper_range)
                                            <div style="color: #43A047; font-weight: 500">Normal</div>
                                        @else
                                            <div style="color: #DC362E; font-weight: 500">Out of Range</div>
                                        @endif
                                    @else
                                        -
                                    @endif
                                </td>
                            </tr>

                            <tr>
                                <th colspan="100%">Body Temperature</th>
                            </tr>

                            <tr>
                                <td>- Temperature</td></td>
                                <td class="text-center">{{ $result->temperature ?? '-' }} {{ $result->temperature_unit ?? '' }}</td>
                                <td class="text-center">{{ $result->temperature_lower_range . ' -' ?? '-' }} {{ $result->temperature_upper_range ?? '' }}</td>
                                <td class="text-center">
                                    @if($result->temperature && $result->temperature_lower_range !== null && $result->temperature_upper_range !== null)
                                        @if($result->temperature >= $result->temperature_lower_range && $result->temperature <= $result->temperature_upper_range)
                                            <div style="color: #43A047; font-weight: 500">Normal</div>
                                        @else
                                            <div style="color: #DC362E; font-weight: 500">Out of Range</div>
                                        @endif
                                    @else
                                        -
                                    @endif
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    @endforeach

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
