<?php

namespace Modules\Auth\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OtpMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $otp;

    public string $usercode;

    public function __construct(string $otp, string $usercode)
    {
        $this->otp = $otp;
        $this->usercode = $usercode;
    }

    public function build()
    {
        return $this->subject('Your Login OTP')
            ->view('auth::emails.otp');
    }
}
