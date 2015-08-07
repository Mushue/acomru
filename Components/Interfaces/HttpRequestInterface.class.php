<?php


interface HttpRequestInterface
{
    public static function create();

    public static function createFromGlobals();

    public function &getGet();

    public function getGetVar($name);

    public function hasGetVar($name);
}