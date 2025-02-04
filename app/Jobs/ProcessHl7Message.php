<?php
namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProcessHl7Message implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $id;

    public function __construct($id)
    {
        $this->id = $id;
    }

    public function handle()
    {
        $rawData = DB::table('vital_signs_im3_raw')
            ->where('parsed', false)
            ->orderBy('id', 'asc')
            ->first();

        if (!$rawData) {
            Log::info("No new HL7 messages to process.");
            return;
        }

        Log::info("Processing HL7 Message ID: {$rawData->id}");

        $parsedData = $this->parseHl7ToJson($rawData->raw_message);

        if (!empty($parsedData['patient_id']) && !empty($parsedData['machine_timestamp'])) {
            $this->insertPatientData($parsedData, $rawData->id);

            DB::table('vital_signs_im3_raw')
                ->where('id', $rawData->id)
                ->update(['parsed' => true]);

            Log::info("HL7 Message ID {$rawData->id} successfully processed.");
        } else {
            Log::warning("HL7 parsing failed for ID {$rawData->id}, missing required fields.");
        }
    }

    private function parseMSH($segment)
    {
        $fields = explode("|", $segment);
        return [
            'machine_timestamp' => isset($fields[6]) ? Carbon::createFromFormat('YmdHis', trim($fields[6]))->format('Y-m-d H:i:s') : null,
        ];
    }

    private function parsePID($segment)
    {
        $fields = explode("|", $segment);
        return [
            'patient_id' => isset($fields[3]) && !empty(trim($fields[3])) ? trim($fields[3]) : null,
            'patient_name' => isset($fields[5]) ? str_replace("^", " ", trim($fields[5])) : null,
        ];
    }

    private function parseOBX($segment, &$patientData)
    {
        $fields = explode("|", $segment);
        $obxType = trim($fields[3] ?? '');
        $obxValue = trim($fields[5] ?? '');
        $obxUnit = trim($fields[6] ?? '');
        $obxRange = trim($fields[7] ?? '');

        if (!$obxType || !$obxValue) {
            Log::warning("Skipping OBX segment due to missing type or value: " . json_encode($fields));
            return;
        }

        if (!empty($obxUnit)) {
            $obxUnitParts = explode('^', $obxUnit);
            $cleanedObxUnit = count($obxUnitParts) > 1 ? $obxUnitParts[1] : $obxUnitParts[0];

            if (is_numeric($cleanedObxUnit)) {
                $cleanedObxUnit = prev($obxUnitParts);
            }
        } else {
            $cleanedObxUnit = null;
        }

        if (strpos($obxRange, '-') !== false) {
            list($lowerRange, $upperRange) = explode('-', $obxRange);
        } else {
            $lowerRange = null;
            $upperRange = null;
        }

        $unitMappings = [
            'MDC_DIM_KILO_G' => 'kg',
            'kg' => 'kg',
            'MDC_DIM_CENTI_M' => 'cm',
            'cm' => 'cm',
            'MDC_DIM_PERCENT' => '%',
            '%' => '%',
            'MDC_DIM_MMHG' => 'mmHg',
            'mmHg' => 'mmHg',
            'MDC_DIM_BEAT_PER_MIN' => 'bpm',
            'bpm' => 'bpm',
            'MDC_DIM_RESP_PER_MIN' => 'rpm',
            'rpm' => 'rpm',
            'MDC_DIM_DEGC' => '°C',
            '°C' => '°C',
            'C' => '°C',
        ];

        $convertedUnit = $unitMappings[$cleanedObxUnit] ?? $cleanedObxUnit;

        switch ($obxType) {
            case "RR":
                $patientData['respiratory_rate'] = $obxValue;
                $patientData['respiratory_rate_unit'] = $convertedUnit;
                $patientData['respiratory_rate_lower_range'] = $lowerRange;
                $patientData['respiratory_rate_upper_range'] = $upperRange;
                break;

            case "Consciousness":
                $patientData['consciousness'] = $obxValue;
                break;

            case "Oxygen":
                $patientData['oxygen'] = $obxValue;
                break;

            case "Pain":
                $patientData['pain'] = $obxValue;
                break;

            case "Weight":
                $patientData['weight'] = $obxValue;
                $patientData['weight_unit'] = $convertedUnit;
                break;

            case "Height":
                $patientData['height'] = $obxValue;
                $patientData['height_unit'] = $convertedUnit;
                break;

            case "BMI":
                $patientData['bmi'] = $obxValue;
                break;

            case "SpO2":
            case "MDC_PULS_O	`XIM_SAT_O2":
            case "150456^MDC_PULS_O	`XIM_SAT_O2^MDC":
                $patientData['spo2'] = $obxValue;
                $patientData['spo2_unit'] = $convertedUnit;
                $patientData['spo2_lower_range'] = $lowerRange;
                $patientData['spo2_upper_range'] = $upperRange;
                break;

            case "SpO2_PR":
            case "MDC_PULS_RATE":
            case "149514^MDC_PULS_RATE^MDC":
                $patientData['spo2_pulse_rate'] = $obxValue;
                $patientData['spo2_pulse_rate_unit'] = $convertedUnit;
                $patientData['spo2_pulse_rate_lower_range'] = $lowerRange;
                $patientData['spo2_pulse_rate_upper_range'] = $upperRange;
                break;

            case "NIBP_SYS":
            case "MDC_PRESS_BLD_NONINV_SYS":
            case "150021^MDC_PRESS_BLD_NONINV_SYS^MDC":
                $patientData['nibp_systolic'] = $obxValue;
                $patientData['nibp_systolic_unit'] = $convertedUnit;
                $patientData['nibp_systolic_lower_range'] = $lowerRange;
                $patientData['nibp_systolic_upper_range'] = $upperRange;
                break;

            case "NIBP_DIA":
            case "MDC_PRESS_BLD_NONINV_DIA":
            case "150022^MDC_PRESS_BLD_NONINV_DIA^MDC":
                $patientData['nibp_diastolic'] = $obxValue;
                $patientData['nibp_diastolic_unit'] = $convertedUnit;
                $patientData['nibp_diastolic_lower_range'] = $lowerRange;
                $patientData['nibp_diastolic_upper_range'] = $upperRange;
                break;

            case "NIBP_MAP":
            case "MDC_PRESS_BLD_NONINV_MEAN":
            case "150023^MDC_PRESS_BLD_NONINV_MEAN^MDC":
                $patientData['nibp_mean'] = $obxValue;
                $patientData['nibp_mean_unit'] = $convertedUnit;
                $patientData['nibp_mean_lower_range'] = $lowerRange;
                $patientData['nibp_mean_upper_range'] = $upperRange;
                break;

            case "TEMP":
            case "QUICKTEMP_TEMP":
                $patientData['temperature'] = $obxValue;
                $patientData['temperature_unit'] = $convertedUnit;
                $patientData['temperature_lower_range'] = $lowerRange;
                $patientData['temperature_upper_range'] = $upperRange;
                break;
        }
    }

    private function parseHl7ToJson($hl7Message)
    {
        $segments = explode("\r", trim($hl7Message));
        $patientData = [
            'machine_timestamp' => null,
            'no_rawat' => null,
            'tgl_perawatan' => null,
            'jam_rawat' => null,
            'patient_id' => null,
            'patient_name' => null,

            'respiratory_rate' => null,
            'respiratory_rate_unit' => null,
            'respiratory_rate_lower_range' => null,
            'respiratory_rate_upper_range' => null,

            'consciousness' => null,
            'oxygen' => null,
            'pain' => null,
            'weight' => null,
            'weight_unit' => null,
            'height' => null,
            'height_unit' => null,
            'bmi' => null,

            'spo2' => null,
            'spo2_unit' => null,
            'spo2_lower_range' => null,
            'spo2_upper_range' => null,

            'spo2_pulse_rate' => null,
            'spo2_pulse_rate_unit' => null,
            'spo2_pulse_rate_lower_range' => null,
            'spo2_pulse_rate_upper_range' => null,

            'spo2_respiratory_rate' => null,
            'spo2_respiratory_rate_unit' => null,
            'spo2_respiratory_rate_lower_range' => null,
            'spo2_respiratory_rate_upper_range' => null,

            'nibp_systolic' => null,
            'nibp_systolic_unit' => null,
            'nibp_systolic_lower_range' => null,
            'nibp_systolic_upper_range' => null,

            'nibp_diastolic' => null,
            'nibp_diastolic_unit' => null,
            'nibp_diastolic_lower_range' => null,
            'nibp_diastolic_upper_range' => null,

            'nibp_mean' => null,
            'nibp_mean_unit' => null,
            'nibp_mean_lower_range' => null,
            'nibp_mean_upper_range' => null,

            'temperature' => null,
            'temperature_unit' => null,
            'temperature_lower_range' => null,
            'temperature_upper_range' => null,
        ];

        foreach ($segments as $segment) {
            if (strpos($segment, "MSH|") === 0) {
                $patientData = array_merge($patientData, $this->parseMSH($segment));
            } elseif (strpos($segment, "PID|") === 0) {
                $patientData = array_merge($patientData, $this->parsePID($segment));
            } elseif (strpos($segment, "OBX|") === 0) {
                $this->parseOBX($segment, $patientData);
            }
        }

        return $patientData;
    }

    private function insertPatientData($patientData)
    {
        $currentDateTime = Carbon::now();
        $no_rawat = $currentDateTime->format('Ymd_His');
        $tgl_perawatan = $currentDateTime->toDateString();
        $jam_rawat = $currentDateTime->format('H:i:s');


        DB::table('vital_signs_im3_json')->insert([
            'machine_timestamp' => $patientData['machine_timestamp'],
            'no_rawat' => $no_rawat,
            'tgl_perawatan' => $tgl_perawatan,
            'jam_rawat' => $jam_rawat,
            'created_at' => now(),
            'updated_at' => now(),

            'patient_id' => $patientData['patient_id'],
            'patient_name' => $patientData['patient_name'],

            'respiratory_rate' => $patientData['respiratory_rate'],
            'respiratory_rate_unit' => $patientData['respiratory_rate_unit'],
            'respiratory_rate_lower_range' => $patientData['respiratory_rate_lower_range'],
            'respiratory_rate_upper_range' => $patientData['respiratory_rate_upper_range'],

            'consciousness' => $patientData['consciousness'],
            'oxygen' => $patientData['oxygen'],
            'pain' => $patientData['pain'],
            'weight' => $patientData['weight'],
            'weight_unit' => $patientData['weight_unit'],
            'height' => $patientData['height'],
            'height_unit' => $patientData['height_unit'],
            'bmi' => $patientData['bmi'],

            'spo2' => $patientData['spo2'],
            'spo2_unit' => $patientData['spo2_unit'],
            'spo2_lower_range' => $patientData['spo2_lower_range'],
            'spo2_upper_range' => $patientData['spo2_upper_range'],

            'spo2_pulse_rate' => $patientData['spo2_pulse_rate'],
            'spo2_pulse_rate_unit' => $patientData['spo2_pulse_rate_unit'],
            'spo2_pulse_rate_lower_range' => $patientData['spo2_pulse_rate_lower_range'],
            'spo2_pulse_rate_upper_range' => $patientData['spo2_pulse_rate_upper_range'],

            'spo2_respiratory_rate' => $patientData['spo2_respiratory_rate'],
            'spo2_respiratory_rate_unit' => $patientData['spo2_respiratory_rate_unit'],
            'spo2_respiratory_rate_lower_range' => $patientData['spo2_respiratory_rate_lower_range'],
            'spo2_respiratory_rate_upper_range' => $patientData['spo2_respiratory_rate_upper_range'],

            'nibp_systolic' => $patientData['nibp_systolic'],
            'nibp_systolic_unit' => $patientData['nibp_systolic_unit'],
            'nibp_systolic_lower_range' => $patientData['nibp_systolic_lower_range'],
            'nibp_systolic_upper_range' => $patientData['nibp_systolic_upper_range'],

            'nibp_diastolic' => $patientData['nibp_diastolic'],
            'nibp_diastolic_unit' => $patientData['nibp_diastolic_unit'],
            'nibp_diastolic_lower_range' => $patientData['nibp_diastolic_lower_range'],
            'nibp_diastolic_upper_range' => $patientData['nibp_diastolic_upper_range'],

            'nibp_mean' => $patientData['nibp_mean'],
            'nibp_mean_unit' => $patientData['nibp_mean_unit'],
            'nibp_mean_lower_range' => $patientData['nibp_mean_lower_range'],
            'nibp_mean_upper_range' => $patientData['nibp_mean_upper_range'],

            'temperature' => $patientData['temperature'],
            'temperature_unit' => $patientData['temperature_unit'],
            'temperature_lower_range' => $patientData['temperature_lower_range'],
            'temperature_upper_range' => $patientData['temperature_upper_range'],
        ]);

        Log::info("Inserted data for patient ID: " . $patientData['patient_id']);
    }
}
?>
