<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class VitalSignIm3Json extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'vital_signs_im3_json';

    protected $fillable = [
        "machine_timestamp",
        "no_rawat",
        "tgl_perawatan",
        "jam_rawat",

        "patient_id",
        "patient_name",

        "respiratory_rate",
        "respiratory_rate_unit",
        "respiratory_rate_lower_range",
        "respiratory_rate_upper_range",

        "consciousness",
        "oxygen",
        "pain",
        "weight",
        "weight_unit",
        "height",
        "height_unit",
        "bmi",

        "spo2",
        "spo2_unit",
        "spo2_lower_range",
        "spo2_upper_range",

        "spo2_pulse_rate",
        "spo2_pulse_rate_unit",
        "spo2_pulse_rate_lower_range",
        "spo2_pulse_rate_upper_range",

        "spo2_respiratory_rate",
        "spo2_respiratory_rate_unit",
        "spo2_respiratory_rate_lower_range",
        "spo2_respiratory_rate_upper_range",

        "nibp_systolic",
        "nibp_systolic_unit",
        "nibp_systolic_lower_range",
        "nibp_systolic_upper_range",

        "nibp_diastolic",
        "nibp_diastolic_unit",
        "nibp_diastolic_lower_range",
        "nibp_diastolic_upper_range",

        "nibp_mean",
        "nibp_systolic_unit",
        "nibp_mean_lower_range",
        "nibp_mean_upper_range",

        "temperature",
        "temperature_unit",
        "temperature_lower_range",
        "temperature_upper_range",
    ];
}
