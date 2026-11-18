<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/** Sent when the post-donation cooldown ends and the donor is switched back to available. */
class DonorAvailableAgain extends Notification implements ShouldQueue
{
    use Queueable;

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('আপনি আবার রক্ত দিতে প্রস্তুত')
            ->greeting('প্রিয় '.$notifiable->name.',')
            ->line('শেষ রক্তদানের পর আপনার '.config('bloodlink.donation_cooldown_months').' মাসের বিশ্রাম শেষ হয়েছে। তাই BloodLink-এ আপনাকে আবার "প্রস্তুত" দেখানো হচ্ছে।')
            ->line('শুধু সুস্থ থাকলে রক্ত দিন। রক্ত দেওয়ার আগে হাসপাতাল আপনাকে পরীক্ষা করবে।')
            ->action('ড্যাশবোর্ড খুলুন', route('dashboard'));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'kind' => 'available_again',
            'title' => 'আপনি আবার প্রস্তুত',
            'body' => 'রক্তদানের পরের বিশ্রামের সময় শেষ হয়েছে।',
            'url' => route('dashboard'),
        ];
    }
}
