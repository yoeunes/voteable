<?php

return [
    'user'           => App\User::class,
    'auth_user'      => true,
    'user_vote_once' => true,
    'amount'         => [
        'up'   => +1,
        'down' => -1,
    ],
];
