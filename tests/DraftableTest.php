<?php

namespace Optix\Draftable\Tests;

use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

class DraftableTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_determine_if_a_model_is_published()
    {
        /** @var TestModel $draftModel */
        $draftModel = factory(TestModel::class)->make();

        $this->assertFalse($draftModel->isPublished());

        /** @var TestModel $publishedModel */
        $publishedModel = factory(TestModel::class)
            ->state('published')
            ->make();

        $this->assertTrue($publishedModel->isPublished());

        /** @var TestModel $scheduledModel */
        $scheduledModel = factory(TestModel::class)->make([
            'published_at' => $scheduledFor = Carbon::now()->addDay(),
        ]);

        $this->assertFalse($scheduledModel->isPublished());

        // Spoof now to be the date the model is scheduled for...
        Carbon::setTestNow($scheduledFor);

        $this->assertTrue($scheduledModel->isPublished());
    }

    /** @test */
    public function it_can_determine_if_a_model_is_draft()
    {
        /** @var TestModel $draftModel */
        $draftModel = factory(TestModel::class)->make();

        $this->assertTrue($draftModel->isDraft());

        /** @var TestModel $publishedModel */
        $publishedModel = factory(TestModel::class)
            ->state('published')
            ->make();

        $this->assertFalse($publishedModel->isDraft());

        /** @var TestModel $scheduledModel */
        $scheduledModel = factory(TestModel::class)->make([
            'published_at' => $scheduledFor = Carbon::now()->addDay(),
        ]);

        $this->assertTrue($scheduledModel->isDraft());

        // Spoof now to be the date the model is scheduled for...
        Carbon::setTestNow($scheduledFor);

        $this->assertFalse($scheduledModel->isDraft());
    }

    /** @test */
    public function it_will_exclude_draft_records_from_query_results_by_default()
    {
        // Create two draft models...
        factory(TestModel::class)->create();
        factory(TestModel::class)->state('scheduled')->create();

        // Create two published models...
        factory(TestModel::class, 2)
            ->state('published')
            ->create();

        $models = TestModel::all();

        $this->assertCount(2, $models);

        $models->each(function (TestModel $model) {
            $this->assertTrue($model->isPublished());
        });
    }

    /** @test */
    public function it_can_include_draft_records_in_query_results()
    {
        // Create two draft models...
        factory(TestModel::class)->create();
        factory(TestModel::class)->state('scheduled')->create();

        // Create two published models...
        factory(TestModel::class, 2)
            ->state('published')
            ->create();

        $models = TestModel::withDrafts()->get();

        $draftCount = 0;
        $publishedCount = 0;

        foreach ($models as $model) {
            if ($model->isDraft()) {
                $draftCount++;
            } else {
                $publishedCount++;
            }
        }

        $this->assertEquals(2, $draftCount);
        $this->assertEquals(2, $publishedCount);
    }

    /** @test */
    public function it_can_exclude_published_records_from_query_results()
    {
        // Create two draft models...
        factory(TestModel::class)->create();
        factory(TestModel::class)->state('scheduled')->create();

        // Create two published models...
        factory(TestModel::class, 2)
            ->state('published')
            ->create();

        $models = TestModel::onlyDrafts()->get();

        $this->assertCount(2, $models);

        $models->each(function (TestModel $model) {
            $this->assertTrue($model->isDraft());
        });
    }

    /** @test */
    public function it_can_publish_or_draft_a_model_based_on_a_boolean_value()
    {
        // Todo...
    }

    /** @test */
    public function it_can_publish_a_model()
    {
        // Todo: Without persisting...

        /** @var TestModel $model */
        $model = factory(TestModel::class)->create();

        $this->assertTrue($model->isDraft());

        $model->publish();

        // The model should now be published...
        $this->assertTrue($model->isPublished());

        // Ensure the change was persisted...
        $this->assertFalse($model->isDirty());
    }

    /** @test */
    public function it_can_draft_a_model()
    {
        // Todo: Without persisting...

        /** @var TestModel $model */
        $model = factory(TestModel::class)
            ->state('published')
            ->create();

        $this->assertTrue($model->isPublished());

        $model->draft();

        // The model should now be draft...
        $this->assertTrue($model->isDraft());

        // Ensure the change was persisted...
        $this->assertFalse($model->isDirty());
    }

    /** @test */
    public function it_can_schedule_a_model_to_be_published_at_a_given_date()
    {
        // Todo: Without persisting...

        /** @var TestModel $model */
        $model = factory(TestModel::class)->state('published')->create();

        $this->assertTrue($model->isPublished());

        $publishDate = Carbon::now()->addDay();

        $model->publishAt($publishDate);

        $this->assertTrue($model->isDraft());

        // The model should be set to publish at the given date...
        $publishDate->equalTo($model->published_at);

        // Ensure the change was persisted...
        $this->assertFalse($model->isDirty());

        // Spoof now to be the date the model is scheduled for...
        Carbon::setTestNow($publishDate);

        $this->assertTrue($model->isPublished());
    }

    /** @test */
    public function it_accepts_the_schedule_date_in_multiple_formats()
    {
        // Todo...
    }
}
