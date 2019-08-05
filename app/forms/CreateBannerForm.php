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


class CreateBannerForm extends BaseForm
{

    public function initialize()
    {
        // Name
        $name = new Text(
            'name',
            [
                "required"  => TRUE,
                "minlenght" => "3"
            ]
        );
        $name->setLabel('Name');
        $name->addValidators([
            new PresenceOf([
                'message' => 'The name is required'
            ]),
            new StringLength([
                'min' => 3,
                'messageMinimum' => 'Name is too short. Minimum 3 characters'
            ]),
        ]);
        $this->add($name);

        // Url
        $url = new Text(
            'url',
            [
                "required"  => TRUE,
                "minlenght" => "5"
            ]
        );
        $url->setLabel('Url');
        $url->addValidators([
            new PresenceOf([
                'message' => 'The url is required'
            ]),
            new StringLength([
                'min' => 5,
                'messageMinimum' => 'Url is too short. Minimum 5 characters'
            ]),
        ]);
        $this->add($url);

        // Status
        $status = new Select(
            'status',
            array(
                'Enabled'  => 'Enabled',
                'Disabled' => 'Disabled'
            ),
            array(
                "required" => TRUE
            )
        );
        $status->setLabel('Status');
        $status->addValidators(array(
            new PresenceOf(array(
                'message' => 'The status is required'
            ))
        ));
        $this->add($status);

        //Csrf
        $csrf = new Hidden('csrf');
        $csrf->addValidator(new Identical([
            'value' => $this->security->getSessionToken(),
            'message' => 'CSRF validation failed'
        ]));
        $csrf->clear();
        $this->add($csrf);
    }
}
