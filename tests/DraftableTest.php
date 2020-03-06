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
    public function it_can_mark_a_model_as_published()
    {
        /** @var TestModel $model */
        $model = factory(TestModel::class)->create();

        $this->assertFalse($model->isPublished());

        $model->publish();

        // The model should now be published...
        $this->assertTrue($model->isPublished());

        // Ensure the change was saved...
        $this->assertFalse($model->isDirty());
    }

    /** @test */
    public function it_can_mark_a_model_as_published_without_saving()
    {
        $model = new TestModel();

        $this->assertFalse($model->isPublished());

        $model->setPublished(true);

        // The model should now be published...
        $this->assertTrue($model->isPublished());

        // Ensure the change was not saved...
        $this->assertTrue($model->isDirty());
    }

    /** @test */
    public function it_can_mark_a_model_as_draft()
    {
        /** @var TestModel $model */
        $model = factory(TestModel::class)
            ->state('published')
            ->create();

        $this->assertFalse($model->isDraft());

        $model->draft();

        // The model should now be draft...
        $this->assertTrue($model->isDraft());

        // Ensure the change was saved...
        $this->assertFalse($model->isDirty());
    }

    public function it_can_mark_a_model_as_draft_without_saving()
    {
        /** @var TestModel $model */
        $model = factory(TestModel::class)
            ->state('published')
            ->make();

        $this->assertFalse($model->isDraft());

        $model->setPublished(false);

        // The model should now be draft...
        $this->assertTrue($model->isDraft());

        // Ensure the change was not saved...
        $this->assertFalse($model->isDirty());
    }

    /** @test */
    public function it_can_publish_or_draft_a_model_based_on_a_boolean_value()
    {
        /** @var TestModel $model */
        $model = factory(TestModel::class)->make();

        $this->assertTrue($model->isDraft());

        // Publish without saving...
        $model->setPublished(true);

        $this->assertTrue($model->isPublished());

        // Draft without saving...
        $model->setPublished(false);

        $this->assertTrue($model->isDraft());

        // Publish and save...
        $model->publish(true);

        $this->assertTrue($model->isPublished());

        // Draft and save...
        $model->publish(false);

        $this->assertTrue($model->isDraft());
    }

    /** @test */
    public function it_will_not_update_the_published_at_timestamp_when_publishing_an_already_published_model()
    {
        $publishedAt = Carbon::now()->startOfDay()->subDay();

        /** @var TestModel $model */
        $model = factory(TestModel::class)->make([
            'published_at' => $publishedAt,
        ]);

        $this->assertTrue($model->isPublished());

        // Publish without saving...
        $model->setPublished(true);

        $this->assertTrue($publishedAt->equalTo($model->published_at));

        // Publish and save...
        $model->publish(true);

        $this->assertTrue($publishedAt->equalTo($model->published_at));
    }

    /** @test */
    public function it_can_schedule_a_model_to_be_published()
    {
        /** @var TestModel $model */
        $model = factory(TestModel::class)
            ->state('published')
            ->create();

        $this->assertTrue($model->isPublished());

        $publishDate = Carbon::now()->startOfDay()->addWeek();

        $model->publishAt($publishDate);

        $this->assertFalse($model->isPublished());

        // The model should be scheduled to publish at the given date...
        $this->assertTrue($publishDate->equalTo($model->published_at));

        // Ensure the change was saved...
        $this->assertFalse($model->isDirty());

        Carbon::setTestNow($publishDate);

        $this->assertTrue($model->isPublished());
    }

    /** @test */
    public function it_can_schedule_a_model_to_be_published_without_saving()
    {
        /** @var TestModel $model */
        $model = factory(TestModel::class)
            ->state('published')
            ->create();

        $this->assertTrue($model->isPublished());

        $publishDate = Carbon::now()->startOfDay()->addWeek();

        $model->publishAt($publishDate);

        $this->assertFalse($model->isPublished());

        // The model should be scheduled to publish at the given date...
        $this->assertTrue($publishDate->equalTo($model->published_at));

        // Ensure the change was not saved...
        $this->assertFalse($model->isDirty());

        Carbon::setTestNow($publishDate);

        $this->assertTrue($model->isPublished());
    }
}
