<!DOCTYPE html>
<html>
	<head>

		<meta http-equiv="Expires" content="Fri, Jan 01 1900 00:00:00 GMT">
		<meta http-equiv="Pragma" content="no-cache">
		<meta http-equiv="Cache-Control" content="no-cache">
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		<meta http-equiv="Lang" content="en">

		<meta name="author" content="">
		<meta name="description" content="">
		<meta name="keywords" content="">

		<title><?=$this->pageTitle?></title>


		
		<link rel="stylesheet" type="text/css" href="<?=HOST?>css/admin/redmond/jquery-ui-1.10.3.custom.css">
		<link rel="stylesheet" type="text/css" href="<?=HOST?>css/admin/chosen.min.css">
		<link rel="stylesheet" type="text/css" href="<?=HOST?>css/admin/toastr.min.css">
		<link rel="stylesheet" type="text/css" href="<?=HOST?>css/simplePagination.css">
		<link rel="stylesheet" type="text/css" href="<?=HOST?>css/admin/style.css?<?=REV?>">
		<link rel="stylesheet" type="text/css" href="<?=HOST?>css/admin/admin.style.css?<?=REV?>">


		<script type="text/javascript" src="<?=HOST?>js/admin/jquery-1.9.1.js"></script>
		<script type="text/javascript" src="<?=HOST?>js/admin/jquery-ui-1.10.3.custom.min.js"></script>
		<script type="text/javascript" src="<?=HOST?>js/admin/jquery.carouFredSel-6.2.1-packed.js"></script>
		<script type="text/javascript" src="<?=HOST?>js/admin/chosen.jquery.min.js"></script>
		<script type="text/javascript" src="<?=HOST?>js/admin/toastr.min.js"></script>
		<script type="text/javascript" src="<?=HOST?>js/jquery.simplePagination.js"></script>
		<script type="text/javascript" src="<?=HOST?>js/all.js"></script>
	</head>
	<body>
    <div class="overlay" style="display:none;">
        <div class="loading" style="display: <? if (isset($this->viewLoading)): echo 'block'; else: echo 'none'; endif;?>;">
            <img src="<?=HOST?>images/loading.gif">
        </div>
    </div>

    <div class="wrap">
        <div class="mainHeader">
            <div class="logo"><a href="<?=makeLink("/")?>"><img src="<?=makeLink("/images/sts_logo.png")?>" width="100px" /></a></div>
            <div class="title">Система управления виджетами</div>
            <div class="userInfo">
                <div>Вы вошли как <?=$_SESSION['user_id']['user_name']?></div>
                <div class="fr"><a href="<?=makeLink('/logout')?>">Выход</a></div></div>
            
            <div class="shops clearfix"></div>
        </div>
        <div class="bc">
            <?=breadcrumbs($this)?>
        </div>