<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Repositories\Auth\RegisterRepository;
use App\Resources\UserResource;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */
    /**
     * @var RegisterRepository
     */
    protected $registerRepository;


    /**
     * RegisterController constructor.
     *
     * @param RegisterRepository $registerRepository
     */
    public function __construct(RegisterRepository $registerRepository)
    {

        $this->registerRepository = $registerRepository;
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     * @throws Exception
     */
    public function create(Request $request): JsonResponse
    {
        $input = $this->validate($request, config('validation-rules.register'));

        $oUser = new UserResource($this->registerRepository->process($input));

        return $this->httpOk([
            'message' => __('register_success', ['email' => $oUser->email]),
            (['email' => $oUser->email]),
            'data' => [
                'user' => $oUser,
            ],
        ]);

    }
}


