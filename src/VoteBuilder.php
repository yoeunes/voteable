<?php

namespace Yoeunes\Voteable;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Yoeunes\Voteable\Exceptions\EmptyUser;
use Yoeunes\Voteable\Exceptions\ModelDoesNotUseVoteableTrait;
use Yoeunes\Voteable\Exceptions\UserDoestNotHaveID;
use Yoeunes\Voteable\Exceptions\VoteableModelNotFound;
use Yoeunes\Voteable\Models\Vote;
use Yoeunes\Voteable\Traits\Voteable;

class VoteBuilder
{
    protected $user;

    protected $voteable;

    protected $uniqueVoteForUsers = true;

    public function __construct()
    {
        if (config('voteable.auth_user')) {
            $this->user = auth()->id();
        }

        if (config('voteable.user_vote_once')) {
            $this->uniqueVoteForUsers = true;
        }
    }

    /**
     * @param Model|int $user
     *
     * @return VoteBuilder
     *
     * @throws \Throwable
     */
    public function user($user)
    {
        throw_if($user instanceof Model && empty($user->id), UserDoestNotHaveID::class, 'User object does not have ID');

        $this->user = $user instanceof Model ? $user->id : $user;

        return $this;
    }

    /**
     * @param Model $voteable
     *
     * @return VoteBuilder
     *
     * @throws \Throwable
     */
    public function voteable(Model $voteable)
    {
        throw_unless(in_array(Voteable::class, class_uses_recursive($voteable)), ModelDoesNotUseVoteableTrait::class, get_class($voteable).' does not use the Voteable Trait');

        $this->voteable = $voteable;

        return $this;
    }

    /**
     * @param bool $unique
     *
     * @return VoteBuilder
     */
    public function uniqueVoteForUsers(bool $unique)
    {
        $this->uniqueVoteForUsers = $unique;

        return $this;
    }

    /**
     * @param int $amount
     *
     * @return Vote
     *
     * @throws \Throwable
     */
    public function amount(int $amount)
    {
        throw_if(empty($this->user), EmptyUser::class, 'Empty user');

        throw_if(empty($this->voteable->id), VoteableModelNotFound::class, 'Voteable model not found');

        $data = [
            'user_id'       => $this->user,
            'voteable_id'   => $this->voteable->id,
            'voteable_type' => in_array(get_class($this->voteable), Relation::morphMap()) ? array_search(get_class($this->voteable), Relation::morphMap()) : get_class($this->voteable),
        ];

        $voteModel = config('voteable.vote');

        $vote = $this->uniqueVoteForUsers ? (new $voteModel)->firstOrNew($data) : (new $voteModel)->fill($data);

        $vote->amount = $amount;

        $vote->save();

        return $vote;
    }

    /**
     * @return Vote
     *
     * @throws \Throwable
     */
    public function voteUp()
    {
        return $this->amount(config('voteable.amount.up'));
    }

    /**
     * @return Vote
     *
     * @throws \Throwable
     */
    public function voteDown()
    {
        return $this->amount(config('voteable.amount.down'));
    }
}
