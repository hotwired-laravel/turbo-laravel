<?php

namespace Tonysm\TurboLaravel\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Tonysm\TurboLaravel\Models\Broadcasts;

class BroadcastModelUpdated implements ShouldQueue
{
    use InteractsWithQueue;
    use SerializesModels;

    public Model $model;
    private ?string $exceptForSocket;

    /**
     * BroadcastModelUpdated constructor.
     *
     * @param Model|Broadcasts $model
     * @param string|null $exceptForSocket
     */
    public function __construct(Model $model, string $exceptForSocket = null)
    {
        $this->model = $model;
        $this->exceptForSocket = $exceptForSocket;
    }

    public function handle()
    {
        $this->model->hotwireBroadcastUsing()
            ->exceptForSocket($this->exceptForSocket)
            ->update($this->model);
    }
}
