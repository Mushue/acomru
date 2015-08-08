<?php

/**
 * Created by PhpStorm.
 * User: mushu_000
 * Date: 08.08.2015
 * Time: 14:05
 */
class WebKernelAjaxHandler implements InterceptingChainHandler
{

    private static $ajaxRequestVar = 'HTTP_X_REQUESTED_WITH';
    private static $ajaxRequestValueList = array('XMLHttpRequest');
    private static $pjaxRequestVar = 'HTTP_X_PJAX';

    /**
     * @return WebKernelAjaxHandler
     */
    public static function create()
    {
        return new self();
    }

    /**
     * @return WebKernelAjaxHandler
     */
    public function run(InterceptingChain $chain)
    {
        /* @var $chain WebKernel */
        $isPjaxRequest = $this->isPjaxRequest($chain->getRequest());
        $isAjaxRequest = !$isPjaxRequest && $this->isAjaxRequest($chain->getRequest());

        $chain->setVar('isPjax', $isPjaxRequest);
        $chain->setVar('isAjax', $isAjaxRequest);
        $chain->getServiceLocator()->
        set('isPjax', $isPjaxRequest)->
        set('isAjax', $isAjaxRequest);

        $chain->next();

        return $this;
    }

    /**
     * @return boolean
     */
    private function isPjaxRequest(HttpRequest $request)
    {
        $form = Form::create()->
        add(
            Primitive::boolean(self::$pjaxRequestVar)
        )->
        add(
            Primitive::boolean('_isPjax')
        )->
        import($request->getServer())->
        importOneMore('_isPjax', $request->getGet());

        if ($form->getErrors()) {
            return false;
        }
        return $form->getValue(self::$pjaxRequestVar) || $form->getValue('_isPjax');
    }

    /**
     * @return boolean
     */
    private function isAjaxRequest(HttpRequest $request)
    {
        $form = Form::create()->
        add(
            Primitive::plainChoice(self::$ajaxRequestVar)->
            setList(self::$ajaxRequestValueList)
        )->
        add(
            Primitive::boolean('_isAjax')
        )->
        import($request->getServer())->
        importOneMore('_isAjax', $request->getGet());

        if ($form->getErrors()) {
            return false;
        }
        if ($form->getValue(self::$ajaxRequestVar)) {
            return true;
        }
        if ($form->getValue('_isAjax')) {
            return true;
        }
        return false;
    }
}