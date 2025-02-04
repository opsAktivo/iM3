<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('vital_signs_im3_json', function (Blueprint $table) {
            $table->id();
            $table->string('machine_timestamp');

            $table->string('patient_id')->nullable();
            $table->string('patient_name')->nullable();

            $table->unsignedInteger('respiratory_rate')->nullable();
            $table->string('respiratory_rate_unit')->nullable();
            $table->unsignedInteger('respiratory_rate_lower_range')->nullable();
            $table->unsignedInteger('respiratory_rate_upper_range')->nullable();

            $table->string('consciousness')->nullable();
            $table->string('oxygen')->nullable();
            $table->unsignedInteger('pain')->nullable();
            $table->unsignedFloat('weight')->nullable();
            $table->string('weight_unit')->nullable();
            $table->unsignedFloat('height')->nullable();
            $table->string('height_unit')->nullable();
            $table->unsignedFloat('bmi')->nullable();

            $table->unsignedInteger('spo2')->nullable();
            $table->string('spo2_unit')->nullable();
            $table->unsignedInteger('spo2_lower_range')->nullable();
            $table->unsignedInteger('spo2_upper_range')->nullable();

            $table->unsignedInteger('spo2_pulse_rate')->nullable();
            $table->string('spo2_pulse_rate_unit')->nullable();
            $table->unsignedInteger('spo2_pulse_rate_lower_range')->nullable();
            $table->unsignedInteger('spo2_pulse_rate_upper_range')->nullable();

            $table->unsignedInteger('spo2_respiratory_rate')->nullable();
            $table->string('spo2_respiratory_rate_unit')->nullable();
            $table->unsignedInteger('spo2_respiratory_rate_lower_range')->nullable();
            $table->unsignedInteger('spo2_respiratory_rate_upper_range')->nullable();

            $table->unsignedInteger('nibp_systolic')->nullable();
            $table->string('nibp_systolic_unit')->nullable();
            $table->unsignedInteger('nibp_systolic_lower_range')->nullable();
            $table->unsignedInteger('nibp_systolic_upper_range')->nullable();

            $table->unsignedInteger('nibp_diastolic')->nullable();
            $table->string('nibp_diastolic_unit')->nullable();
            $table->unsignedInteger('nibp_diastolic_lower_range')->nullable();
            $table->unsignedInteger('nibp_diastolic_upper_range')->nullable();

            $table->unsignedInteger('nibp_mean')->nullable();
            $table->string('nibp_mean_unit')->nullable();
            $table->unsignedInteger('nibp_mean_lower_range')->nullable();
            $table->unsignedInteger('nibp_mean_upper_range')->nullable();

            $table->unsignedFloat('temperature')->nullable();
            $table->string('temperature_unit')->nullable();
            $table->unsignedFloat('temperature_lower_range')->nullable();
            $table->unsignedFloat('temperature_upper_range')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vital_signs_im3_json');
    }
};
