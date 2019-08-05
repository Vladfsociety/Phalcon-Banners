<?php

use Phalcon\Mvc\Model\Criteria;
use Phalcon\Paginator\Adapter\Model as Paginator;

use App\Forms\CreateBannerForm;
use App\Forms\EditBannerForm;
use App\Forms\DeleteBannerForm;
//use App\Forms\FilePlugin;


class AdminController extends ControllerBase
{

    public function indexAction($column='id', $direction='DESC')
    {
        $numberPage = $this->request->getQuery("page", "int");
        //$this->view->banners = Banners::find(['order' => 'position DESC']);
        $banners = Banners::find(['order' => $column . " " . $direction]);
        $this->view->count = count($banners);

        $paginator = new Paginator([
            'data' => $banners,
            'limit'=> 3,
            'page' => $numberPage
        ]);

        $this->view->page = $paginator->getPaginate();

        //$this->view->banners = Banners::find();
    }


    public function usersAction()
    {
        $this->view->users = Users::find();
    }


    public function editAction($id=null)
    {
        $form = new EditBannerForm();

        if ($this->request->isPost()) {
            if ($form->isValid($this->request->getPost())) {
                $id = $this->request->getPost("id");
                $banner = Banners::findFirstByid($id);

                if (!$banner) {
                    $this->flash->error("banner does not exist " . $id);
                    $this->response->redirect('admin/edit/' . $banner->id);
                    return;
                }

                if ($this->request->hasFiles(TRUE) == TRUE) {
                    $this->deleteImage($banner->image);
                    $files = $this->request->getUploadedFiles();
                    if (count($files) !== 1) {
                        $this->response->redirect('admin/edit/' . $banner->id);
                        return;
                    }
                    $file = $files[0];
                    if (!$this->checkExtension($file)) {
                        $this->response->redirect('admin/edit/' . $banner->id);
                        return;
                    }
                    $image = $this->getImage($file, $this->request->getPost("name"));
                    if (!$image) {
                        $this->response->redirect('admin/edit/' . $banner->id);
                        return;
                    }
                    if (!$this->uploadImage($file, $image)) {
                        $this->response->redirect('admin/edit/' . $banner->id);
                        return;
                    }

                } else {
                    $image = $this->renameImage($banner->image, $this->request->getPost("name"));
                    if (!$image) {
                        $this->response->redirect('admin/edit/' . $banner->id);
                        return;
                    }
                }

                $banner->name = $this->request->getPost("name");
                $banner->url = $this->request->getPost("url");
                $banner->image = $image;
                $banner->status = $this->request->getPost("status");
                //$banner->position = $this->request->getPost("position");

                if (!$banner->save()) {
                    $this->deleteImage($image);
                    foreach ($banner->getMessages() as $message) {
                        $this->flash->error($message);
                    }
                    $this->response->redirect('admin/edit/' . $banner->id);
                    return;
                }

                $this->flash->success("banner was updated successfully");
                $this->response->redirect('admin/index');
                return;
            } else {
                $id = $this->request->getPost("id");
                $banner = Banners::findFirstById($id);
                if (!$banner) {
                    $this->flash->error("Banner was not found");
                    $this->response->redirect('admin/search');
                    return;
                }
                $this->view->image = $banner->showImage();
                $this->view->form = $form;
            }
        }
        else {
            $banner = Banners::findFirstById($id);
            if (!$banner) {
                $this->flash->error("Banner was not found");
                $this->response->redirect('admin/search');
                return;
            }
            $this->view->image = IMG_URL . htmlspecialchars($banner->image);
            $this->view->form = new EditBannerForm($banner);
        }
    }


    public function createAction()
    {
        $form = new CreateBannerForm();

        if ($this->request->isPost()) {
            if ($form->isValid($this->request->getPost())) {

                $this->view->form = $form;

                if ($this->request->hasFiles(TRUE) == TRUE) {
                    $files = $this->request->getUploadedFiles();
                    if (count($files) !== 1) {
                        $this->response->redirect('admin/create');
                        return;
                    }
                    $file = $files[0];
                    if (!$this->checkExtension($file)) {
                        $this->response->redirect('admin/create');
                        return;
                    }
                    $image = $this->getImage($file, $this->request->getPost("name"));
                    if (!$image) {
                        $this->response->redirect('admin/create');
                        return;
                    }
                } else {
                    $this->response->redirect('admin/create');
                    return;
                }

                $max_position = Banners::maximum(
                    [
                        "column" => "position"
                    ]
                );

                $banner = new Banners();
                $banner->name = $this->request->getPost("name");
                $banner->image = $image;
                $banner->url = $this->request->getPost("url");
                $banner->status = $this->request->getPost("status");
                $banner->position = $max_position + 1;

                if (!$banner->save()) {
                    $this->response->redirect('admin/create');
                    return;
                }

                if (!$this->uploadImage($file, $image)) {
                    $this->response->redirect('admin/create');
                    return;
                }

                $this->response->redirect('admin/index');
                return;
            }
        }

        $this->view->form = $form;
    }


    public function deleteAction($id=null)
    {
        $form = new DeleteBannerForm();

        if ($this->request->isPost()) {
            if ($form->isValid($this->request->getPost())) {
                if ($this->request->getPost('delete') !== 'Yes') {
                    $this->response->redirect('admin/index');
                    return;
                }
                $banner = Banners::findFirstByid($this->request->getPost("id"));
                if (!$banner) {
                    $this->flash->error("banner was not found");

                    $this->response->redirect('admin/index/');
                    return;
                }

                if (!$banner->delete()) {
                    //echo var_dump($banner), "gdsgsdgsg", exit;
                    foreach ($banner->getMessages() as $message) {
                        $this->flash->error($message);
                    }

                    $this->response->redirect('admin/index/');
                    return;
                }

                if (!$this->deleteImage($banner->image)) {
                    $this->flash->error("Delete banner error");

                    $this->response->redirect('admin/index/');
                    return;
                }

                $this->response->redirect('admin/index/');
            } else {
                //echo "gdsgsdgsg", exit;
                $this->view->form = $form;
            }
        }
        else {
            $banner = Banners::findFirstById($id);
            if (!$banner) {
                $this->flash->error("Banner was not found");
                $this->response->redirect('admin/index');
                return;
            }
            $this->view->form = new DeleteBannerForm($banner);
        }

    }


    protected function checkExtension($file)
    {
        $extensions = ["image/jpeg", "image/jpg", "image/png"];
        if (!in_array($file->getRealType(), $extensions)) {
            return FALSE;
        }
        return TRUE;
    }


    protected function getImage($file, $file_name)
    {
        $file_array = explode(".", $file->getName());
        $file_extension = end($file_array);
        return $file_name . "." . $file_extension;
    }

    protected function uploadImage($file, $image)
    {
        if (!$file->moveTo(IMG_PATH . $image)) {
            return FALSE;
        }
        return TRUE;
    }


    protected function deleteImage($image)
    {
        $image_abs = IMG_PATH . $image;

        if (file_exists($image_abs) && is_file($image_abs)) {
            unlink($image_abs);
        } else {
            return FALSE;
        }

        return TRUE;
    }


    protected function renameImage($image, $image_name)
    {
        $image_array = explode(".", $image);
        $image_extension = end($image_array);
        $new_image = $image_name . "." . $image_extension;
        $image_abs = IMG_PATH . $image;
        $new_image_abs = IMG_PATH . $new_image;

        if (file_exists($image_abs) && is_file($image_abs)) {
            if (!rename($image_abs, $new_image_abs)) {
                return FALSE;
            }
        } else {
            return FALSE;
        }

        return $new_image;
    }
}
