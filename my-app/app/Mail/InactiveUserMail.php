<?php

namespace App\Mail;

use App\Models\Reguser;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class InactiveUserMail extends Mailable
{
    use Queueable, SerializesModels;

    public $reguser;

    public function __construct(Reguser $reguser)
    {
        $this->reguser = $reguser;
    }

    public function build()
    {
        return $this->subject('We miss you! Book a new service today')
                    ->markdown('emails.inactive-user', [
                        'name' => $this->reguser->user->name,
                    ]);
    }
}
