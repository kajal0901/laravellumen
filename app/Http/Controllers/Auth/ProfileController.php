<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Repositories\Auth\ProfileRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;


class ProfileController extends Controller
{
    /**
     * @var
     */
    protected $model;
    /**
     * @var ProfileRepository
     */
    protected $repository;

    /**
     * ProfileController constructor.
     *
     * @param ProfileRepository  $profileRepository
     *
     */
    public function __construct(ProfileRepository $profileRepository)
    {
        $this->repository = $profileRepository;
    }

    /**
     * Get profile of current user
     *
     * @param Request $request
     *
     * @return JsonResponse
     * @throws \Exception
     */
    public function show(Request $request): JsonResponse
    {
        return $this->httpOk([
            'data' => [
                'user' => $this->repository->getProfile(),
            ],
        ]);
    }


}
