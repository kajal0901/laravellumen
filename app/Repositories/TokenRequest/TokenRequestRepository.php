<?php

namespace App\Repositories\TokenRequest;

use App\Models\TokenRequest;
use App\Repositories\BaseRepository;
use Exception;

class TokenRequestRepository extends BaseRepository implements TokenRequestContract
{
    /*
     * @var TokenRequest
     */
    protected $model;

    public function __construct(TokenRequest $model)
    {

        $this->model = $model;
    }

    /**
     * {@inheritDoc}
     * @throws Exception
     */
    public function store(array $input): TokenRequest
    {
        return $this->model->create($input);
    }

    /**
     * {@inheritDoc}
     */
    public function deleteExisting(string $email): void
    {
        $this->model->where(['email' => $email])->delete();
    }


}