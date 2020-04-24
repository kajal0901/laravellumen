<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Service extends Model
{

    /**
     * The users that belong to services.
     */
    public function users()
    {
        return $this->belongsToMany(
            User::class,
            'user_service'
        )
            ->withPivot(['deb_auto_id']);
    }
}
