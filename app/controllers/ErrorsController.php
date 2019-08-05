<?php
/**
 * ErrorsController
 *
 * Manage errors
 */
class ErrorsController extends ControllerBase
{
    public function initialize()
    {
        $this->tag->setTitle('Oops!');
        parent::initialize();
    }
    public function show404Action()
    {
        $this->response->setStatusCode(404, 'Not Found');
    }
    public function show401Action()
    {
    }
    public function show500Action()
    {
    }
}
