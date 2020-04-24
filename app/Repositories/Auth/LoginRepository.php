<?php

namespace App\Repositories\Auth;

use App\Exceptions\EmailNotVerifiedException;
use App\Exceptions\PassportClientNotFound;
use Dusterio\LumenPassport\Http\Controllers\AccessTokenController;
use Exception;
use GuzzleHttp\Psr7\BufferStream;
use Laravel\Passport\Client;
use Symfony\Bridge\PsrHttpMessage\Tests\Fixtures\ServerRequest;
use Symfony\Component\HttpFoundation\Response;

class LoginRepository
{

    /**
     * @var $response
     */
    protected $response;

    /**
     * @var $passportClient
     */
    protected $passportClient;

    /**
     * Process login request
     *
     * @param array $input
     *
     * @return array
     */
    public function process(array $input): array
    {

        $this->passportClient = $this->passportClient();

        $this->issueToken($input);

        $data = json_decode((string)$this->response->getBody(), true);

        return $this->buildSuccessResponse($data, __('login_success'));
    }

    /**
     * Get passport client
     *
     * @return Client
     */
    public function passportClient(): Client
    {
        try {
            return Client::where('password_client', 1)
                ->orderBy('id', 'desc')
                ->firstOrFail();
        } catch (Exception $e) {
            throw new PassportClientNotFound(
                __('login_feature_disabled'),
                Response::HTTP_BAD_REQUEST
            );
        }
    }

    /**
     * Build success response
     *
     * @param array  $data
     * @param string $message
     *
     * @return array
     */
    private function buildSuccessResponse(array $data, string $message): array
    {
        return [
            'code' => Response::HTTP_OK,
            'message' => $message,
            'data' => [
                'accessToken' => $data['access_token'],
                'refreshToken' => $data['refresh_token'],
                'expiresIn' => $data['expires_in'],
                'tokenType' => $data['token_type'],
            ],
        ];
    }

    /**
     * Issue token to user attempting for login
     *
     * @param array $input
     */
    private function issueToken(array $input): void
    {
        $this->response = app(AccessTokenController::class)
            ->issueToken(
                new ServerRequest(
                    '1.1',
                    [],
                    new BufferStream(''),
                    '/oauth/token',
                    'POST',
                    config('app.url') . '/oauth/token',
                    [],
                    [],
                    ['url' => config('app.url')],
                    [],
                    [
                        'client_id' => $this->passportClient->id,
                        'client_secret' => $this->passportClient->secret,
                        'username' => $input['email'],
                        'password' => $input['password'],
                        'scope' => '*',
                        'grant_type' => 'password',
                    ],
                    []
                )
            );
    }
}