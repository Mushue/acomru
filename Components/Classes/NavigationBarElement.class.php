<?php


class NavigationBarElement
{
    protected $name;

    protected $url;

    /**
     * NavigationBarElement constructor.
     * @param $name
     * @param $url
     */
    public function __construct($name, $url)
    {
        $this->name = $name;
        $this->url = $url;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     * @return NavigationBarElement
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @param mixed $url
     * @return NavigationBarElement
     */
    public function setUrl($url)
    {
        $this->url = $url;
        return $this;
    }


}