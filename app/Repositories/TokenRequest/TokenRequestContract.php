<?php

namespace App\Repositories\TokenRequest;

use App\Models\TokenRequest;

interface TokenRequestContract
{

    /**
     * @param array $input
     *
     * @return TokenRequest
     */
    public function store(array $input): TokenRequest;

    /**
     * @param string $email
     *
     * @return Void
     */
    public function deleteExisting(string $email): void;
}