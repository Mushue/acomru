<?php $part->view('header'); ?>
    <div class="container">
        <div>
            <h1>Профиль</h1>

            <p>Открой новый мир для себя.</p>
        </div>
        <div><?php
            /** @var ProfileUiComponentInterface $profileUIComponent */
            $profileUIComponent = Core::get(ProfileUiComponentInterface::class);
            echo $profileUIComponent->render();
            ?>
        </div>
    </div>
<?php $part->view('footer'); ?>