<?php

namespace HotwiredLaravel\TurboLaravel\Tests;

use Illuminate\Database\Eloquent\Model;

class TestModel extends Model
{
    protected $table = 'broadcast_test_models';

    protected $guarded = [];
}
