<?php

namespace Workbench\App\Models;

use HotwiredLaravel\TurboLaravel\Models\Broadcasts;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Board extends Model
{
    use Broadcasts;
    use HasFactory;

    protected $broadcastsRefreshes = true;

    protected $guarded = [];
}
