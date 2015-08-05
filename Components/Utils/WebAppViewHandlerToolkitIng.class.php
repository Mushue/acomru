<?php

class WebAppViewHandlerToolkitIng extends WebAppViewHandlerToolkit
{
    /**
     * @return WebAppViewHandlerToolkitIng
     */
    public static function create()
    {
        return new self();
    }

    /**
     * @return string
     */
    protected function getMenuContructor() {
        return 'ToolkitMenuConstructorIng';
    }
    /**
     * @return string
     */
    protected function getNameConverterClass() {
        return 'ObjectNameConverterIng';
    }
}