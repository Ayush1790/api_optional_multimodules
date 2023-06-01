<?php

use Phalcon\Loader;
use Phalcon\Mvc\Micro;
use Phalcon\Di\FactoryDefault;
use Phalcon\Events\Event;
use Phalcon\Events\Manager as EventsManager;
use Phalcon\Acl\Adapter\Memory;
use handler\Token;
use handler\Curl;

require_once './vendor/autoload.php';
$loader = new Loader();

$loader->registerNamespaces(
    [
        'MyApp\Models' => __DIR__ . '/models/',
        'handler' => __DIR__ . '/handler/'
    ]
);
$loader->register();

$container = new FactoryDefault();
$container->set(
    'mongo',
    function () {
        $mongo = new MongoDB\Client('mongodb+srv://myAtlasDBUser:myatlas-001@myatlas' .
            'clusteredu.aocinmp.mongodb.net/?retryWrites=true&w=majority');
        return $mongo->robots->robot;
    },
    true
);
// Create a events manager
$eventsManager = new EventsManager();

$app = new Micro($container);

// Bind the events manager to the app
// $app->setEventsManager($eventsManager);

// Searches for robots with $name in their name
$app->get(
    '/api/robot',
    function () {
        $robot = $this->mongo->find();
        foreach ($robot as $value) {
            $result[] = [
                'id'   =>  $value->id,
                'name' =>  $value->name,
                'price' => $value->price,
                'qty' => $value->qty,
                'desc' => $value->desc,
            ];
        }
        echo json_encode($result);
    }
);

$app->get(
    '/api/robot/search/{name}',
    function ($name) {
        $result = $this->mongo->findOne(['name' => $name]);
        if (empty($result)) {
            echo "data not matched";
        } else {
            echo json_encode($result);
        }
    }
);

$app->get(
    '/api/robot/search/{id:[0-9]+}',
    function ($id) {
        $result = $this->mongo->findOne(['id' => $id]);
        if (empty($result)) {
            return  "data not matched";
        } else {
            return json_encode($result);
        }
    }
);
$app->post(
    '/api/robot',
    function () {
        $data = (json_decode(file_get_contents('php://input')));
        $res = $this->mongo->insertOne($data);
        return json_encode($res->getInsertedCount());
    }
);

$app->put(
    '/api/robot/{id:[0-9]+}',
    function ($id) {
        $data = (json_decode(file_get_contents('php://input')));
        $this->mongo->updateOne(['id' => $id], ['$set' => $data]);
        echo "data updated succesfully " . json_encode($data);
    }
);
$app->delete(
    '/api/robot/{id:[0-9]+}',
    function ($id) {
        $data = $this->mongo->findOne(['id' => $id]);
        $this->mongo->deleteOne(['id' => $id]);
        echo "data deleted succesfully " . json_encode($data);
    }
);


$app->notFound(
    function () use ($app) {
        $app->response->setStatusCode(404, 'Not Found');
        $app->response->sendHeaders();

        $message = 'Nothing to see here. Move along....';
        $app->response->setContent($message);
        $app->response->send();
    }
);

$app->handle(
    $_SERVER["REQUEST_URI"]
);
