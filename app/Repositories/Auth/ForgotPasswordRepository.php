<?php

namespace App\Repositories\Auth;

use Illuminate\Mail\Message;
use Illuminate\Support\Facades\Password;
use Symfony\Component\HttpFoundation\Response;

class ForgotPasswordRepository
{
    protected $broker = 'users';

    /**
     * Send a reset link to the given user.
     *
     * @param array $input
     *
     * @return array
     */
    public function process(array $input): array
    {
        $broker = $this->getBroker();

        $response = Password::broker($broker)->sendResetLink(
            [
                'email' => $input['email'],
            ],
            function (Message $message) {
                $message->subject($this->getEmailSubject());
            }
        );
       // dd($response);
        switch ($response) {
            case Password::RESET_LINK_SENT:
                return [
                    'code' => Response::HTTP_OK,
                    'message' => __('password_reset_success'),
                ];
            case Password::INVALID_USER:
                return [
                    'code' => Response::HTTP_BAD_REQUEST,
                    'message' => __('user_with_email_not_found'),
                ];
            default:
                return [
                    'code' => Response::HTTP_BAD_REQUEST,
                    'message' => __('password_reset_failed'),
                ];
        }
    }

    /**
     * Get the broker to be used during password reset.
     *
     * @return string|null
     */
    public function getBroker(): ?string
    {
        return property_exists($this, 'broker') ? $this->broker : null;
    }

    /**
     * Get the e-mail subject line to be used for the reset link email.
     *
     * @return string
     */
    public function getEmailSubject(): string
    {
        return property_exists($this, 'subject')
            ? $this->subject
            : __('password_reset_link_subject');
    }
}
