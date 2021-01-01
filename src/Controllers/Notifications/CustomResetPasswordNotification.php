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
        $GLOBALS["SL"]->x["subj"] = 'Reset your '
            . $GLOBALS["SL"]->sysOpts["site-name"] 
            . ' password';
        /* $content = view(
            'vendor.survloop.emails.password', 
            [
                'token'     => $this->token,
                'cssColors' => $GLOBALS["SL"]->getCssColorsEmail()
            ]
        )->render(); */
        $GLOBALS["SL"]->x["emaTo"] = [
            [ $notifiable->email, $notifiable->email ]
        ];
        if ($GLOBALS["SL"]->isHomestead()) {
            echo '<br /><br /><br /><div class="container"><h2>' 
                . $GLOBALS["SL"]->x["subj"] . '</h2><hr><hr></div><pre>' 
                . /* $content . */ '</pre><hr><br />';
            return true;
        }
        Mail::send(
            'vendor.survloop.emails.password', 
            [
                'token'     => $this->token,
                'cssColors' => $GLOBALS["SL"]->getCssColorsEmail()
            ],
            function ($m) { 
                $m->subject($GLOBALS["SL"]->x["subj"])
                    ->to($GLOBALS["SL"]->x["emaTo"][0][0]);
            }
        );
        session()->put(
            'status',
            'Please check your email for a password reset link.'
        );
        echo '<script type="text/javascript"> '
            . 'setTimeout("window.location=\'/password/email-sent\'", 10); '
            . '</script>';
        exit;
        /*
        return (new MailMessage)
            ->to($this->emaTo[0][0])
            ->subject($subj)
            ->view(
                'vendor.survloop.emails.password', 
                [
                    'token'     => $this->token,
                    'cssColors' => $GLOBALS["SL"]->getCssColorsEmail()
                ]
            );
        */
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
