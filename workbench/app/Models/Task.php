<?php

namespace Workbench\App\Models;

use HotwiredLaravel\TurboLaravel\Models\Broadcasts;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Task extends Model
{
    use Broadcasts;
    use HasFactory;

    protected $broadcastRefreshesTo = 'board';

    protected $guarded = [];

    public function board(): BelongsTo
    {
        return $this->belongsTo(Board::class);
    }
}
