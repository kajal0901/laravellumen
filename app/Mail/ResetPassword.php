<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ResetPassword extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @var User
     */
    public $user;

    /**
     * @var
     */
    public $redirectUrl;

    /**
     * @var
     */
    public $resetPassword;

    /**
     * Create a new message instance.
     *
     * @param User $user
     * @param      $resetPassword
     */
    public function __construct(User $user, $resetPassword)
    {
        $this->user = $user;

        $this->resetPassword = $resetPassword;

        $this->setAddress($user->email, $user->name);
        $this->subject(__('reset_password_notification'));
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $newToken = base64_encode($this->user->email . '/' . $this->resetPassword->token);

        $this->redirectUrl = config('app.frontend_url') . "/reset-password/{$newToken}";

        return $this->markdown('emails.reset_password');
    }
}
