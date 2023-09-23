<?php

namespace Workbench\App\Models;

use HotwiredLaravel\TurboLaravel\Models\Broadcasts;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Company extends Model
{
    use Broadcasts;
    use HasFactory;

    protected $guarded = [];

    protected $broadcasts = [
        'insertsBy' => 'prepend',
        'stream' => 'custom-channel',
    ];

    public function contacts(): HasMany
    {
        return $this->hasMany(Contact::class);
    }
}
