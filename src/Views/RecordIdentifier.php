<?php

namespace Tonysm\TurboLaravel\Views;

use Illuminate\Database\Eloquent\Model;
use Tonysm\TurboLaravel\Models\Naming\Name;

class RecordIdentifier
{
    const NEW_PREFIX = "create";
    const DELIMITER = "_";

    /** @var Model|TurboStreamable */
    private $record;

    public function __construct(object $record)
    {
        throw_if(
            ! $this->isStreamable($record),
            sprintf('[%s] must be an instance of Eloquent or TurboStreamable.', get_class($record))
        );

        $this->record = $record;
    }

    public function domId(?string $prefix = null): string
    {
        if ($recordId = $this->streamableKey()) {
            return sprintf('%s%s%s', $this->domClass($prefix), self::DELIMITER, $recordId);
        }

        return $this->domClass($prefix ?: static::NEW_PREFIX);
    }

    public function domClass(?string $prefix = null): string
    {
        $singular = Name::forModel($this->record)->singular;
        $delimiter = static::DELIMITER;

        return trim("{$prefix}{$delimiter}{$singular}", $delimiter);
    }

    protected function streamableKey(): ?string
    {
        if ($this->record instanceof Model) {
            return $this->record->getKey();
        }

        if ($this->record instanceof TurboStreamable) {
            return $this->record->getDomId();
        }
    }

    protected function isStreamable($record): bool
    {
        return $record instanceof Model || $record instanceof TurboStreamable;
    }
}
