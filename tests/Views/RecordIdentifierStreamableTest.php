<?php

namespace Tonysm\TurboLaravel\Tests\Views;

use stdClass;
use Tonysm\TurboLaravel\Tests\Stubs\Models\TestTurboStreamable;
use Tonysm\TurboLaravel\Tests\TestCase;
use Tonysm\TurboLaravel\Views\RecordIdentifier;
use Tonysm\TurboLaravel\Views\UnidentifiableRecordException;

class RecordIdentifierStreamableTest extends TestCase
{
    private $streamable;
    private $singular;

    protected function setUp(): void
    {
        parent::setUp();

        $this->streamable = new TestTurboStreamable;
        $this->singular = "test_turbo_streamable";
    }

    /** @test */
    public function dom_id_of_streamable()
    {
        $this->assertEquals("{$this->singular}_turbo-dom-id", (new RecordIdentifier($this->streamable))->domId());
    }

    /** @test */
    public function dom_id_of_streamable_with_custom_prefix()
    {
        $this->assertEquals("custom_prefix_{$this->singular}_turbo-dom-id", (new RecordIdentifier($this->streamable))->domId("custom_prefix"));
    }

    /** @test */
    public function exception_is_thrown_when_given_non_streamable_instance()
    {
        $this->expectException(UnidentifiableRecordException::class);

        new RecordIdentifier(new stdClass);
    }
}
