<?php

namespace HotwiredLaravel\TurboLaravel\Tests\Views;

use HotwiredLaravel\TurboLaravel\Tests\TestCase;
use HotwiredLaravel\TurboLaravel\Views\RecordIdentifier;
use HotwiredLaravel\TurboLaravel\Views\UnidentifiableRecordException;
use stdClass;
use Workbench\App\Models\ReviewStatus;

class RecordIdentifierStreamableTest extends TestCase
{
    private $streamable;

    private $singular;

    protected function setUp(): void
    {
        parent::setUp();

        $this->streamable = ReviewStatus::Approved;
        $this->singular = 'review_status';
    }

    /** @test */
    public function dom_id_of_streamable()
    {
        $this->assertEquals("{$this->singular}_{$this->streamable->value}", (new RecordIdentifier($this->streamable))->domId());
    }

    /** @test */
    public function dom_id_of_streamable_with_custom_prefix()
    {
        $this->assertEquals("custom_prefix_{$this->singular}_{$this->streamable->value}", (new RecordIdentifier($this->streamable))->domId('custom_prefix'));
    }

    /** @test */
    public function exception_is_thrown_when_given_non_streamable_instance()
    {
        $this->expectException(UnidentifiableRecordException::class);

        new RecordIdentifier(new stdClass);
    }
}
