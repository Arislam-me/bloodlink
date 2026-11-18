<?php

namespace App\Notifications;

use App\Models\EmergencyRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class EmergencyBloodRequestNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public EmergencyRequest $request) {}

    /** @return array<int, string> */
    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $r = $this->request;

        return (new MailMessage)
            ->subject("জরুরি: {$r->district->name}-এ {$r->bloodGroup->code} রক্ত দরকার")
            ->greeting('প্রিয় '.$notifiable->name.',')
            ->line("আপনার কাছাকাছি {$r->hospital_name}-এ ({$r->generalLocation()}) {$r->units_needed} ব্যাগ {$r->bloodGroup->code} রক্ত দরকার।")
            ->line('কখন লাগবে: '.$r->needed_at->translatedFormat('j F Y, g:i A'))
            ->action('অনুরোধটি দেখুন', route('emergency.show', $r))
            ->line('শুধু সুস্থ থাকলে এবং রক্ত দিতে পারলে সাড়া দিন। রক্ত দেওয়ার আগে হাসপাতাল আপনাকে পরীক্ষা করবে।');
    }

    /** @return array<string, mixed> */
    public function toArray(object $notifiable): array
    {
        $r = $this->request;

        return [
            'kind' => 'emergency',
            'title' => "{$r->bloodGroup->code} রক্ত দরকার · {$r->generalLocation()}",
            'body' => "{$r->hospital_name}-এ {$r->units_needed} ব্যাগ, লাগবে ".$r->needed_at->translatedFormat('j F, g:i A'),
            'url' => route('emergency.show', $r),
        ];
    }
}
