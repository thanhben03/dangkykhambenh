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
                'trieu_chung' => $item->trieu_chung,
                'chuan_doan' => $item->chuan_doan,
                'nic' => $item->nic,
                'info' => $item->patient,
                'history_medicine' => $item->medicines,
                'history' => PatientVisit::query()
                    ->where('patient_id', $item->patient_id)
                    ->whereNot('id', $item->id)
                    ->get(),
            ];
        });
    }
}
