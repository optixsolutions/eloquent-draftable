<?php

namespace Optix\Draftable\Tests;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Optix\Draftable\Draftable;

/**
 * @property int $id
 * @property Carbon $published_at
 */
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
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'published_at',
    ];
}
