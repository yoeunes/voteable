<h1 align="center">Laravel 5 Voting System</h1>

<p align="center">:+1:  :-1:  This package helps you to add user based voting system to your model.</p>

<p align="center">
    <a href="https://travis-ci.org/yoeunes/voteable"><img src="https://travis-ci.org/yoeunes/voteable.svg?branch=master" alt="Build Status"></a>
    <a href="https://packagist.org/packages/yoeunes/voteable"><img src="https://poser.pugx.org/yoeunes/voteable/v/stable" alt="Latest Stable Version"></a>
    <a href="https://packagist.org/packages/yoeunes/voteable"><img src="https://poser.pugx.org/yoeunes/voteable/v/unstable" alt="Latest Unstable Version"></a>
    <a href="https://scrutinizer-ci.com/g/yoeunes/voteable/build-status/master"><img src="https://scrutinizer-ci.com/g/yoeunes/voteable/badges/build.png?b=master" alt="Build Status"></a>
    <a href="https://scrutinizer-ci.com/g/yoeunes/voteable/?branch=master"><img src="https://scrutinizer-ci.com/g/yoeunes/voteable/badges/quality-score.png?b=master" alt="Scrutinizer Code Quality"></a>
    <a href="https://scrutinizer-ci.com/g/yoeunes/voteable/?branch=master"><img src="https://scrutinizer-ci.com/g/yoeunes/voteable/badges/coverage.png?b=master" alt="Code Coverage"></a>
    <a href="https://packagist.org/packages/yoeunes/voteable"><img src="https://poser.pugx.org/yoeunes/voteable/downloads" alt="Total Downloads"></a>
    <a href="https://packagist.org/packages/yoeunes/voteable"><img src="https://poser.pugx.org/yoeunes/voteable/license" alt="License"></a>
</p>

You can install the package using composer

```sh
$ composer require yoeunes/voteable
```

Then add the service provider to `config/app.php`. In Laravel versions 5.5 and beyond, this step can be skipped if package auto-discovery is enabled.

```php
'providers' => [
    ...
    Yoeunes\Voteable\VoteableServiceProvider::class
    ...
];
```

Publish the migrations file:

```sh
$ php artisan vendor:publish --provider='Yoeunes\Voteable\VoteableServiceProvider' --tag="migrations"
```

As optional if you want to modify the default configuration, you can publish the configuration file:
 
```sh
$ php artisan vendor:publish --provider='Yoeunes\Voteable\VoteableServiceProvider' --tag="config"
```

And create tables:

```php
$ php artisan migrate
```

Finally, add feature trait into User model:

```php
<?php

namespace App;

use Yoeunes\Voteable\Traits\Voteable;
use Illuminate\Database\Eloquent\Model;

class Lesson extends Model
{
    use Voteable;
}
```

## Usage


All available APIs are listed below.

#### `Yoeunes\Voteable\Traits\Voteable`

### Create a vote
```php
$user   = User::first();
$lesson = Lesson::first();

$rating = $lesson->getVoteBuilder()
                 ->user($user) // you may also use $user->id
                 ->uniqueVoteForUsers(true) // update if already rated
                 ->amount(3);
```

### Update a vote
```php
$lesson = Lesson::first();

$lesson->updateVote($vote_id, $amount); // vote_id and the new amount value
$lesson->updateVotesForUser($user_id, $amount); // update all votes for a single user related to the lesson
```

### cancel a vote:
```php
$lesson = Lesson::first();
$lesson->cancelVote($vote_id); // delete a vote with the giving id
$lesson->cancelVotesForUser($user_id); // delete all votes for a single user related to the lesson
$lesson->resetVotes(); // delete all rating related to the lesson
```

### check if a model is already votes:
```php
$lesson->isVoted();
$lesson->isUpVoted();
$lesson->isDownVoted();
$lesson->isUpVotedBy($user_id);// check if its already up voted by the given user
$lesson->isDownVotedBy($user_id);// check if its already up voted by the given user
```

### Fetch the votes count:
```php
$lesson->upVotesCount();
$lesson->downVotesCount();
$lesson->votesCount(); // get the votes count (up votes + down votes)
```

### get list of users who voted a model (voters):
```php
$lesson->voters()->get();
$lesson->voters()->where('name', 'like', '%yoeunes%')->get();
$lesson->voters()->orderBy('name')->get();
```

### get count votes between by dates
```php
$lesson->countVotesByDate('2018-02-03 13:23:03', '2018-02-06 15:26:06');
$lesson->countVotesByDate('2018-02-03 13:23:03');
$lesson->countVotesByDate(null, '2018-02-06 15:26:06');
$lesson->countVotesByDate(Carbon::now()->parse('01-04-2017'), Carbon::now()->parse('01-06-2017'));
$lesson->countVotesByDate(Carbon::now()->subDays(2));
```

### other api methods:
```php
Lesson::select('lessons.*')->orderByAverageUpVotes('asc')->get()
Lesson::select('lessons.*')->orderByAverageDownVotes('desc')->get()
```

### Query relations

```php
$ratings = $user->votes
$ratings = $user->votes()->where('id', '>', 10)->get()
```

### date transformer

Because we all love having to repeat less, this package allows you to define date transformers. Let's say we are using the following code a lot: $lesson->countRatingsByDate(Carbon::now()->subDays(3)). It can get a little bit annoying and unreadable. Let's solve that!

If you've published the configuration file, you will see something like this:

```php
'date-transformers' => [
    // 'past24hours' => Carbon::now()->subDays(1),
    // 'past7days'   => Carbon::now()->subWeeks(1),
    // 'past14days'  => Carbon::now()->subWeeks(2),
],
```

They are all commented out as default. To make them available, simply uncomment them. The provided ones are serving as an example. You can remove them or add your own ones.

```php
'date-transformers' => [
    'past3days' => Carbon::now()->subDays(3),
],
```

We can now retrieve the rating count like this:

```php
$lesson->countVotesByDate('past3days');
```



## License

MIT
