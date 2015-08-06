<?php


class ModulePool extends Singleton implements Instantiatable
{
    private $default = null;

    private $pool = array();

    /**
     * @return ModulePool
     */
    public static function me()
    {
        return Singleton::getInstance(__CLASS__);
    }
}