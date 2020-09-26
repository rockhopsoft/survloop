<?php

namespace Survloop\Controllers\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SurvloopEmail extends Notification
{
    use Queueable;

    protected $subj    = '';
    protected $content = '';
    protected $emaTo   = [];
    protected $emaCC   = [];
    protected $emaBCC  = [];
    protected $replyTo = [];

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($subj, $content, $emaTo)
    {
        $this->subj    = $subj;
        $this->content = $content;
        $this->emaTo   = $emaTo;
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
        /*
        return (new MailMessage)
            ->to($this->emaTo[0][0])
            ->view(
                'emails.order', ['order' => $this->order]
            );
            ->subject()
            ->subject()
            ->subject()
            ->subject();


                    ->line('The introduction to the notification.')
                    ->action('Notification Action', url('/'))
                    ->line('Thank you for using our application!');
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
