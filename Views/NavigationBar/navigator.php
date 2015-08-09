<?php foreach ($bar as $positionName => $positionElement): ?>
    <ul class="nav navbar-nav <?= $positionName; ?>">
        <?php foreach ($positionElement as $elemName => $elemValue): ?>
            <?php /** @var \NavigationBarElement $elemValue */ ?>
            <li><a href="<?= $elemValue->getUrl() ?>"><?= $elemValue->getName(); ?></a></li>
        <?php endforeach; ?>
    </ul>
<?php endforeach; ?>

