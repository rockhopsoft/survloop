<?php

namespace RockHopSoft\Survloop\Controllers\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Http\Request;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Mail;
use RockHopSoft\Survloop\Controllers\SurvloopController;
use RockHopSoft\Survloop\Controllers\Globals\Globals;

class CustomResetPasswordNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($token)
    {
        $this->token = $token;
        //
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $GLOBALS["SL"] = new Globals(new Request, 1, 1, 1);
        $subj = 'Reset your ' . $GLOBALS["SL"]->sysOpts["site-name"] . ' password';
        $content = view(
            'vendor.survloop.emails.password',
            [
                'token'     => $this->token,
                'cssColors' => $GLOBALS["SL"]->getCssColorsEmail()
            ]
        )->render();
        $emaTo = [
            [ $notifiable->email, $notifiable->email ]
        ];
        $surv = new SurvloopController;
        $surv->sendEmail($content, $subj, $emaTo);
        echo '<script type="text/javascript"> '
            . 'setTimeout("window.location=\'/password/email-sent\'", 10); '
            . '</script>';
        exit;
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}
