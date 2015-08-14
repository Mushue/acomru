<?php foreach ($left as $positionElement): ?>
    <?php /**  @var NavigationBarElement $positionElement */ ?>
    <ul class="nav navbar-nav navbar-left">
        <li class="<?= $positionElement->getRoute() == $currentRouteName ? 'active' : ''; ?>"><a href="<?=
            $positionElement->getUrl() ?>"><?=
                $positionElement->getName();
                ?></a></li>
    </ul>
<?php endforeach; ?>
<?php foreach ($right as $positionElement): ?>
    <?php /**  @var NavigationBarElement $positionElement */ ?>
    <ul class="nav navbar-nav navbar-right">
        <li class="<?= $positionElement->getRoute() == $currentRouteName ? 'active' : ''; ?>"><a href="<?=
            $positionElement->getUrl() ?>"><?= $positionElement->getName(); ?></a></li>
    </ul>
<?php endforeach; ?>