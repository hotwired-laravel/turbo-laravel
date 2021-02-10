<?php

namespace Tonysm\TurboLaravel\Views;

use Illuminate\Database\Eloquent\Model;
use Tonysm\TurboLaravel\Models\Naming\Name;

class RecordIdentifier
{
    const NEW_PREFIX = "create";
    const DELIMITER = "_";

    /** @var Model */
    private $record;

    public function __construct(Model $record)
    {
        $this->record = $record;
    }

    public function domId(?string $prefix = null): string
    {
        if ($recordId = $this->record->getKey()) {
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

    public function channelName(): string
    {
        return static::channelAuthKey(get_class($this->record), $this->record->getKey());
    }

    public static function channelAuthKey(string $className, string $key): string
    {
        $path = str_replace('\\', '.', $className);

        return "{$path}.{$key}";
    }
}
