<?php

namespace App\Http\Resources;

use App\Models\PatientVisit;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;

class PatientPendingResource extends ResourceCollection
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Support\Collection
     */
    public function toArray($request)
    {
        return $this->collection->map(function ($item) {
            return [
                'id' => $item->id,
                'stt' => $item->stt,
                'name' => $item->name,
                'address' => $item->address,
                'sex' => $item->sex,
                'bod' => $item->bod,
                'telephone' => $item->telephone,
                'symptom' => $item->symptom,
                'diagnosis' => $item->diagnosis,
                'nic' => $item->nic,
                'info' => $item->patient,
                'kham_tq' => $item->kham_tq,
                'department' => $item->department,
                'arrival_time' => $item->arrival_time,
                'department_id' => $item->department_id,
                'history_medicine' => $item->medicines,
                'history' => PatientVisit::query()
                    ->where('patient_id', $item->patient_id)
                    ->whereNot('id', $item->id)
                    ->take(5)
                    ->get()
                ,
            ];
        });
    }
}
