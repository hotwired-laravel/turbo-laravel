<?php

namespace HotwiredLaravel\TurboLaravel\Tests\Views;

use HotwiredLaravel\TurboLaravel\Tests\TestCase;
use HotwiredLaravel\TurboLaravel\Views\RecordIdentifier;
use Workbench\App\Models\Article;

class RecordIdentifierTest extends TestCase
{
    private \Workbench\App\Models\Article $model;

    private string $singular;

    protected function setUp(): void
    {
        parent::setUp();

        $this->model = new Article(['title' => 'Hello World']);
        $this->singular = 'article';
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function dom_id_of_new_record(): void
    {
        $this->assertEquals("create_{$this->singular}", (new RecordIdentifier($this->model))->domId());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function dom_id_of_new_record_with_custom_prefix(): void
    {
        $this->assertEquals("custom_prefix_{$this->singular}", (new RecordIdentifier($this->model))->domId('custom_prefix'));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function dom_id_of_saved_record(): void
    {
        $this->model->save();

        $this->assertEquals("{$this->singular}_{$this->model->getKey()}", (new RecordIdentifier($this->model))->domId());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function dom_id_of_saved_record_with_custom_prefix(): void
    {
        $this->model->save();

        $this->assertEquals("custom_prefix_{$this->singular}_{$this->model->getKey()}", (new RecordIdentifier($this->model))->domId('custom_prefix'));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function dom_class(): void
    {
        $this->assertEquals($this->singular, (new RecordIdentifier($this->model))->domClass());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function dom_class_with_custom_prefix(): void
    {
        $this->assertEquals("custom_prefix_{$this->singular}", (new RecordIdentifier($this->model))->domClass('custom_prefix'));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function channel_name_for_model(): void
    {
        $this->model->save();

        // This is now built into Laravel. I'm letting the test here in case something changes upstream.

        $this->assertEquals(
            sprintf('Workbench.App.Models.Article.%s', $this->model->getKey()),
            $this->model->broadcastChannel()
        );

        $this->assertEquals(
            'Workbench.App.Models.Article.{article}',
            $this->model->broadcastChannelRoute()
        );
    }
}
