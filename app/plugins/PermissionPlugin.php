<?php

namespace Permissions;

use Phalcon\Acl;
use Phalcon\Acl\Role;
use Phalcon\Acl\Resource;
use Phalcon\Events\Event;
use Phalcon\Mvc\User\Plugin;
use Phalcon\Mvc\Dispatcher;
use Phalcon\Acl\Adapter\Memory as AclList;
/**
 * SecurityPlugin
 *
 * This is the security plugin which controls that users only have access to the modules they're assigned to
 */
class PermissionPlugin extends Plugin
{

    public function beforeExecuteRoute(Event $event, Dispatcher $dispatcher)
    {
        if ($this->session->has('loggedin') && $this->session->get('loggedin')) {
            $role = $this->session->get('user_status');
        } else {
            $role = 'Guest';
        }
        $controller = $dispatcher->getControllerName();
        $action = $dispatcher->getActionName();
        $acl = $this->getAcl();

        if (!$acl->isResource($controller)) {
            /*$dispatcher->forward([
                'controller' => 'errors',
                'action'     => 'show404'
                'controller' => 'index',
                'action'     => 'index'
            ]);*/
            $this->response->redirect('session/signin');
            return false;
        }
        $allowed = $acl->isAllowed($role, $controller, $action);
        if (!$allowed) {
            /*$dispatcher->forward([
                'controller' => 'errors',
                'action'     => 'show401'
                'controller' => 'session',
                'action'     => 'signin'
            ]);*/
            $this->response->redirect('session/signin');
            return false;
        }
    }


    private function getAcl()
    {
        $acl = new AclList();
        $acl->setDefaultAction(Acl::DENY);
        // Register roles
        $roles = [
            'Admin' => new Role(
                'Admin',
                'Member high-level privileges, granted after sign in.'
            ),
            'User'  => new Role(
                'User',
                'Member privileges, granted after sign in.'
            ),
            'Guest' => new Role(
                'Guest',
                'Anyone browsing the site who is not signed in is considered to be a "Guest".'
            )
        ];

        $this->addRoleAndResource(
            $acl,
            $roles['Guest'],
            [
                'index'      => ['index'],
                'session'    => ['signup', 'signin', 'signout'],
            ]
        );
         $this->addRoleAndResource(
            $acl,
            $roles['User'],
            [
                'admin'      => ['index', 'search'],
            ],
            $roles['Guest']
        );
          $this->addRoleAndResource(
            $acl,
            $roles['Admin'],
            [
                'admin'      => ['edit', 'save', 'create', 'delete', 'users'],
            ],
            $roles['User']
        );
            //The acl is stored in session, APC would be useful here too

        return $acl;
    }


    private function addRoleAndResource($acl, $role_name, $resources, $base_role_name=null)
    {
        $acl->addRole($role_name);
        if ($base_role_name !== null) {
            $acl->addInherit($role_name, $base_role_name);
        }
        foreach ($resources as $resource => $actions) {
            $acl->addResource(new Resource($resource), $actions);
        }
        foreach ($resources as $resource => $actions) {
            foreach ($actions as $action){
                $acl->allow($role_name, $resource, $action);
            }
        }
    }
}
