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

        /** @var User $user */
        $user = Factory::create(User::class);

        Factory::create(Vote::class, ['voteable_id' => $lesson->id, 'amount' => +1, 'user_id' => $user->id]);
        Factory::create(Vote::class, ['voteable_id' => $lesson->id, 'amount' => -2, 'user_id' => $user->id]);
        Factory::create(Vote::class, ['voteable_id' => $lesson->id, 'amount' => +3]);

        $this->assertEquals(4, $lesson->upVotesCount());
        $this->assertEquals(-2, $lesson->downVotesCount());
    }
}
