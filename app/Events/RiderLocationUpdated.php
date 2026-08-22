<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RiderLocationUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $driverID;
    public $location;

    /**
     * Create a new event instance.
     */


    public function __construct($driverID, $location)
    {
        $this->driverID = $driverID;
        $this->location = $location;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {

        return [
            new Channel('rider-location.'.$this->driverID),
        ];
    }

     public function broadcastWith()
    {
        return [
            'location' => [
                'driver_id' => $this->driverID,
                'latitude' => $this->location->latitude,
                'longitude' => $this->location->longitude
            ],
        ];
    }

    public function broadcastAs(): string
    {
        return 'rider.location.updated';
    }
}
