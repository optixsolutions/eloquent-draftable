<?php

namespace Optix\Draftable\Tests;

class DraftableTest extends TestCase
{
    /** @test */
    public function it_will_save_a_model_as_draft()
    {
        $model = TestModel::create(['title' => 'Test']);

        $this->assertFalse($model->isPublished());
    }
    
    /** @test */
    public function it_can_publish_a_model()
    {
        $methodOne = TestModel::create([
            'title' => 'Method one',
            'published_at' => now()
        ]);

        $methodTwo = TestModel::create(['title' => 'Method two']);
        $methodTwo->publish();

        $this->assertTrue($methodOne->isPublished());
        $this->assertTrue($methodTwo->isPublished());
    }
    
    /** @test */
    public function it_will_exclude_draft_models_from_query_results()
    {
        //
    }
}
