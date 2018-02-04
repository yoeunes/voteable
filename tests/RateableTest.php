<?php

namespace Yoeunes\Voteable\Tests;

use Laracasts\TestDummy\Factory;
use Yoeunes\Voteable\Models\Vote;
use Yoeunes\Voteable\Tests\Stubs\Models\User;
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

        $user = Factory::create(User::class);

        Factory::create(Vote::class, ['voteable_id' => $lessons[0]->id, 'user_id' => $user->id, 'amount' => +1]);
        Factory::create(Vote::class, ['voteable_id' => $lessons[0]->id, 'amount' => -1]);
        Factory::create(Vote::class, ['voteable_id' => $lessons[1]->id, 'amount' => +1]);

        $this->assertTrue($lessons[0]->isUpVoted());
        $this->assertTrue($lessons[1]->isUpVoted());
        $this->assertTrue($lessons[0]->isDownVoted());
        $this->assertFalse($lessons[1]->isDownVoted());
        $this->assertTrue($lessons[0]->isVotedByUser($user->id));
        $this->assertFalse($lessons[1]->isUpVotedByUser($user->id));
    }

    /** @test */
    public function it_vote_lesson_using_vote_builder()
    {
        /** @var Lesson */
        $lesson = Factory::create(Lesson::class);

        /** @var User $user */
        $user = Factory::create(User::class);

        $rating = $lesson
            ->getVoteBuilder()
            ->user($user)
            ->voteUp();

        $this->assertEquals(1, $lesson->upVotesCount());
        $this->assertEquals($rating->amount, $lesson->upVotesCount());
    }

    /** @test */
    public function it_test_cancel_vote()
    {
        /** @var Lesson $lesson */
        $lesson = Factory::create(Lesson::class);

        Factory::create(Vote::class, ['voteable_id' => $lesson->id, 'amount' => +1]);
        Factory::create(Vote::class, ['voteable_id' => $lesson->id, 'amount' => -2]);
        $vote = Factory::create(Vote::class, ['voteable_id' => $lesson->id, 'amount' => +3]);

        $this->assertEquals(4, $lesson->upVotesCount());

        $lesson->cancelVote($vote->id);
        $this->assertEquals(1, $lesson->upVotesCount());
    }

    /** @test */
    public function it_test_cancel_vote_for_a_user()
    {
        /** @var Lesson $lesson */
        $lesson = Factory::create(Lesson::class);

        $user = Factory::create(User::class);

        Factory::create(Vote::class, ['voteable_id' => $lesson->id, 'user_id' => $user->id, 'amount' => +1]);
        Factory::create(Vote::class, ['voteable_id' => $lesson->id, 'user_id' => $user->id, 'amount' => -1]);
        Factory::create(Vote::class, ['voteable_id' => $lesson->id, 'amount' => +1]);

        $lesson->cancelVotesForUser($user->id);
        $this->assertEquals(1, $lesson->upVotesCount());
    }

    /** @test */
    public function it_test_reset_votes_for_a_lesson()
    {
        $lessons = Factory::times(3)->create(Lesson::class);

        Factory::times(3)->create(Vote::class, ['voteable_id' => $lessons[0]->id, 'amount' => +1]);
        Factory::create(Vote::class, ['voteable_id' => $lessons[1]->id, 'amount' => +1]);
        Factory::create(Vote::class, ['voteable_id' => $lessons[2]->id, 'amount' => +1]);

        $lessons[0]->resetVotes();

        $this->assertEquals(0, $lessons[0]->upVotesCount());
        $this->assertEquals(1, $lessons[1]->upVotesCount());
        $this->assertEquals(1, $lessons[2]->upVotesCount());
    }

    /** @test */
    public function it_test_update_vote_for_a_user()
    {
        /** @var Lesson $lesson */
        $lesson = Factory::create(Lesson::class);

        $user = Factory::create(User::class);

        Factory::create(Vote::class, ['voteable_id' => $lesson->id, 'user_id' => $user->id, 'amount' => +1]);

        $this->assertEquals(1, $lesson->upVotesCount());

        $lesson->updateVotesForUser($user->id, 3);

        $this->assertEquals(3, $lesson->upVotesCount());
    }

    /** @test */
    public function it_test_update_vote()
    {
        /** @var Lesson $lesson */
        $lesson = Factory::create(Lesson::class);

        $vote = Factory::create(Vote::class, ['voteable_id' => $lesson->id, 'amount' => +1]);

        $this->assertEquals(1, $lesson->upVotesCount());

        $lesson->updateVote($vote->id, 3);

        $this->assertEquals(3, $lesson->upVotesCount());
    }

    /** @test */
    public function it_get_voters_for_a_specific_lesson()
    {
        /** @var Lesson $lesson */
        $lesson = Factory::create(Lesson::class);

        $users = Factory::times(2)->create(User::class);

        Factory::create(Vote::class, ['voteable_id' => $lesson->id, 'user_id' => $users[0]->id]);
        Factory::create(Vote::class, ['voteable_id' => $lesson->id, 'user_id' => $users[1]->id]);
        Factory::times(3)->create(Vote::class, ['voteable_id' => $lesson->id]);
        Factory::times(4)->create(Vote::class);

        $this->assertCount(5, $lesson->voters()->get());
    }

    /** @test */
    public function it_get_votes_from_start_date()
    {
        /** @var Lesson $lesson */
        $lesson = Factory::create(Lesson::class);

        $votes = Factory::times(5)->create(Vote::class, ['voteable_id' => $lesson->id, 'amount' => 1]);

        $votes[0]->created_at = '2018-02-01 11:21:01';
        $votes[0]->save();
        $votes[1]->created_at = '2018-02-02 12:22:02';
        $votes[1]->save();
        $votes[2]->created_at = '2018-02-03 13:23:03';
        $votes[2]->save();
        $votes[3]->created_at = '2018-02-04 14:24:04';
        $votes[3]->save();
        $votes[4]->created_at = '2018-02-05 15:25:05';
        $votes[4]->save();

        $this->assertEquals(5, $lesson->countVotesByDate());
        $this->assertEquals(3, $lesson->countVotesByDate('2018-02-03 13:23:03'));
        $this->assertEquals(0, $lesson->countVotesByDate('2018-02-06 15:26:06'));
        $this->assertEquals(2, $lesson->countVotesByDate('2018-02-03 13:23:03', '2018-02-04 14:24:04'));
        $this->assertEquals(2, $lesson->countVotesByDate(null, '2018-02-02 12:22:02'));
    }
}
