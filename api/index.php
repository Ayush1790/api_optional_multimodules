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
        return $mongo->products;
    },
    true
);
// Create a events manager
$eventsManager = new EventsManager();

$eventsManager->attach(
    'micro:beforeExecuteRoute',
    function ($app) {
        $acl = new Memory();

        /**
         * Add the roles
         */
        $acl->addRole('user');
        $acl->addRole('admin');
        $acl->addComponent(
            'products',
            []
        );
        $acl->addComponent(
            'orders',
            []
        );
        $acl->allow('admin', '*', '*');
        $obj = new Token();
        if ($app->request->get('role') == 'admin') {
            $token = 'admin';
        } else {
            $token = $obj->decodeToken($app->request->get('role'));
        }
        if (!$acl->isAllowed($token, 'products', '*')) {
            echo "You are not authorised to view this.";
            die;
        }
    }
);
$app = new Micro($container);

// Bind the events manager to the app
// $app->setEventsManager($eventsManager);

// Searches for product with $name in their name
$app->get(
    '/products',
    function () {
        if (array_key_exists('per_page', $this->request->get())) {
            $perpage = $this->request->get('per_page');
        } else {
            $perpage = 2;
        }
        if (array_key_exists('page', $this->request->get())) {
            $page = $this->request->get('page');
        } else {
            $page = 0;
        }
        $product = $this->mongo->product->find([], ["limit" => (int)$perpage, "skip" => (int)$perpage * $page]);
        foreach ($product as $value) {
            $result[] = [
                'id'   =>  $value->_id,
                'name' =>  $value->name,
                'price' => $value->price,
                'color' => $value->color,
            ];
        }
        echo json_encode($result);
    }
);
$app->get(
    '/products/search/{name}',
    function ($name) {
        $product = $this->mongo->product->find();
        $data = array();
        $data = explode("%20", $name);
        foreach ($product as $products) {
            foreach ($data as $value) {
                $pattern = "/$value/i";
                if (preg_match_all($pattern, $products->name)) {
                    $result[] = [
                        'id'   =>  $products->_id,
                        'name' =>  $products->name,
                        'price' => $products->price,
                        'color' => $products->color,
                    ];
                }
            }
        }
        if (empty($result)) {
            echo "data not matched";
        } else {
            echo json_encode($result);
        }
    }
);

$app->get(
    '/findUser',
    function () {
        $res = $this->mongo->user->findOne(['$and' => [['email' => $_GET['email'], 'pswd' => $_GET['pswd']]]]);
        $data = [
            'id' => $res->_id,
            'role' => $res->role
        ];
        return json_encode($data);
    }
);

$app->post(
    '/adduser',
    function () {
        $this->mongo->user->insertOne($_POST);
    }
);

$app->post(
    '/addwebhook',
    function () {
        $this->mongo->webhook->insertOne($_POST);
    }
);

$app->post(
    '/order/create',
    function () use ($app) {
        $data = $app->request->getJsonRawBody();
        $data = (array)$data;
        $this->mongo->order->insertOne($data);
        $order = new Curl();
        $res=$this->mongo->webhook->find(['event'=>'Order.create']);
        foreach ($res as $value) {
            $res=$order->addAction($value->url, $data);
        }
    }
);

$app->post(
    '/product/create',
    function () use ($app) {
        $data = $app->request->getJsonRawBody();
        $data = (array)$data;
        $this->mongo->product->insertOne($data);
        $order = new Curl();
        $res=$this->mongo->webhook->find(['event'=>'Product.create']);
        foreach ($res as $value) {
            $res=$order->addAction($value->url, $data);
        }
    }
);

$app->put(
    '/order/update',
    function () use ($app) {
        $data = $app->request->getJsonRawBody();
        $data = (array)$data;
        $this->mongo->order->updateOne(
            ['id' => $data['id']],
            ['$set' => $data]
        );
        $order = new Curl();
        $res=$this->mongo->webhook->find(['event'=>'Order.update']);
        foreach ($res as $value) {
            $res=$order->updateAction($value->url, $data);
            print_r($res);
        }
    }
);

$app->put(
    '/product/update',
    function () use ($app) {
        $data = $app->request->getJsonRawBody();
        $data = (array)$data;
        $this->mongo->product->updateOne(
            ['id' => $data['id']],
            ['$set' => $data]
        );
        $order = new Curl();
        $res=$this->mongo->webhook->find(['event'=>'Product.update']);
        foreach ($res as $value) {
            $res=$order->updateAction($value->url, $data);
            print_r($res);
        }
    }
);

$app->get(
    '/allOrders',
    function () {
        $response = $this->mongo->order->find();
        $order = [];
        foreach ($response as $value) {
            $data = [
                'product_id' => $value->id,
                'name' => $value->name,
                'price' => $value->price,
                'qty' => $value->qty,
                'customer_name' => $value->customer_name,
                'pincode' => $value->pin
            ];
            array_push($order, $data);
        }
        return json_encode($order);
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
