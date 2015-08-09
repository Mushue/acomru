<?php


class NavigationBar
{
    protected $navigationBar = [];

    public function set(NavigationBarElement $element, PositionBarInterface $position)
    {
        $this->navigationBar[$position->getPoistion()][$element->getName()] = $element;
        return $this;
    }

    /**
     * @return ModelAndView
     */
    public function render()
    {

        $right = new PositionBarRight();
        $left = new PositionBarLeft();

        $viewResolver = new PhpViewResolver(PATH_VIEWS, EXT_TPL);
        $view = $viewResolver->resolveViewName('NavigationBar' . DIRECTORY_SEPARATOR . 'navigator');
        $model = new Model();

        $model->set('left', $left)
            ->set('right', $right)
            ->set('bar', $this->navigationBar);

        return $view->render($model);
    }

    /**
     * @param $element
     * @param PositionBarInterface $position
     * @return NavigationBar
     */
    protected function add($element, PositionBarInterface $position)
    {
        $this->navigationBar[$position->getPoistion()][] = $element;
        return $this;
    }
}