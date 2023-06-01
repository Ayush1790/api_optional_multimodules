<?php

namespace MyApp\Controller;

use Phalcon\Mvc\Controller;
use MyApp\Component\Robot;

class RobotController extends Controller
{
    public function indexAction()
    {
        //redirect to view
    }

    public function robotaddAction()
    {
        $data = [
            'name' => $this->request->get('name'),
            'id' => $this->request->get('id'),
            'qty' => $this->request->get('qty'),
            'price' => $this->request->get('price'),
            'desc' => $this->request->get('desc'),
        ];
        $robot = new Robot();
        $res = $robot->add($data);
        if ($res) {
            echo "robot Added Succesfullly....";
            echo "<br><a href='../robot' class='btn btn-outline-warning'>Back</a>";
        } else {
            echo "Something Went Wrong....";
            echo "<br><a href='../robot' class='btn btn-outline-warning'>Back</a>";
        }
    }

    public function robotviewAction()
    {
        $robot = new Robot();
        $res = $robot->view();
        $this->view->data = $res;
    }
    public function deleteAction()
    {
        $robot = new Robot();
        $robot->delete($this->request->get('id'));
        $this->response->redirect('../robot/robotview');
    }

    public function updateAction()
    {
        $robot = new Robot();
        $res = $robot->searchById($this->request->get('id'));
        $this->view->data = $res;
    }

    public function robotupdateAction()
    {
        $data = [
            'name' => $this->request->get('name'),
            'id' => $this->request->get('id'),
            'qty' => $this->request->get('qty'),
            'price' => $this->request->get('price'),
            'desc' => $this->request->get('desc'),
        ];
        $robot = new Robot();
        $robot->update($data);
        $this->response->redirect('../robot/robotview');
    }
}