<?php

namespace HotwiredLaravel\TurboLaravel\Tests\Views;

use HotwiredLaravel\TurboLaravel\Tests\TestCase;
use HotwiredLaravel\TurboLaravel\Views\RecordIdentifier;
use Workbench\App\Models\Article;

class RecordIdentifierTest extends TestCase
{
    private $model;

    private $singular;

    protected function setUp(): void
    {
        parent::setUp();

        $this->model = new Article(['title' => 'Hello World']);
        $this->singular = 'article';
    }

    /** @test */
    public function dom_id_of_new_record()
    {
        $this->assertEquals("create_{$this->singular}", (new RecordIdentifier($this->model))->domId());
    }

    /** @test */
    public function dom_id_of_new_record_with_custom_prefix()
    {
        $this->assertEquals("custom_prefix_{$this->singular}", (new RecordIdentifier($this->model))->domId('custom_prefix'));
    }

    /** @test */
    public function dom_id_of_saved_record()
    {
        $this->model->save();

        $this->assertEquals("{$this->singular}_{$this->model->getKey()}", (new RecordIdentifier($this->model))->domId());
    }

    /** @test */
    public function dom_id_of_saved_record_with_custom_prefix()
    {
        $this->model->save();

        $this->assertEquals("custom_prefix_{$this->singular}_{$this->model->getKey()}", (new RecordIdentifier($this->model))->domId('custom_prefix'));
    }

    /** @test */
    public function dom_class()
    {
        $this->assertEquals($this->singular, (new RecordIdentifier($this->model))->domClass());
    }

    /** @test */
    public function dom_class_with_custom_prefix()
    {
        $this->assertEquals("custom_prefix_{$this->singular}", (new RecordIdentifier($this->model))->domClass('custom_prefix'));
    }

    /** @test */
    public function channel_name_for_model()
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
