<?php
use library\Common;

if ($_SERVER['REQUEST_METHOD'] == 'POST' && !empty($_POST['login']) && !empty($_POST['password'])):
    if ($id = Common::getInstance()->getUserId($_POST['login'], $_POST['password'])):
        $_SESSION['user_id'] = $id;
    endif;
endif;
?>
<?php if (empty($_SESSION['user_id'])): ?>
    <head>
    <title>Вход</title>
    <script type="text/javascript" src="<?= HOST ?>js/admin/jquery-1.9.1.js"></script>
    <link rel="stylesheet" type="text/css" href="<?= HOST ?>css/bootstrap.min.css?<?= REV ?>">
        <style type="text/css">
            body {
                padding-top: 40px;
                padding-bottom: 40px;
                background-color: #f5f5f5;
            }

            .form-signin {
                max-width: 300px;
                padding: 19px 29px 29px;
                margin: 0 auto 20px;
                background-color: #fff;
                border: 1px solid #e5e5e5;
                -webkit-border-radius: 5px;
                -moz-border-radius: 5px;
                border-radius: 5px;
                -webkit-box-shadow: 0 1px 2px rgba(0,0,0,.05);
                -moz-box-shadow: 0 1px 2px rgba(0,0,0,.05);
                box-shadow: 0 1px 2px rgba(0,0,0,.05);
                background: url(/images/sts_logo.png) no-repeat center;
                -moz-background-size: 340px auto;
                -webkit-background-size: 340px auto;
                -o-background-size: 340px auto;
                background-size: 340px auto;

            }
            .form-signin .form-signin-heading,
            .form-signin .checkbox {
                margin-bottom: 10px;
            }
            .form-signin input[type="text"],
            .form-signin input[type="password"] {
                font-size: 16px;
                height: auto;
                margin-bottom: 15px;
                padding: 7px 9px;
            }

        </style>
    </head>
    <div class="container">
        <form class="form-signin" name="auth_form" action="<?= HOST ?>admin" method="post">
            <h3 class="form-signin-heading">Вход в систему</h3>
            
            <input type="text" class="input-block-level" name="login" id="login" placeholder="Логин"/>
            
            <input class="input-block-level" type="password" name="password" id="password" placeholder="Пароль"/>
            <input type="submit" class="btn btn-large btn-primary" name="btn_send" value="Вход"/>
        </form>
    </div>
    <?php exit; endif; ?>