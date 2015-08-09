<?php

/**
 * Created by PhpStorm.
 * User: mushu_000
 * Date: 09.08.2015
 * Time: 11:57
 */
class PositionBarRight implements PositionBarInterface
{
    protected $position = 'navbar-right';

    public function getPoistion()
    {
        return $this->position;
    }
}