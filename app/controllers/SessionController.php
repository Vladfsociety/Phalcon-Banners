<?php

use Phalcon\Acl\Adapter\Memory as AclList;
use Phalcon\Acl;
use Phalcon\Acl\Role;
use Phalcon\Acl\Resource;

use App\Forms\SignInForm;
use App\Forms\SignUpForm;


class SessionController extends ControllerBase
{

    public function signinAction()
    {
        $form = new SignInForm();

        $sessions = $this->session;

        if ($sessions->has("loggedin") && $sessions->get("loggedin")) {
            $this->response->redirect(BASE_URL);
            return;
        }

        if ($this->request->isPost()) {
            if ($form->isValid($this->request->getPost())) {

                $this->view->form = $form;

                $login = $this->request->getPost("login");
                $password = $this->request->getPost("password");

                $user = Users::findFirst([
                    "conditions" => "login = ?0",
                    "bind" => [
                        0 => $login,
                    ]
                ]);
                if ($user === FALSE) {
                    $this->view->message = "Wrong login";
                    return;
                } else {
                    if ($this->security->checkHash($password, $user->password)) {
                        $sessions->set("loggedin", TRUE);
                        $sessions->set("user_id", $user->id);
                        $sessions->set("user_name", $user->name);
                        $sessions->set("user_status", $user->status);
                    } else {
                        $this->view->message = "Wrong password";
                        return;
                    }

                }
                $this->response->redirect(BASE_URL);
                return;
            }
        }

        $this->view->form = $form;
    }


    public function signupAction()
    {
        $form = new SignUpForm();

        if ($this->session->has("loggedin") && $this->session->get("loggedin")) {
            $this->response->redirect(BASE_URL);
            return;
        }

        if ($this->request->isPost()) {
            if ($form->isValid($this->request->getPost())) {
                $user = new Users([
                    'name' => $this->request->getPost('name'),
                    'login' => $this->request->getPost('login'),
                    'password' => $this->security->hash($this->request->getPost('password')),
                    'status' => 'User',
                ]);
                if ($user->save()) {
                    $this->response->redirect(BASE_URL);
                    return;
                }
                $this->flash->error($user->getMessages());
            }
        }
        $this->view->form = $form;
    }


    public function signoutAction()
    {
        $this->session->destroy();
        return $this->response->redirect(BASE_URL);
    }

}

