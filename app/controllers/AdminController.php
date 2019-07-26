<?php

use Phalcon\Mvc\Model\Criteria;
use Phalcon\Paginator\Adapter\Model as Paginator;


class AdminController extends ControllerBase
{
    /**
     * Index action
     */
    public function indexAction()
    {
        $this->view->users = Users::find();
        $this->view->banners = Banners::find(['order' => 'position DESC']);
        //$this->persistent->parameters = null;
    }

    /**
     * Searches for banners
     */
    public function searchAction()
    {
        $numberPage = 1;

        // !!!!!!!!!!!
        if ($this->request->isPost()) {
            $query = Criteria::fromInput($this->di, 'Banners', $_POST);
            $this->persistent->parameters = $query->getParams();
        } else {
            $numberPage = $this->request->getQuery("page", "int");
        }

        $parameters = $this->persistent->parameters;
        if (!is_array($parameters)) {
            $parameters = [];
        }
        $parameters["order"] = "id";

        $banners = Banners::find($parameters);
        if (count($banners) == 0) {
            $this->flash->notice("The search did not find any banners");

            $this->dispatcher->forward([
                "controller" => "admin",
                "action" => "index"
            ]);

            return;
        }

        $paginator = new Paginator([
            'data' => $banners,
            'limit'=> 2,
            'page' => $numberPage
        ]);

        $this->view->page = $paginator->getPaginate();
    }

    /**
     * Displays the creation form
     */
    public function newAction()
    {

    }

    /**
     * Edits a banner
     *
     * @param string $id
     */
    public function editAction($id)
    {
        if (!$this->request->isPost()) {

            $banner = Banners::findFirstByid($id);
            if (!$banner) {
                $this->flash->error("banner was not found");

                $this->dispatcher->forward([
                    'controller' => "admin",
                    'action' => 'index'
                ]);

                return;
            }

            $this->view->id = $banner->id;

            $this->tag->setDefault("id", $banner->id);
            $this->tag->setDefault("name", $banner->name);
            $this->tag->setDefault("url", $banner->url);
            $this->tag->setDefault("status", $banner->status);
            $this->tag->setDefault("position", $banner->position);

        }
        if ($this->request->isPost()) {

            $banner = new Banners();
            $banner->name = $this->request->getPost("name");
            $banner->url = $this->request->getPost("url");
            $banner->status = $this->request->getPost("status");
            $banner->position = $this->request->getPost("position");


            if (!$banner->save()) {
                foreach ($banner->getMessages() as $message) {
                    $this->flash->error($message);
                }

                $this->dispatcher->forward([
                    'controller' => "admin",
                    'action' => 'new'
                ]);

                return;
            }

            $this->flash->success("banner was edit successfully");

            $this->dispatcher->forward([
                'controller' => "admin",
                'action' => 'index'
            ]);

        }
    }


    public function createAction()
    {
        if (!$this->request->isPost()) {
            $this->dispatcher->forward([
                'controller' => "admin",
                'action' => 'index'
            ]);

            return;
        }

        if ($this->request->hasFiles()) {

            $url = $this->uploadImage($this->request->getUploadedFiles());
            if (!$url) {
                foreach ($banner->getMessages() as $message) {
                    $this->flash->error($message);
                }

                $this->dispatcher->forward([
                    'controller' => "admin",
                    'action' => 'new'
                ]);

                return;
            }
        }

        $banner = new Banners();
        $banner->name = $this->request->getPost("name");
        $banner->url = $url;
        $banner->status = $this->request->getPost("status");
        $banner->position = $this->request->getPost("position");


        if (!$banner->save()) {
            foreach ($banner->getMessages() as $message) {
                $this->flash->error($message);
            }

            $this->dispatcher->forward([
                'controller' => "admin",
                'action' => 'new'
            ]);

            return;
        }

        $this->flash->success("banner was created successfully");

        $this->dispatcher->forward([
            'controller' => "admin",
            'action' => 'index'
        ]);
    }

    /**
     * Saves a banner edited
     *
     */
    public function saveAction()
    {
        if (!$this->request->isPost()) {
            $this->dispatcher->forward([
                'controller' => "admin",
                'action' => 'index'
            ]);

            return;
        }

        $id = $this->request->getPost("id");
        $banner = Banners::findFirstByid($id);

        if (!$banner) {
            $this->flash->error("banner does not exist " . $id);

            $this->dispatcher->forward([
                'controller' => "admin",
                'action' => 'index'
            ]);

            return;
        }

        $banner->name = $this->request->getPost("name");
        $banner->url = $this->request->getPost("url");
        $banner->status = $this->request->getPost("status");
        $banner->position = $this->request->getPost("position");


        if (!$banner->save()) {

            foreach ($banner->getMessages() as $message) {
                $this->flash->error($message);
            }

            $this->dispatcher->forward([
                'controller' => "admin",
                'action' => 'edit',
                'params' => [$banner->id]
            ]);

            return;
        }

        $this->flash->success("banner was updated successfully");

        $this->dispatcher->forward([
            'controller' => "admin",
            'action' => 'index'
        ]);
    }


    public function deleteAction($id)
    {
        $banner = Banners::findFirstByid($id);
        if (!$banner) {
            $this->flash->error("banner was not found");

            $this->dispatcher->forward([
                'controller' => "admin",
                'action' => 'index'
            ]);

            return;
        }

        if (!$this->deleteImage($banner->url)) {

            foreach ($banner->getMessages() as $message) {
                $this->flash->error($message);
            }

            $this->dispatcher->forward([
                'controller' => "admin",
                'action' => 'search'
            ]);

            return;
        }

        if (!$banner->delete()) {

            foreach ($banner->getMessages() as $message) {
                $this->flash->error($message);
            }

            $this->dispatcher->forward([
                'controller' => "admin",
                'action' => 'search'
            ]);

            return;
        }

        $this->flash->success("banner was deleted successfully");

        $this->dispatcher->forward([
            'controller' => "admin",
            'action' => "index"
        ]);
    }


    protected function uploadImage($files)
    {

        $extensions = ["image/jpeg", "image/jpg", "image/png"];

        if (count($files) !== 1) {
            return FALSE;
        }
        else {
            $file = $files[0];
        }

        if (!in_array($file->getRealType(), $extensions)) {
            return FALSE;
        }

        if (!$file->moveTo(IMG_PATH . $file->getName())) {
            return FALSE;
        }

        return $file->getName();
    }


    protected function deleteImage($url)
    {
        $url = IMG_PATH . $url;

        if (file_exists($url) && is_file($url)) {
            unlink($url);
        }
        else {
            return FALSE;
        }

        return TRUE;
    }

}
