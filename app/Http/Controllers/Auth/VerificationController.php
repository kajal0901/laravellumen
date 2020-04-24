<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\TokenRequest;
use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Exceptions\InvalidTokenException;

class VerificationController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Email Verification Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling email verification for any
    | user that recently registered with the application. Emails may also
    | be re-sent if the user didn't receive the original email message.
    |
    */
    /**
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function verify(Request $request): JsonResponse
    {
        $oUser = User::where(['email' => $request->email])->first();

        if ($oUser->hasVerifiedEmail()) {
            return $this->httpOk([
                'message' => __('email_already_verified'),
                'data' => [
                    'verified' => false,
                    'already_verified' => true,
                ],
            ]);
        }

        $signature = $request->signature;

        $tokenRequest = TokenRequest::where(['email' => $oUser->email])->first();

        if ($tokenRequest) {
            if (app('hash')->check($signature, $tokenRequest->token)) {
                if ($oUser->markEmailAsVerified()) {
                    TokenRequest::where(['email' => $oUser->email])->delete();
                    event(new Verified($oUser));
                }
                return $this->httpOk([
                    'message' => __('email_verification_success'),
                    'data' => [
                        'verified' => true,
                        'already_verified' => false,
                    ],
                ]);
            }
        }

        throw new InvalidTokenException;
    }
}
