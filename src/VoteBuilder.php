<?php

namespace Yoeunes\Rateable;

use Yoeunes\Voteable\Models\Vote;
use Yoeunes\Voteable\Traits\Voteable;
use Illuminate\Database\Eloquent\Model;
use Yoeunes\Voteable\Exceptions\EmptyUser;
use Yoeunes\Voteable\Exceptions\UserDoestNotHaveID;
use Illuminate\Database\Eloquent\Relations\Relation;
use Yoeunes\Voteable\Exceptions\VoteableModelNotFound;
use Yoeunes\Voteable\Exceptions\ModelDoesNotUseVoteableTrait;

class VoteBuilder
{
    protected $user;

    protected $voteable;

    protected $uniqueVoteForUsers = true;

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
     * @param int $score
     *
     * @return Vote
     *
     * @throws \Throwable
     */
    public function score(int $score)
    {
        throw_if(empty($this->user), EmptyUser::class, 'Empty user');

        throw_if(empty($this->voteable->id), VoteableModelNotFound::class, 'Voteable model not found');

        $data = [
            'user_id'       => $this->user,
            'voteable_id'   => $this->voteable->id,
            'voteable_type' => Relation::getMorphedModel(get_class($this->voteable)) ?? get_class($this->voteable),
        ];

        $vote = $this->uniqueVoteForUsers ? Vote::firstOrNew($data) : (new Vote())->fill($data);

        $vote->score = $score;

        $vote->save();

        return $vote;
    }
}
