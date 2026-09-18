<?php

namespace App\Notifications;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\URL;

class UserInvited extends Notification
{
    use Queueable;

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(User $notifiable): MailMessage
    {
        $url = URL::temporarySignedRoute(
            'invite.accept',
            now()->addDays(7),
            ['user' => $notifiable->getKey()],
        );

        $store = Setting::getStoreDetails();

        return (new MailMessage)
            ->subject('You have been invited to '.$store['storeName'])
            ->markdown('emails.user-invited', [
                'user' => $notifiable,
                'url' => $url,
                'store' => $store,
            ]);
    }
}
