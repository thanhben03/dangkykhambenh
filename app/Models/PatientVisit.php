<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PatientVisit extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function patient()
    {
        return $this->belongsTo('App\Models\Patient');
    }

    public function medicines()
    {
        return $this->hasMany(MedicinePrescription::class, 'current_patient_visit');
    }

    public function department () {
        return $this->belongsTo(Department::class);
    }
}
