<?php

namespace Yoeunes\Voteable;

use Yoeunes\Voteable\Traits\Voteable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\JoinClause;
use Yoeunes\Voteable\Exceptions\UserDoestNotHaveID;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Yoeunes\Voteable\Exceptions\ModelDoesNotUseVoteableTrait;

class VoteQueryBuilder
{
    protected $query = null;

    public function __construct(MorphMany $query)
    {
        $this->query = $query;
    }

    public function from($date)
    {
        $this->query = $this->query->where('created_at', '>=', date_transformer($date));

        return $this;
    }

    public function to($date)
    {
        $this->query = $this->query->where('created_at', '<=', date_transformer($date));

        return $this;
    }

    /**
     * @param $user
     *
     * @return VoteQueryBuilder
     *
     * @throws \Throwable
     */
    public function user($user)
    {
        throw_if($user instanceof Model && empty($user->id), UserDoestNotHaveID::class, 'User object does not have ID');

        $this->query = $this->query->where('user_id', $user instanceof Model ? $user->id : $user);

        return $this;
    }

    /**
     * @param Model $voteable
     *
     * @return VoteQueryBuilder
     *
     * @throws \Throwable
     */
    public function voteable(Model $voteable)
    {
        throw_unless(in_array(Voteable::class, class_uses_recursive($voteable)), ModelDoesNotUseVoteableTrait::class, get_class($voteable).' does not use the Voteable Trait');

        $this->query = $this->query
             ->leftJoin('votes', function (JoinClause $join) use ($voteable) {
                 $join
                     ->on('votes.voteable_id', $voteable->getTable() . '.id')
                     ->where('votes.voteable_type', Relation::getMorphedModel(get_class($voteable)) ?? get_class($voteable));
             });

        return $this;
    }

    public function getQuery()
    {
        return $this->query;
    }
}
