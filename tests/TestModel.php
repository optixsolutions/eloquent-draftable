<?php

namespace Optix\Draftable\Tests;

use Optix\Draftable\Draftable;
use Illuminate\Database\Eloquent\Model;

class TestModel extends Model
{
    use Draftable;

    protected $guarded = [];
}
