<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TokenRequest extends Model
{
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['email', 'token'];

    public static function boot()
    {

        parent::boot();

        static::creating(function ($model) {
            $model->created_at = $model->freshTimestamp();
        });
    }

}
