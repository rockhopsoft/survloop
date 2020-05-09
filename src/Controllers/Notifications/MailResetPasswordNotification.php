<?php

namespace SurvLoop\Controllers\Notifications;

use Illuminate\Http\Request;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use SurvLoop\Controllers\Globals\Globals;

class MailResetPasswordNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($token = null)
    {
        $this->token = $token;
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
        /* if (static::$toMailCallback) {
            return call_user_func(static::$toMailCallback, $notifiable, $this->token);
        } */
        $GLOBALS["SL"] = new Globals(new Request, 1, 1, 1);
        $subj = $GLOBALS["SL"]->sysOpts["site-name"] 
            . ' Reset Password Notification';
        /*
        return (new MailMessage)
            ->view(
                'vendor.survloop.emails.password', 
                [
                    'token'     => $this->token,
                    'cssColors' => $GLOBALS["SL"]->getCssColorsEmail()
                ]
            )
            ->subject($subj);
        */

        $mail = "Illuminate\\Support\\Facades\\Mail::send('vendor.survloop.emails.password', [
            'token'      => \$this->token,
            'cssColors'  => \$GLOBALS['SL']->getCssColorsEmail()
            ], function (\$m) { \$m->subject('" . str_replace("'", "\\'", $subj) . "')";
                if (sizeof($emaTo) > 0) {
                    foreach ($emaTo as $i => $eTo) {
                        $mail .= "->to('" . $eTo[0] . "'" . ((trim($eTo[1]) != '') 
                            ? ", '" . str_replace("'", "\\'", $eTo[1]) . "'" : "") . ")";
                    }
                }
                if (sizeof($emaCC) > 0) {
                    foreach ($emaCC as $eTo) {
                        $mail .= "->cc('" . $eTo[0] . "'" . ((trim($eTo[1]) != '') 
                            ? ", '" . str_replace("'", "\\'", $eTo[1]) . "'" : "") . ")";
                    }
                }
                if (sizeof($emaBCC) > 0) {
                    foreach ($emaBCC as $eTo) {
                        $mail .= "->bcc('" . $eTo[0] . "'" . ((trim($eTo[1]) != '') 
                            ? ", '" . str_replace("'", "\\'", $eTo[1]) . "'" : "") . ")";
                    }
                }
        $mail .= "->replyTo('" . $repTo[0] . "'" . ((trim($repTo[1]) != '') 
            ? ", '" . str_replace("'", "\\'", $repTo[1]) . "'" : "") . "); });";
        if ($GLOBALS["SL"]->isHomestead()) {
            echo '<br /><br /><br /><div class="container"><h2>' 
                . $emaSubject . '</h2>' . $emaContent 
                . '<hr><hr></div><pre>' . $mail . '</pre><hr><br />';
            return true;
        }
        eval($mail);
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
