<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PatientRegistered implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $departmentId;
    public $patientInfo;

    public function __construct($departmentId, $patientInfo)
    {
        $this->departmentId = $departmentId;
        $this->patientInfo = $patientInfo;
    }

    public function broadcastOn()
    {
        return new Channel("department.{$this->departmentId}");
    }
}
