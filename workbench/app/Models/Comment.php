<?php

namespace Workbench\App\Models;

use HotwiredLaravel\TurboLaravel\Models\Broadcasts;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    use Broadcasts;
    use HasFactory;

    protected $guarded = [];

    protected $broadcasts = true;

    protected $broadcastTo = 'article';

    public function article()
    {
        return $this->belongsTo(Article::class);
    }
}
