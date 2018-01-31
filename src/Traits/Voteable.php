<?php

namespace Yoeunes\Voteable\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Facades\DB;
use Yoeunes\Voteable\Models\Vote;

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

    public function upVotesCount()
    {
        return $this->votes()->where('score', '>=', 0)->sum('score');
    }

    public function downVotesCount()
    {
        return $this->votes()->where('score', '<', 0)->sum('score');
    }

    public function isUpVotes()
    {
        return $this->votes()->where('score', '>=', 0)->exists();
    }

    public function isDownVoted()
    {
        return $this->votes()->where('score', '<', 0)->exists();
    }

    public function isUpVotedByUser(int $user_id)
    {
        return $this->votes()->where('score', '>=', 0)->where('user_id', $user_id)->exists();
    }

    public function isDownVotedByUser(int $user_id)
    {
        return $this->votes()->where('score', '<', 0)->where('user_id', $user_id)->exists();
    }

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

    public function scopeOrderByDownVotes(Builder $query, string $direction = 'asc')
    {
        return $this->scopeOrderByUpVotes($query, $direction, '<');
    }
}
