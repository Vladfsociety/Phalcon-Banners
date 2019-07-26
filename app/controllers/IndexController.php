<?php

class IndexController extends ControllerBase
{

    public function indexAction()
    {
        $this->view->banners = Banners::find([
            'conditions' => "status = 'Enabled'",
            'order' => 'position DESC'
        ]);
    }

}

