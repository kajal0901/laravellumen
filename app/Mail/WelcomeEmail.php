<?php

namespace App\Mail;

use App\Models\User;
use App\Repositories\TokenRequest\TokenRequestRepository;
use Illuminate\Bus\Queueable;
use Illuminate\Http\Request;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class WelcomeEmail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @var User
     */
    public $user;

    /**
     * @var string
     */
    public $verificationUrl;

    /**
     * Create a new message instance.
     *
     * @param User $user
     */
    public function __construct(User $user)
    {

        $this->user = $user;

        $this->setAddress($user->email);
    }

    /**
     * Build the message.
     *
     * @param Request $request
     *
     * @return $this
     */
    public function build(Request $request)
    {

        $token = createNewToken();

        $this->verificationUrl = config('constants.EMAIL_VERIFICATION_URL') . "?signature={$token}&email={$this->user->email}";

        $tokenRequestRepository = app()->make(TokenRequestRepository::class);

        $tokenRequestRepository->deleteExisting($this->user->email);

        // store hashed token into database
       $tokenRequestRepository->store([
            'email' => $this->user->email,
            'token' => hashString($token),
        ]);


        return $this->markdown('emails.welcome_email');

    }
}
