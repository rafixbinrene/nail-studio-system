<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/*
|--------------------------------------------------------------------------
| NS BEAUTY COMMENT:
|--------------------------------------------------------------------------
| NS Beauty Notification Email
|
| Purpose:
| - Sends email notification copies for important system events.
| - Used together with in-app notifications.
|
| Defense explanation:
| Email notifications help users stay informed even when they are not
| currently logged in to the website.
|--------------------------------------------------------------------------
*/

class NsBeautyNotificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $notificationTitle;
    public string $notificationMessage;
    public ?string $notificationLink;

    public function __construct(
        string $notificationTitle,
        string $notificationMessage,
        ?string $notificationLink = null
    ) {
        $this->notificationTitle = $notificationTitle;
        $this->notificationMessage = $notificationMessage;
        $this->notificationLink = $notificationLink;
    }

    public function build()
    {
        return $this
            ->subject($this->notificationTitle)
            ->view('emails.ns-beauty-notification');
    }
}