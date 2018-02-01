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
        return $this->votes()->where('amount', '>=', 0)->sum('amount');
    }

    /**
     * @return mixed
     */
    public function downVotesCount()
    {
        return $this->votes()->where('amount', '<', 0)->sum('amount');
    }

    /**
     * @return bool
     */
    public function isUpVoted()
    {
        return $this->votes()->where('amount', '>=', 0)->exists();
    }

    /**
     * @return bool
     */
    public function isDownVoted()
    {
        return $this->votes()->where('amount', '<', 0)->exists();
    }

    /**
     * @param int $user_id
     *
     * @return bool
     */
    public function isUpVotedByUser(int $user_id)
    {
        return $this->votes()->where('amount', '>=', 0)->where('user_id', $user_id)->exists();
    }

    /**
     * @param int $user_id
     *
     * @return bool
     */
    public function isDownVotedByUser(int $user_id)
    {
        return $this->votes()->where('amount', '<', 0)->where('user_id', $user_id)->exists();
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
            ->where('amount', $type, 0)
            ->addSelect(DB::raw('SUM(votes.value) as count_votes'))
            ->groupBy($this->getTable(). '.id')
            ->orderBy('count_votes', $direction);
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

    /**
     * @param int $vote_id
     *
     * @return mixed
     */
    public function deleteVote(int $vote_id)
    {
        return $this->votes()->where('id', $vote_id)->delete();
    }

    /**
     * @return mixed
     */
    public function resetVotes()
    {
        return $this->votes()->delete();
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

    /**
     * @param int $user_id
     * @param int $amount
     *
     * @return int
     */
    public function updateVotesForUser(int $user_id, int $amount)
    {
        return $this->votes()->where('user_id', $user_id)->update(['amount' => $amount]);
    }

    /**
     * @param int $vote_id
     * @param int $amount
     *
     * @return int
     */
    public function updateVote(int $vote_id, int $amount)
    {
        return $this->votes()->where('id', $vote_id)->update(['amount' => $amount]);
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
