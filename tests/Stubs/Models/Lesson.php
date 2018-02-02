<?php

namespace Yoeunes\Voteable\Tests\Stubs\Models;

use Yoeunes\Voteable\Traits\Voteable;
use Illuminate\Database\Eloquent\Model;

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
