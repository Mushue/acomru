<?php


interface UserAuthInterface
{
    public function authenticate(Identifiable $user);

    public function getUserEntity();

    public function hasUserEntity();

    public function logout();

    public function isAuthenticated();
}