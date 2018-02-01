<?php

namespace Yoeunes\Voteable\Traits;

use Yoeunes\Rateable\VoteBuilder;
use Yoeunes\Voteable\Models\Vote;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Database\Eloquent\Relations\Relation;

trait Voteable
{
    /**
     * This model has many votes.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function votes()
    {
        return $this->morphMany(Vote::class, 'voteable');
    }

    /**
     * @return mixed
     */
    public function upVotesCount()
    {
        return $this->votes()->where('score', '>=', 0)->sum('score');
    }

    public function likesCount()
    {
        return $this->upVotesCount();
    }

    /**
     * @return mixed
     */
    public function downVotesCount()
    {
        return $this->votes()->where('score', '<', 0)->sum('score');
    }

    public function dislikesCount()
    {
        return $this->downVotesCount();
    }

    public function unlikesCount()
    {
        return $this->downVotesCount();
    }

    /**
     * @return bool
     */
    public function isUpVoted()
    {
        return $this->votes()->where('score', '>=', 0)->exists();
    }

    public function isLiked()
    {
        return $this->isUpVoted();
    }

    /**
     * @return bool
     */
    public function isDownVoted()
    {
        return $this->votes()->where('score', '<', 0)->exists();
    }

    /**
     * @param int $user_id
     *
     * @return bool
     */
    public function isUpVotedByUser(int $user_id)
    {
        return $this->votes()->where('score', '>=', 0)->where('user_id', $user_id)->exists();
    }

    public function isLikedByUser(int $user_id)
    {
        return $this->isUpVotedByUser($user_id);
    }

    /**
     * @param int $user_id
     *
     * @return bool
     */
    public function isDownVotedByUser(int $user_id)
    {
        return $this->votes()->where('score', '<', 0)->where('user_id', $user_id)->exists();
    }

    public function isDislikedByUser(int $user_id)
    {
        return $this->isDownVotedByUser($user_id);
    }

    public function isUnlikedByUser(int $user_id)
    {
        return $this->isDownVotedByUser($user_id);
    }

    /**
     * @param Builder $query
     * @param string $direction
     * @param string $type
     *
     * @return Builder
     */
    public function scopeOrderByUpVotes(Builder $query, string $direction = 'asc', string $type = '>=')
    {
        return $query
            ->leftJoin('votes', function (JoinClause $join) {
                $join
                    ->on('votes.voteable_id', $this->getTable() . '.id')
                    ->where('votes.voteable_type', Relation::getMorphedModel(__CLASS__) ?? __CLASS__);
            })
            ->where('score', $type, 0)
            ->addSelect(DB::raw('SUM(votes.value) as count_votes'))
            ->groupBy($this->getTable(). '.id')
            ->orderBy('count_votes', $direction);
    }

    public function scopeOrderByLikes(Builder $query, string $direction = 'asc', string $type = '>=')
    {
        return $this->scopeOrderByUpVotes($query, $direction, $type);
    }

    /**
     * @param Builder $query
     * @param string $direction
     *
     * @return Builder
     */
    public function scopeOrderByDownVotes(Builder $query, string $direction = 'asc')
    {
        return $this->scopeOrderByUpVotes($query, $direction, '<');
    }

    public function scopeOrderByDislikes(Builder $query, string $direction = 'asc')
    {
        return $this->scopeOrderByDownVotes($query, $direction);
    }

    /**
     * @param int $vote_id
     *
     * @return mixed
     */
    public function deleteVote(int $vote_id)
    {
        return $this->votes()->where('id', $vote_id)->delete();
    }

    public function deleteLike(int $like_id)
    {
        return $this->deleteVote($like_id);
    }

    /**
     * @return mixed
     */
    public function resetVotes()
    {
        return $this->votes()->delete();
    }

    public function resetLikes()
    {
        return $this->resetVotes();
    }

    /**
     * @param int $user_id
     *
     * @return mixed
     */
    public function deleteVotesForUser(int $user_id)
    {
        return $this->votes()->where('user_id', $user_id)->delete();
    }

    public function deleteLikesForUser(int $user_id)
    {
        return $this->deleteVotesForUser($user_id);
    }

    /**
     * @param int $user_id
     * @param int $score
     *
     * @return int
     */
    public function updateVotesForUser(int $user_id, int $score)
    {
        return $this->votes()->where('user_id', $user_id)->update(['score' => $score]);
    }

    public function updateLikesForUser(int $user_id, int $score)
    {
        return $this->updateVotesForUser($user_id, $score);
    }

    /**
     * @param int $vote_id
     * @param int $score
     *
     * @return int
     */
    public function updateVote(int $vote_id, int $score)
    {
        return $this->votes()->where('id', $vote_id)->update(['score' => $score]);
    }

    public function updateLike(int $vote_id, int $score)
    {
        return $this->updateVote($vote_id, $score);
    }

    /**
     * @return VoteBuilder
     *
     * @throws \Throwable
     */
    public function getRatingBuilder()
    {
        return (new VoteBuilder())
            ->voteable($this);
    }
}
