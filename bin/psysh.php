<?php
/*
    0. Navigate to project ROOT and type in CMD:
        composer require psy/psysh
    1. place this file in ROOT/bin/psysh.php
    2. place psysh.config.php in ROOT/bin/psysh.config.php
    3. Run:
        php bin\psysh.php
*/
require_once __DIR__ . '/../vendor/autoload.php';

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Capsule\Manager as Capsule;

function initEloquent()
{
    $capsule = new Capsule();
    $capsule->addConnection([
        'driver' => 'mysql',
        'host' => '127.0.0.1',
        'database' => 'cpc',
        'username' => 'root',
        'password' => '',
        'charset' => 'utf8',
        'collation' => 'utf8_general_ci',
        'prefix' => ''
    ]);
    $capsule->setAsGlobal();
    $capsule->bootEloquent();
    date_default_timezone_set('UTC');
    $db = $capsule->getConnection();
    return $db;
}


$db = initEloquent();

/* example ORM usage (needs 'users' and 'posts' tables having 'id' column as primary key)

    class User extends Model {
        protected $primaryKey='user_id';
        public function posts()
        {
            return $this->hasMany('Project');
        }
    }

    class Project extends Model {
        protected $primaryKey='project_id';
        public function user()
        {
            return $this->belongsTo('User');
        }
    }

    $user = User::first();
    $first_posts = $user->posts()->limit(3)->get();
    $posts = $user->posts;
*/

$configFile = __DIR__.'/psysh.config.php';
$sh = new \Psy\Shell(new \Psy\Configuration(['configFile' => $configFile]));
$sh->setScopeVariables(get_defined_vars());
$sh->run();
