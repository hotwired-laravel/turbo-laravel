<?php

namespace Tonysm\TurboLaravel\Broadcasting;

use Illuminate\Database\Eloquent\Model;
use Tonysm\TurboLaravel\NamesResolver;

class Rendering
{
    public ?string $partial = null;
    public ?array $data = [];

    public function __construct(?string $partial = null, ?array $data = [])
    {
        $this->partial = $partial;
        $this->data = $data;
    }

    public static function empty(): self
    {
        return new self();
    }

    public static function forModel(Model $model): self
    {
        return new self(
            NamesResolver::partialNameFor($model),
            [
                NamesResolver::resourceVariableName($model) => $model,
            ],
        );
    }
}
