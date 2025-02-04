<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class VitalSignIm3Raw extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'vital_signs_im3_raw';

    protected $fillable = [
        "raw_message",
        "received_at",
        "parsed",
    ];
}
