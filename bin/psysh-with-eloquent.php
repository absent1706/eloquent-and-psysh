<?php
/*
    0. Navigate to project ROOT and type in CMD:
        composer require psy/psysh illuminate/database doctrine/dbal illuminate/events
    1. place this file in ROOT/bin/psysh.php
    2. place psysh.config.php in ROOT/bin/psysh.config.php
    3. Run:
        php bin\psysh.php
*/
require_once __DIR__ . '/../vendor/autoload.php';

// begin init Eloquent
    use Illuminate\Database\Eloquent\Model;
    use Illuminate\Database\Capsule\Manager as Capsule;
    use Illuminate\Events\Dispatcher;
    use Illuminate\Container\Container;

    function initEloquent($container)
    {
        $capsule = new Capsule($container);
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
        // $capsule->setAsGlobal();
        $capsule->bootEloquent();
        date_default_timezone_set('UTC');
        $db = $capsule->getConnection();
        return $db;
    }

    $container = new Container;
    $dispatcher = new Dispatcher;
    $container['events'] = $dispatcher;
    $db = initEloquent($container);

    // begin init SQL logger
        $dispatcher->listen('Illuminate\Database\Events\QueryExecuted', function ($query) {
            $msg =  "== SQL: ".$query->sql."\n";
            $msg .= "== Params: ".join(', ', $query->bindings);
            $msg .= "\n\n";

            // if code is executed in CLI, echo message
            if (php_sapi_name() == 'cli') {
                echo $msg;
            }
            // if code executed by server, log message so stderr
            else {
                $msg = "[".date("Y-m-d H:i:s")."]\n" . $msg;
                file_put_contents(__DIR__.'/db.log', $msg, FILE_APPEND); // log into file
                error_log($msg); // log into stderr. usable in php builtin server
            }
        });
    // end init SQL logger
// end init Eloquent


/* example ORM usage (needs 'users' and 'posts' tables having 'id' column as primary key)

    class User extends Model {
        protected $primaryKey='user_id';
        public function posts()
        {
            return $this->hasMany('Post');
        }
    }

    class Post extends Model {
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
