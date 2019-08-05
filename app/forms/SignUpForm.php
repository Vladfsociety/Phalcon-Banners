<?php

namespace App\Forms;

use Phalcon\Forms\Element\Text;
use Phalcon\Forms\Element\Hidden;
use Phalcon\Forms\Element\Password;
use Phalcon\Forms\Element\Submit;
use Phalcon\Forms\Element\Check;
use Phalcon\Validation\Validator\PresenceOf;
use Phalcon\Validation\Validator\Email;
use Phalcon\Validation\Validator\Identical;
use Phalcon\Validation\Validator\StringLength;
use Phalcon\Validation\Validator\Confirmation;


class SignUpForm extends BaseForm
{

    public function initialize()
    {
        // Name
        $name = new Text(
            'name',
            [
                "required"    => TRUE,
                "minlenght"   => "3",
                "class"       => "input-block-level",
                "placeholder" => "name"
            ]
        );
        //$name->setLabel('Name');
        $name->addValidators([
            new PresenceOf([
                'message' => 'The name is required'
            ]),
            new StringLength([
                'min' => 3,
                'messageMinimum' => 'name is too short. Minimum 3 characters'
            ]),
        ]);
        $this->add($name);

        // Login
        $login = new Text(
            'login',
            [
                "required"    => TRUE,
                "minlenght"   => "3",
                "class"       => "input-block-level",
                "placeholder" => "login"
            ]
        );
        //$login->setLabel('Login');
        $login->addValidators([
            new PresenceOf([
                'message' => 'The login is required'
            ]),
            new StringLength([
                'min' => 3,
                'messageMinimum' => 'login is too short. Minimum 3 characters'
            ]),
        ]);
        $this->add($login);

        // Password
        $password = new Password(
            'password',
            [
                "required"    => TRUE,
                "minlenght"   => "3",
                "class"       => "input-block-level",
                "placeholder" => "password"
            ]
        );
        //$password->setLabel('Password');
        $password->addValidators([
            new PresenceOf([
                'message' => 'The password is required'
            ]),
            new StringLength([
                'min' => 3,
                'messageMinimum' => 'Password is too short. Minimum 3 characters'
            ]),
            new Confirmation([
                'message' => 'Password doesn\'t match confirmation',
                'with' => 'confirmPassword'
            ])
        ]);
        $password->clear();
        $this->add($password);

        // Confirm password
        $confirmPassword = new Password(
            'confirmPassword',
            [
                "required"    => TRUE,
                "minlenght"   => "3",
                "class"       => "input-block-level",
                "placeholder" => "confirmPassword"
            ]
        );
        //$confirmPassword->setLabel('Confirm password');
        $confirmPassword->addValidators([
            new PresenceOf([
                'message' => 'The confirmation password is required'
            ]),
            new StringLength([
                'min' => 3,
                'messageMinimum' => 'Confirm password is too short. Minimum 3 characters'
            ])
        ]);
        $confirmPassword->clear();
        $this->add($confirmPassword);

        // Csrf
        $csrf = new Hidden('csrf');
        $csrf->addValidator(new Identical([
            'value' => $this->security->getSessionToken(),
            'message' => 'CSRF validation failed'
        ]));
        $csrf->clear();
        $this->add($csrf);
    }
}
