<?php
/**
 * Created by PhpStorm.
 * User: mushu_000
 * Date: 08.08.2015
 * Time: 20:24
 */

namespace Modules\WebModules\WebAuth\Classes;

class WebUser implements \UserAuthInterface
{
    /**
     * @param \Identifiable $user
     * @return WebUser
     */
    public function authenticate(\Identifiable $user)
    {
        \Session::assign('authenticated', true);
        \Session::assign('id', $user->getId());

        $this->setUserEntity($user);

        return $this;
    }

    /**
     * @param \Identifiable $user
     * @return WebUser
     */
    protected function setUserEntity(\Identifiable $user)
    {
        \Session::assign('user', $user);
        return $this;
    }

    /**
     * @return bool
     */
    public function isAuthenticated()
    {
        return (bool)\Session::get('authenticated');
    }

    /**
     * @return WebUser
     */
    public function logout()
    {
        \Session::dropAll();
        \Session::destroy();

        return $this;
    }

    /**
     * @return bool
     */
    public function hasUserEntity()
    {
        return !is_null(\Session::get('user'));
    }

    /**
     * @return \Identifiable
     */
    public function getUserEntity()
    {
        $user = \Session::get('user');

        \Assert::isNotEmpty($user);

        return $user;
    }

}