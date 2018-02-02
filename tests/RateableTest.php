<?php

namespace Yoeunes\Voteable\Tests;

use Laracasts\TestDummy\Factory;
use Yoeunes\Voteable\Models\Vote;
use Yoeunes\Voteable\Tests\Stubs\Models\Lesson;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Yoeunes\Voteable\Tests\Stubs\Models\User;

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

    /** @test */
    public function it_get_up_vote_count()
    {
        /** @var Lesson $lesson */
        $lesson = Factory::create(Lesson::class);

        Factory::create(Vote::class, ['voteable_id' => $lesson->id, 'amount' => +1]);
        Factory::create(Vote::class, ['voteable_id' => $lesson->id, 'amount' => -2]);
        Factory::create(Vote::class, ['voteable_id' => $lesson->id, 'amount' => +3]);

        $this->assertEquals(4, $lesson->upVotesCount());
        $this->assertEquals(-2, $lesson->downVotesCount());
    }

    /** @test */
    public function it_test_if_a_lesson_if_upvoted_or_downvoted()
    {
        $lessons = Factory::times(2)->create(Lesson::class);

        Factory::create(Vote::class, ['voteable_id' => $lessons[0]->id, 'amount' => +1]);
        Factory::create(Vote::class, ['voteable_id' => $lessons[0]->id, 'amount' => -1]);
        Factory::create(Vote::class, ['voteable_id' => $lessons[1]->id, 'amount' => +1]);

        $this->assertTrue($lessons[0]->isUpVoted());
        $this->assertTrue($lessons[1]->isUpVoted());
        $this->assertTrue($lessons[0]->isDownVoted());
        $this->assertFalse($lessons[1]->isDownVoted());
    }
}
