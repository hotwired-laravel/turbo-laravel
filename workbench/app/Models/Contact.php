<?php

namespace Workbench\App\Models;

use HotwiredLaravel\TurboLaravel\Models\Broadcasts;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Contact extends Model
{
    use Broadcasts;
    use HasFactory;

    protected $guarded = [];

    protected $broadcasts = true;

    public function broadcastsTo()
    {
        return $this->company;
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}
