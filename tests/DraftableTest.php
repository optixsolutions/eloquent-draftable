<?php

namespace Optix\Draftable\Tests;

use Carbon\Carbon;

class DraftableTest extends TestCase
{
    /** @test */
    public function it_will_draft_a_model_on_save()
    {
        $model = TestModel::create(['title' => 'Test']);

        $this->assertTrue($model->isDraft());
    }
    
    /** @test */
    public function it_can_publish_a_model()
    {
        $methodOne = TestModel::create([
            'title' => 'Method one',
            'published_at' => Carbon::now()
        ]);

        $methodTwo = TestModel::create(['title' => 'Method two']);
        $methodTwo->publish();

        $this->assertTrue($methodOne->isPublished());
        $this->assertTrue($methodTwo->isPublished());
    }

    /** @test */
    public function it_can_publish_a_model_on_a_scheduled_date()
    {
        $model = TestModel::create(['title' => 'Scheduled']);
        $model->schedule($date = Carbon::now()->addWeek());

        $this->assertTrue($model->isDraft());

        Carbon::setTestNow($date);

        $this->assertTrue($model->isPublished());
    }

    /** @test */
    public function it_can_draft_a_published_model()
    {
        $publishedModel = TestModel::create([
            'title' => 'Published',
            'published_at' => Carbon::now()
        ]);

        $publishedModel->draft();

        $this->assertTrue($publishedModel->isDraft());
    }
    
    /** @test */
    public function it_will_exclude_draft_models_from_query_results()
    {
        $publishedModel = TestModel::create([
            'title' => 'Published',
            'published_at' => now()
        ]);

        $draftModel = TestModel::create(['title' => 'Draft']);

        $models = TestModel::all();

        $this->assertCount(1, $models);
        $this->assertTrue($models->first()->is($publishedModel));
    }
}
