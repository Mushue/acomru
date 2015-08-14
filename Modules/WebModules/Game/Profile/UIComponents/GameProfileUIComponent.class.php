<?php
/**
 * Created by PhpStorm.
 * User: pgorbachev
 * Date: 14.08.15
 * Time: 17:03
 */

namespace Modules\WebModules\Game\Profile\UIComponents;


class GameProfileUIComponent implements \ProfileUiComponentInterface
{

    protected $profile;

    public function  getProfile()
    {
        return $this->profile;
    }

    /**
     * @param mixed $profile
     */
    public function setProfile($profile)
    {
        $this->profile = $profile;
        return $this;
    }

    public function render()
    {
        $viewResolver = new \PhpViewResolver(PATH_MODULES .
            'WebModules' . DIRECTORY_SEPARATOR .
            'Game' . DIRECTORY_SEPARATOR .
            'Profile' . DIRECTORY_SEPARATOR .
            'Views' . DIRECTORY_SEPARATOR
            , EXT_TPL);
        $view = $viewResolver->resolveViewName('Components' . DIRECTORY_SEPARATOR . 'profile');
        $model = new \Model();
        return $view->toString($model);
    }


}