<?php

namespace Tonysm\TurboLaravel\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Tonysm\TurboLaravel\Models\Broadcasts;

class BroadcastModelCreated implements ShouldQueue
{
    use InteractsWithQueue;
    use SerializesModels;

    public Model $model;
    public ?string $exceptSocket;

    /**
     * BroadcastModelCreated constructor.
     *
     * @param Model|Broadcasts $model
     * @param string|null $exceptSocket
     */
    public function __construct(Model $model, string $exceptSocket = null)
    {
        $this->model = $model;
        $this->exceptSocket = $exceptSocket;
    }

    public function handle()
    {
        $this->model->hotwireBroadcastUsing()
            ->exceptForSocket($this->exceptSocket)
            ->create($this->model);
    }
}
