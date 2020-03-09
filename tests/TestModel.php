<?php

namespace Optix\Draftable\Tests;

use Illuminate\Database\Eloquent\Model;
use Optix\Draftable\Draftable;

class TestModel extends Model
{
    use Draftable;

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'published_at',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
    ];
}
