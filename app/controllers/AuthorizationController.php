<?php

class AuthorizationController extends ControllerBase
{

    public function indexAction()
    {

    }


    public function signinAction()
    {
        $sessions = $this->session;
        //$sessions = $this->getDI()->getShared("session");

        if ($sessions->has("user_id")) {
            return $this->response->redirect(BASE_URL);
        }

        if ($this->request->isPost()) {

            $password = $this->request->getPost("password");
            $login = $this->request->getPost("login");
            if ($login === "") {
                $this->flashSession->error("return enter your login");
                return $this->view->pick("authorization/index");
            }

            if ($password === "") {
                $this->flashSession->error("return enter your password");
                return $this->view->pick("authorization/index");
            }

            $user = Users::findFirst([
                "conditions" => "login = ?0 AND password = ?1",
                "bind" => [
                    0 => $login,
                    1 => $password,
                ]
            ]);

            if ($user === FALSE) {
                $this->flashSession->error("wrong login / password");
                return $this->response->redirect(BASE_URL);
            } else {
                $sessions->set("loggedin", TRUE);
                $sessions->set("user_id", $user->id);
                $sessions->set("user_name", $user->name);
            }
        }
    }


    public function signoutAction()
    {
        $this->session->remove('loggedin');
        $this->session->remove('user_id');
        $this->session->remove('user_name');
        $this->session->destroy();
        return $this->response->redirect(BASE_URL);
    }
}

