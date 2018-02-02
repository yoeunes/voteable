<?php

use Yoeunes\Voteable\Models\Vote;
use Yoeunes\Voteable\Tests\Stubs\Models\User;
use Yoeunes\Voteable\Tests\Stubs\Models\Lesson;

$factory(Lesson::class, [
    'title'   => $faker->sentence,
    'subject' => $faker->words(2),
]);

$factory(Vote::class, [
    'amount'        => $faker->numberBetween(config('voteable.amount.down'), config('voteable.amount.up')),
    'user_id'       => 'factory:Yoeunes\Voteable\Tests\Stubs\Models\User',
    'voteable_id'   => 'factory:Yoeunes\Voteable\Tests\Stubs\Models\Lesson',
    'voteable_type' => Lesson::class,
]);

$factory(User::class, [
    'name'           => $faker->name,
    'email'          => $faker->unique()->safeEmail,
    'password'       => bcrypt('secret'),
    'remember_token' => str_random(10),
]);
