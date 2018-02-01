<?php

namespace Yoeunes\Voteable\Traits;

use Yoeunes\Voteable\Models\Vote;

trait CanVote
{
    public function votes()
    {
        return $this->hasMany(Vote::class);
    }
}
