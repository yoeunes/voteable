<?php

namespace Yoeunes\Voteable\Tests;

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Yoeunes\Voteable\Tests\Stubs\Models\User;
use Yoeunes\Voteable\VoteableServiceProvider;
use Orchestra\Testbench\TestCase as BaseTestCase;

class TestCase extends BaseTestCase
{
    protected function getPackageProviders($app)
    {
        return [
            VoteableServiceProvider::class,
        ];
    }

    public function tearDown()
    {
        Schema::drop('votes');
        Schema::drop('lessons');
        Schema::drop('users');
    }

    /**
     * @param \Illuminate\Foundation\Application $app
     */
    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('database.default', 'testbench');

        $app['config']->set('database.connections.testbench', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]);

        $app['config']->set('app', [
            'name'   => 'voteable',
            'locale' => 'en',
            'key'    => 'base64:O30Ogm4MaKjrqSAXq5okDox31Yt3MRn6eUjKymabybw=',
            'cipher' => 'AES-256-CBC',
        ]);

        $app['config']->set('auth', [
            'defaults' => [
                'guard'     => 'web',
                'passwords' => 'users',
            ],
            'guards' => [
                'web' => [
                    'driver'   => 'session',
                    'provider' => 'users',
                ],

                'api' => [
                    'driver'   => 'token',
                    'provider' => 'users',
                ],
            ],
            'providers'    => [
                'users' => [
                    'driver' => 'eloquent',
                    'model'  => User::class,
                ],
            ],
        ]);

        $app['config']->set('voteable', [
            'vote'           => \Yoeunes\Voteable\Models\Vote::class,
            'user'           => User::class,
            'auth_user'      => true,
            'user_vote_once' => true,
            'amount'         => [
                'up'   => +1,
                'down' => -1,
            ],
        ]);

        Schema::create('lessons', function (Blueprint $table) {
            $table->increments('id');

            $table->string('title');
            $table->string('subject');

            $table->timestamps();
        });

        Schema::create('votes', function (Blueprint $table) {
            $table->increments('id');

            $table->integer('amount');
            $table->morphs('voteable');

            $table->integer('user_id')->unsigned();
            $table->foreign('user_id')->references('id')->on('users');

            $table->timestamps();
        });

        Schema::create('users', function (Blueprint $table) {
            $table->increments('id');

            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');

            $table->rememberToken();

            $table->timestamps();
        });
    }
}
