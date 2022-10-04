<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use stdClass;

class SendMail extends Mailable
{
    use Queueable, SerializesModels;
    private $user;
    private $account;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($user)
    {
        $this->user = $user;
        // $this->account = $account;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $this->subject('Recuperar senha');
        $this->to($this->user->email, $this->user->name);
        return $this->view('emails.sendmail', [
            'user' => $this->user,
        ]);
    }
}
