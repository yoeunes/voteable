<?php

namespace Yoeunes\Voteable\Tests\Stubs\Models;

use Illuminate\Database\Eloquent\Model;
use Yoeunes\Voteable\Traits\Voteable;

class Lesson extends Model
{
    use Voteable;

    protected $connection = 'testbench';

    protected $fillable = [
        'title',
        'subject',
    ];

    protected $appends = ['average_rating'];
}
