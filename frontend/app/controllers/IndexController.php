<?php

namespace MyApp\Controller;

use Phalcon\Mvc\Controller;


class IndexController extends Controller
{
    public function indexAction()
    {
        $this->response->redirect('robot');
    }
}