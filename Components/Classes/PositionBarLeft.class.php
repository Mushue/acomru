<?php

/**
 * Created by PhpStorm.
 * User: mushu_000
 * Date: 09.08.2015
 * Time: 11:57
 */
class PositionBarLeft implements PositionBarInterface
{
    protected $position = '';

    public function getPoistion()
    {
        return $this->position;
    }

}