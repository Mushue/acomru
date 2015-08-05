<?php

/**
 * Created by PhpStorm.
 * User: mushu_000
 * Date: 05.08.2015
 * Time: 3:25
 */
class IndexController extends BaseController
{
    protected $methodMap = array(
        'index' => 'indexAction',
        'authForbidden' => 'authForbiddenAction',
    );

    protected $defaultAction = 'index';

    /**
     * ����������, ����� �������� ������ ��������� ����������, �������� ��� � ���������� ���������
     * @param $request HttpRequest
     * @return ModelAndView
     **/
    public function handleRequest(HttpRequest $request)
    {
        return $this->resolveAction($request);
    }

    public function indexAction(HttpRequest $request)
    {
        $this->meta->setTitle('��������! ������ � �������� ���������.');
        return $this->getMav('error');
    }

    public function authForbiddenAction(HttpRequest $request)
    {
        return $this->getMav('auth-forbidden');
    }
}