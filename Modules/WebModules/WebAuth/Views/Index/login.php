<?php $part->view('header'); ?>
    <style>
        .form-signin {
            max-width: 330px;
            padding: 15px;
            margin: 0 auto;
        }

        .form-signin .form-signin-heading,
        .form-signin .checkbox {
            margin-bottom: 10px;
        }

        .form-signin .checkbox {
            font-weight: normal;
        }

        .form-signin .form-control {
            position: relative;
            height: auto;
            -webkit-box-sizing: border-box;
            -moz-box-sizing: border-box;
            box-sizing: border-box;
            padding: 10px;
            font-size: 16px;
        }

        .form-signin .form-control:focus {
            z-index: 2;
        }

        .form-signin input[type="email"] {
            margin-bottom: -1px;
            border-bottom-right-radius: 0;
            border-bottom-left-radius: 0;
        }

        .form-signin input[type="password"] {
            margin-bottom: 10px;
            border-top-left-radius: 0;
            border-top-right-radius: 0;
        }
    </style>
    <div class="container">
        <form class="form-signin" method="POST">
            <h2 class="form-signin-heading">Пожалуйста авторизуйтесь</h2>
            <label for="inputEmail" class="sr-only">Ваш логин</label>
            <input name="inputEmail" type="text" id="inputEmail" class="form-control" placeholder="Ваш логин" required
                   autofocus>
            <label for="inputPassword" class="sr-only">Пароль</label>
            <input name="inputPassword" type="password" id="inputPassword" class="form-control" placeholder="Пароль"
                   required>
            <button class="btn btn-lg btn-primary btn-block" type="submit">Войти</button>
        </form>

    </div>
<?php $part->view('footer'); ?>