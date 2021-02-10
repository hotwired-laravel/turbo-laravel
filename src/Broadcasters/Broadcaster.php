<?php

namespace Tonysm\TurboLaravel\Broadcasters;

use Illuminate\Database\Eloquent\Model;

interface Broadcaster
{
    public function exceptForSocket(string $socket = null): Broadcaster;

    public function create(Model $model): void;

    public function update(Model $model): void;

    public function remove(Model $model): void;
}
