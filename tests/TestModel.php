<?php

namespace Tonysm\TurboLaravel\Tests;

use Illuminate\Database\Eloquent\Model;

class TestModel extends Model
{
    protected $table = 'test_models';
    protected $guarded = [];
}
