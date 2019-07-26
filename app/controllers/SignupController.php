<?php

class SignupController extends ControllerBase
{

    public function indexAction()
    {

    }


    public function registerAction()
    {
        if ($this->session->has("user_id")) {
            return $this->response->redirect(BASE_URL);
        }

        $user = new Users();

        $success = $user->save(
            $this->request->getPost(),
            [
                "name",
                "login",
                "password",
            ]
        );

        if ($success) {
            $this->view->data = "Successful!";
        } else {
            $this->view->data = "Unsuccessful: ";

            $messages = $user->getMessages();

            foreach ($messages as $message) {
                $this->view->data .= $message->getMessage() . "<br/>";
            }
        }

        //$this->view->disable();
    }

}

