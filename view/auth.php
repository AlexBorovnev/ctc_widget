<?php
use library\Common;
if($_SERVER['REQUEST_METHOD'] == 'POST' && !empty($_POST['login']) && !empty($_POST['password'])):
    if($id = Common::getInstance()->getUserId($_POST['login'], $_POST['password'])):
        $_SESSION['user_id'] = $id;
    endif;
endif;
?>
<?php if (empty($_SESSION['user_id'])):?>
<form name="auth_form" action="<?=HOST?>admin" method="post">
    <label for="login">Login:</label>
    <input type="text" name="login" id="login" placeholder="Your login"/>
    <label for="password">Password:</label>
    <input type="password" name="password" id="password" placeholder="Your password"/>
    <input type="submit" name="btn_send" value="Login" />
</form>
<?php exit; endif;?>