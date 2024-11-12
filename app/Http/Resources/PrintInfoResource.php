<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PrintInfoResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request)
    {
        return [
            'name' => $this->patient->name,
            'cccd' => $this->patient->nic,
            'bod' => $this->patient->bod,
            'sex' => $this->patient->sex == 'Male' ? 'Nam' : 'Nữ',
            'trieu_chung' => $this->trieu_chung,
            'chuan_doan' => $this->chuan_doan,
            'address' => $this->patient->address,
            'history_medicine' => implode(', ', $this->medicines->map(function ($item) {
                return $item->medicine_name.'/'.$item->qty.'('.$item->use.')';
            })->toArray()),
        ];
    }
}
