<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\User;

class NewUserCredentials extends Mailable
{
    use Queueable, SerializesModels;

    public User   $user;
    public string $password;

    /**
     * @param User   $user
     * @param string $password  The plain‐text password we generated
     */
    public function __construct(User $user, string $password)
    {
        $this->user     = $user;
        $this->password = $password;
    }

    public function build()
    {
        return $this
            ->subject('Your new account credentials')
            ->view('emails.users.new_credentials')
            ->with([
                'name'     => $this->user->name,
                'nin'      => $this->user->nin,
                'password' => $this->password,
            ]);
    }
}
