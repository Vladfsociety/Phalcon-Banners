<?php

namespace App\Forms;

use Phalcon\Forms\Element\Text;
use Phalcon\Forms\Element\Hidden;
use Phalcon\Forms\Element\Password;
use Phalcon\Forms\Element\Select;
use Phalcon\Forms\Element\File;
use Phalcon\Forms\Element\Submit;
use Phalcon\Forms\Element\Check;
use Phalcon\Validation\Validator\PresenceOf;
use Phalcon\Validation\Validator\Email;
use Phalcon\Validation\Validator\Identical;
use Phalcon\Validation\Validator\StringLength;
use Phalcon\Validation\Validator\Confirmation;


class DeleteBannerForm extends BaseForm
{

    public function initialize($entity = null)
    {
        $id = new Hidden('id');
        $this->add($id);

        //Csrf
        $csrf = new Hidden('csrf');
        $csrf->addValidator(new Identical([
            'value' => $this->security->getSessionToken(),
            'message' => 'CSRF validation failed'
        ]));
        $csrf->clear();
        $this->add($csrf);

        // Confirm button
        $this->add(new Submit('Yes', [
            'name' => 'delete',
            'class' => 'btn btn-dark'
        ]));

        // Reject button
        $this->add(new Submit('No', [
            'name' => 'delete',
            'class' => 'btn btn-dark'
        ]));
    }
}
