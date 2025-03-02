<?php

namespace HotwiredLaravel\TurboLaravel\Tests\Views;

use HotwiredLaravel\TurboLaravel\Tests\TestCase;
use HotwiredLaravel\TurboLaravel\Views\RecordIdentifier;
use HotwiredLaravel\TurboLaravel\Views\UnidentifiableRecordException;
use stdClass;
use Workbench\App\Models\ReviewStatus;

class RecordIdentifierStreamableTest extends TestCase
{
    private \Workbench\App\Models\ReviewStatus $streamable;

    private string $singular;

    protected function setUp(): void
    {
        parent::setUp();

        $this->streamable = ReviewStatus::Approved;
        $this->singular = 'review_status';
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function dom_id_of_streamable(): void
    {
        $this->assertEquals("{$this->singular}_{$this->streamable->value}", (new RecordIdentifier($this->streamable))->domId());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function dom_id_of_streamable_with_custom_prefix(): void
    {
        $this->assertEquals("custom_prefix_{$this->singular}_{$this->streamable->value}", (new RecordIdentifier($this->streamable))->domId('custom_prefix'));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function exception_is_thrown_when_given_non_streamable_instance(): void
    {
        $this->expectException(UnidentifiableRecordException::class);

        new RecordIdentifier(new stdClass);
    }
}
