<?php

namespace App\Listeners;

use App\Mail\OrderMail;
use Illuminate\Support\Facades\Mail;

class OrderMailListener
{
    private const RATE_LIMIT_MESSAGE = 'Too many emails per second';

    private const RETRY_DELAYS_SECONDS = [2, 4, 6];

    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(object $event): void
    {
        if (config('app.mail_username') && config('app.mail_password') && config('app.mail_encryption')) {
            $this->sendOrderMail($event->email, $event->order);
        }
    }


    private function sendOrderMail(string $email, $order): void
    {
        $delays = array_merge([0], self::RETRY_DELAYS_SECONDS);
        $lastException = null;

        foreach ($delays as $delay) {
            if ($delay > 0) {
                sleep($delay);
            }

            try {
                Mail::to($email)->send(new OrderMail($email, $order));

                return;
            } catch (\Throwable $th) {
                if (! str_contains($th->getMessage(), self::RATE_LIMIT_MESSAGE)) {
                    throw $th;
                }

                $lastException = $th;
            }
        }

        if ($lastException) {
            throw $lastException;
        }
    }
}
