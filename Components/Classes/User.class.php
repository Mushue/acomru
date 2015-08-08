<?php

/**
 * Created by PhpStorm.
 * User: mushu_000
 * Date: 08.08.2015
 * Time: 22:40
 */
class User implements Identifiable
{
    protected $id = null;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param $id
     * @return User
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

}