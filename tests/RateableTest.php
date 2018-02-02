<?php

namespace Yoeunes\Voteable\Tests;

use Laracasts\TestDummy\Factory;
use Yoeunes\Voteable\Models\Vote;
use Yoeunes\Voteable\Tests\Stubs\Models\Lesson;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class RateableTest extends TestCase
{
    /** @test */
    public function it_test_if_voteable_is_a_morph_to_relation()
    {
        /** @var Vote $vote */
        $vote = Factory::create(Vote::class);
        $this->assertInstanceOf(MorphTo::class, $vote->voteable());
    }

    /** @test */
    public function it_test_if_user_is_a_belongs_to_relation()
    {
        /** @var Vote $vote */
        $vote = Factory::create(Vote::class);
        $this->assertInstanceOf(BelongsTo::class, $vote->user());
    }

    /** @test */
    public function it_test_if_votes_is_a_morph_many_relation()
    {
        /** @var Lesson $lesson */
        $lesson = Factory::create(Lesson::class);
        $this->assertInstanceOf(MorphMany::class, $lesson->votes());
    }
}
