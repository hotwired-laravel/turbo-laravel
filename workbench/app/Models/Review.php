<?php

namespace Workbench\App\Models;

use HotwiredLaravel\TurboLaravel\Models\Broadcasts;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    use Broadcasts;
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'status' => ReviewStatus::class,
    ];

    protected $touches = [
        'comment',
    ];

    public function comment()
    {
        return $this->belongsTo(Comment::class);
    }
}
