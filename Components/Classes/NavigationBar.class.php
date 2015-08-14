<?php


class NavigationBar
{
    /**
     * @var SplStack
     */
    protected $leftNavigationBar;
    /**
     * @var SplStack
     */
    protected $rightNavigationBar;

    /**
     * NavigationBar constructor.
     */
    public function __construct()
    {
        $this->leftNavigationBar = new SplStack();
        $this->rightNavigationBar = new SplStack();
    }


    public function push(NavigationBarElement $element, PositionBarInterface $position)
    {
        if ($position instanceof \PositionBarLeft) {
            $this->leftNavigationBar->push($element);
        } else if ($position instanceof \PositionBarRight) {
            $this->rightNavigationBar->push($element);
        }
        //$this->navigationBar[$position->getPoistion()][$element->getName()] = $element;
        return $this;
    }

    /**
     * @return ModelAndView
     */
    public function render()
    {

        $viewResolver = new PhpViewResolver(PATH_VIEWS, EXT_TPL);
        $view = $viewResolver->resolveViewName('NavigationBar' . DIRECTORY_SEPARATOR . 'navigator');
        $model = new Model();

        RouterRewrite::me()->route(RouterRewrite::me()->getRequest());
        try {
            /** @var RouterRule $currentRoute */
            $currentRouteName = RouterRewrite::me()->getCurrentRouteName();
        } catch (RouterException $e) {
            $currentRouteName = '';
        }

        $model->set('left', $this->leftNavigationBar)
            ->set('right', $this->rightNavigationBar)
            ->set('currentRouteName', $currentRouteName);
        return $view->render($model);
    }

    /**
     * @param $element
     * @param PositionBarInterface $position
     * @return NavigationBar
     */
    public function unshift($element, PositionBarInterface $position)
    {
        if ($position instanceof \PositionBarLeft) {
            $this->leftNavigationBar->unshift($element);
        } else if ($position instanceof \PositionBarRight) {
            $this->rightNavigationBar->unshift($element);
        }
        //$this->navigationBar[$position->getPoistion()][] = $element;
        return $this;
    }
}