<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;

class PatientDataRemote extends ResourceCollection
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request)
    {
        return $this->collection->map(function ($item) {
            return [
                'id' => $item->id,
                'name' => $item->name,
                'address' => $item->address,
                'sex' => $item->sex,
                'bod' => $item->bod,
                'telephone' => $item->telephone,
                'nic' => $item->nic,
                'kham_tq' => $item->kham_tq,
                'arrival_time' => $item->arrival_time,
                'department' => $item->department
            ];
        });
    }
}
