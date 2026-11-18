<?php

namespace App\Notifications;

use App\Models\EmergencyRequestResponse;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class EmergencyResponseReceived extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public EmergencyRequestResponse $response) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('একজন ডোনার আপনার অনুরোধে সাড়া দিয়েছেন')
            ->line($this->response->donor->publicName().' জানিয়েছেন, তিনি '.$this->response->emergencyRequest->hospital_name.'-এ রক্ত দিতে পারবেন।')
            ->action('সাড়াগুলো দেখুন', route('emergency.show', $this->response->emergencyRequest));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'kind' => 'emergency_response',
            'title' => 'একজন ডোনার রক্ত দিতে রাজি',
            'body' => $this->response->donor->publicName().' আপনার '.$this->response->emergencyRequest->hospital_name.'-এর অনুরোধে সাড়া দিয়েছেন।',
            'url' => route('emergency.show', $this->response->emergencyRequest),
        ];
    }
}
