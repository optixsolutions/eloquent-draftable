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
        $draftModel = $this->newTestModel();

        $this->assertFalse($draftModel->isPublished());

        $publishedModel = $this->newTestModel(Carbon::now());

        $this->assertTrue($publishedModel->isPublished());

        $scheduledModel = $this->newTestModel(
            $scheduledFor = Carbon::now()->addDay()
        );

        $this->assertFalse($scheduledModel->isPublished());

        // Spoof now to be the date the model is scheduled for...
        Carbon::setTestNow($scheduledFor);

        $this->assertTrue($scheduledModel->isPublished());
    }

    /** @test */
    public function it_can_determine_if_a_model_is_draft()
    {
        $draftModel = $this->newTestModel();

        $this->assertTrue($draftModel->isDraft());

        $publishedModel = $this->newTestModel(Carbon::now());

        $this->assertFalse($publishedModel->isDraft());

        $scheduledModel = $this->newTestModel(
            $scheduledFor = Carbon::now()->addDay()
        );

        $this->assertTrue($scheduledModel->isDraft());

        // Spoof now to be the date the model is scheduled for...
        Carbon::setTestNow($scheduledFor);

        $this->assertFalse($scheduledModel->isDraft());
    }

    /** @test */
    public function it_will_exclude_draft_records_from_query_results_by_default()
    {
        // Create two draft models...
        $this->createTestModel();
        $this->createTestModel(Carbon::now()->addDay());

        // Create two published models...
        $this->createTestModel(Carbon::now()->subMinute());
        $this->createTestModel(Carbon::now());

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
        $this->createTestModel();
        $this->createTestModel(Carbon::now()->addDay());

        // Create two published models...
        $this->createTestModel(Carbon::now()->subMinute());
        $this->createTestModel(Carbon::now());

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
        $this->createTestModel();
        $this->createTestModel(Carbon::now()->addDay());

        // Create two published models...
        $this->createTestModel(Carbon::now()->subMinute());
        $this->createTestModel(Carbon::now());

        $models = TestModel::onlyDrafts()->get();

        $this->assertCount(2, $models);

        $models->each(function (TestModel $model) {
            $this->assertTrue($model->isDraft());
        });
    }

    /** @test */
    public function it_can_mark_a_model_as_published()
    {
        $model = $this->createTestModel();

        $this->assertFalse($model->isPublished());

        $model->publish();

        // The model should now be published...
        $this->assertTrue($model->isPublished());

        // Ensure the change was saved...
        $this->assertTrue($model->isClean());
    }

    /** @test */
    public function it_can_mark_a_model_as_published_without_saving()
    {
        $model = $this->newTestModel();

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
        $model = $this->createTestModel(Carbon::now());

        $this->assertFalse($model->isDraft());

        $model->draft();

        // The model should now be draft...
        $this->assertTrue($model->isDraft());

        // Ensure the change was saved...
        $this->assertTrue($model->isClean());
    }

    /** @test */
    public function it_can_mark_a_model_as_draft_without_saving()
    {
        $model = $this->newTestModel(Carbon::now());

        $this->assertFalse($model->isDraft());

        $model->setPublished(false);

        // The model should now be draft...
        $this->assertTrue($model->isDraft());

        // Ensure the change was not saved...
        $this->assertTrue($model->isDirty());
    }

    /** @test */
    public function it_can_publish_or_draft_a_model_based_on_a_boolean_value()
    {
        $model = $this->newTestModel();

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

        $model = $this->newTestModel($publishedAt);

        $this->assertTrue($model->isPublished());

        // Publish without saving...
        $model->setPublished(true);

        $this->assertEquals(
            $publishedAt->toDateTimeString(),
            $model->published_at->toDateTimeString()
        );

        // Publish and save...
        $model->publish(true);

        $this->assertEquals(
            $publishedAt->toDateTimeString(),
            $model->published_at->toDateTimeString()
        );
    }

    /** @test */
    public function it_can_schedule_a_model_to_be_published()
    {
        $model = $this->createTestModel(Carbon::now());

        $this->assertTrue($model->isPublished());

        $publishDate = Carbon::now()->startOfDay()->addWeek();

        $model->publishAt($publishDate);

        $this->assertFalse($model->isPublished());

        $this->assertEquals(
            $publishDate->toDateTimeString(),
            $model->published_at->toDateTimeString()
        );

        // Ensure the change was saved...
        $this->assertTrue($model->isClean());

        Carbon::setTestNow($publishDate);

        $this->assertTrue($model->isPublished());
    }

    /** @test */
    public function it_can_schedule_a_model_to_be_published_without_saving()
    {
        $model = $this->createTestModel(Carbon::now());

        $this->assertTrue($model->isPublished());

        $publishDate = Carbon::now()->startOfDay()->addWeek();

        $model->setPublishedAt($publishDate);

        $this->assertFalse($model->isPublished());

        $this->assertEquals(
            $publishDate->toDateTimeString(),
            $model->published_at->toDateTimeString()
        );

        // Ensure the change was not saved...
        $this->assertTrue($model->isDirty());

        Carbon::setTestNow($publishDate);

        $this->assertTrue($model->isPublished());
    }

    /**
     * @test
     *
     * @param Carbon $now
     * @param mixed $input
     * @param Carbon $expected
     *
     * @dataProvider acceptedDates
     */
    public function it_can_accept_the_publish_date_in_multiple_formats($now, $input, $expected)
    {
        $model = $this->createTestModel();

        Carbon::setTestNow($now);

        $model->setPublishedAt($input);

        $this->assertEquals(
            $expected->toDateTimeString(),
            $model->published_at->toDateTimeString()
        );

        // Ensure the change was not saved...
        $this->assertTrue($model->isDirty());

        $model->setPublished(false);

        $model->publishAt($input);

        $this->assertEquals(
            $expected->toDateTimeString(),
            $model->published_at->toDateTimeString()
        );

        // Ensure the change was saved...
        $this->assertFalse($model->isDirty());
    }

    public function acceptedDates()
    {
        $now = Carbon::now();

        return [
            [$now, $now, $now],
            [$now, $now->copy()->addDay()->toDateTimeString(), $now->copy()->addDay()],
            [$now, 'now', $now],
            [$now, '+1 week', $now->copy()->addWeek()],
        ];
    }

    /** @test */
    public function it_can_accept_a_null_publish_date_to_indefinitely_draft_a_model()
    {
        $model = $this->createTestModel(Carbon::now());

        $model->setPublishedAt(null);

        $this->assertTrue($model->isDraft());

        // Ensure the change was not saved...
        $this->assertTrue($model->isDirty());

        $model->setPublished(true);

        $model->publishAt(null);

        $this->assertTrue($model->isDraft());

        // Ensure the change was saved...
        $this->assertTrue($model->isClean());
    }
}
