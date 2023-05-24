<?php

namespace MyApp\Controller;

use Phalcon\Escaper;
use Phalcon\Mvc\Controller;

class UserdashboardController extends Controller
{
    public function indexAction()
    {
        //redirect to view
    }

    public function addwebhookAction()
    {
        $escaper = new Escaper();
        $data = array(
            'url' =>  $escaper->escapeHtml($this->request->getPost('url')),
            'event' => $escaper->escapeHtml($this->request->getPost('event')),
        );
        // set post fields
        $ch = curl_init();
        $url = 'http://172.26.0.3/addwebhook';
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        // execute!
        curl_exec($ch);
        $this->response->redirect('../userdashboard/index');
    }
}
