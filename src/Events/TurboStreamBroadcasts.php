<?php

namespace Tonysm\TurboLaravel\Events;

use Tonysm\TurboLaravel\TurboStreamChannelsResolver;

/**
 * @property-read \Illuminate\Database\Eloquent\Model|\Tonysm\TurboLaravel\Models\Broadcasts $model
 */
trait TurboStreamBroadcasts
{
    public function broadcastOn()
    {
        if (method_exists($this->model, 'hotwireResolveBroadcastChannelNamesUsing')) {
            return $this->model->hotwireResolveBroadcastChannelNamesUsing()->hotwireBroadcastsOn($this->model);
        }

        return resolve(TurboStreamChannelsResolver::class)->hotwireBroadcastsOn($this->model);
    }

    public function broadcastWith()
    {
        return [
            'message' => $this->render(),
        ];
    }
}
