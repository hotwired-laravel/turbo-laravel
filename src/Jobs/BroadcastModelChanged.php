<?php

namespace Tonysm\TurboLaravel\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Queue\SerializesModels;

class BroadcastModelChanged
{
    use Queueable, SerializesModels;

    public $model;
    public $action;

    public function __construct(Model $model, string $action)
    {
        $this->model = $model;
        $this->action = $action;
    }

    public function handle()
    {
        $this->model->hotwireBroadcastUsing()->update(
            $this->model,
            $this->action
        );
    }
}
