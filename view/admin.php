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

		<title>Untitled</title>



		<link rel="stylesheet" type="text/css" href="<?=HOST?>css/admin/redmond/jquery-ui-1.10.3.custom.css">
		<link rel="stylesheet" type="text/css" href="<?=HOST?>css/admin/chosen.min.css">
		<link rel="stylesheet" type="text/css" href="<?=HOST?>css/admin/toastr.min.css">
		<link rel="stylesheet" type="text/css" href="<?=HOST?>css/admin/style.css?<?=REV?>">


		<script type="text/javascript" src="<?=HOST?>js/admin/jquery-1.9.1.js"></script>
		<script type="text/javascript" src="<?=HOST?>js/admin/jquery-ui-1.10.3.custom.min.js"></script>
		<script type="text/javascript" src="<?=HOST?>js/admin/jquery.carouFredSel-6.2.1-packed.js"></script>
		<script type="text/javascript" src="<?=HOST?>js/admin/chosen.jquery.min.js"></script>
		<script type="text/javascript" src="<?=HOST?>js/admin/toastr.min.js"></script>



		<script type="text/javascript" src="<?=HOST?>js/admin/tree.js?<?=REV?>"></script>

		<script type="text/javascript" src="<?=HOST?>js/admin/shop.js?<?=REV?>"></script>
		<script type="text/javascript" src="<?=HOST?>js/admin/widget.js?<?=REV?>"></script>
		<script type="text/javascript" src="<?=HOST?>js/admin/offer.js?<?=REV?>"></script>
		<script type="text/javascript" src="<?=HOST?>js/admin/main2.js?<?=REV?>"></script>
		<script type="text/javascript" src="<?=HOST?>js/admin/main.js?<?=REV?>"></script>
		<script type="text/javascript" src="<?=HOST?>js/admin/system.js?<?=REV?>"></script>
	</head>
	<body>
		<div class="wrapper clearfix">
			<div id="tabs">
				<ul class="tabList">


				</ul>



			</div>
		</div>
		<div class="hidden">
			<div class="categoryTpl">
				<div class="grid13">
					<h4>Выбор категории</h4>
					<div class="treeHolder"></div>
				</div>
			</div>
			<div class="categoryOfferTpl">

				<div class="grid13">
					<h4>Выбор категории</h4>
					<div class="treeHolder"></div>
				</div>
				<div class="grid13">
					<h4>Выбор товара</h4>
					<ol class="offerHolder">

					</ol>
				</div>
				<div class="grid13">
					<h4>Предпросмотр товара</h4>
					<div class="preview">
						<div class="previewPic">
							<img src="../images/preview.png" />
						</div>
						<div class="offerInfo">
						</div>
						<a href="#" class="btn addProduct">Выбрать</a>
					</div>
				</div>
			</div>

			<div class="colorTpl clearfix">
				<h4>Выбор цвета</h4>
			</div>




			<div class="block widget widgetTpl">
				<div class="block-header"></div>
				<div class="block-content">

				</div>
			</div>
			
			<div class="freePositionTpl">
				<div class="header"><h4><span class="positionNum"></span> позиция</h4></div>
				<div class="body clearfix">
					<a href="#" class="btn choseProduct">Выбрать товар</a>
					<a href="#" class="btn createRule">Создать правило</a>
					<div class="categoryOfferHolder clearfix"></div>
					<div class="ruleHolder clearfix"></div>
					<div class="clearfix">
						<a href="#" class="btn savePosition">Сохранить позицию</a>
						<span class="saved hidden">Сохранено</span>
					</div>
				</div>
			</div>
			<div class="rule ruleTpl">
				<div class="block-content clearfix">
					<div class="categoryHolder clearfix"></div>
					
					<div class="colorHolder clearfix">
					</div>

				</div>

			</div>
			<div class="block choose-product chooseProduct chooseProductTpl clearfix hidden">
				<div class="block-header"><span>Выбор товара</span><div class="close">X</div></div>
				<div class="block-content clearfix">
					<div class="grid13">
						<h4>Выбор категории</h4>
						<div class="treeHolder"></div>
					</div>
					<div class="grid13">
						<h4>Выбор товара</h4>
						<ol class="offerHolder">

						</ol>
					</div>
					<div class="grid13">
						<h4>Предпросмотр товара</h4>
						<div class="preview">
							<div class="previewPic">
								<img src="../images/preview.png" />
							</div>
							<div class="offerInfo">
							</div>
							<a href="#" class="btn addProduct">Выбрать</a>
						</div>
					</div>
				</div>
				<div class="block-footer">

				</div>
			</div>
			<div class="treeTpl"></div>

			<div class="tabTpl">
				<div class="wrap">
					<div class="widgets">
						<a href="#" class="btn add-widget">+ виджет</a>
						<div>
							<ul class="widget-list">

							</ul>
						</div>
					</div>

				</div>
			</div>
			<div class="positionTpl">
				<div class="header"><h4><span class="positionNum"></span> позиция</h4></div>
				<div class="body">
					<a href="#" class="btn choseProduct">Выбрать товар</a>
					<div class="categoryOfferHolder"></div>
				</div>
			</div>
			
			<div class="block smallWidget smallWidgetTpl widget">
				<div class="block-header">Маленький виджет</div>
				<div class="block-content">
					<a href="#" class="btn createRule">Создать правило</a>
					<div class="ruleHolder"></div>
					<div class="positionHolder clearfix"  data-position-num="1"></div>
				</div>
			</div>
			<div class="block bigWidget bigWidgetTpl widget">
				<div class="block-header">Большой виджет</div>
				<div class="block-content">
					<a href="#" class="btn createRule">Создать правило</a>
					<div class="ruleHolder"></div>
					<div class="positionHolder clearfix positionHolder1" data-position-num="1"></div>
					<div class="positionHolder clearfix positionHolder2" data-position-num="2"></div>
				</div>
			</div>
			<div class="block widget freeWidget freeWidgetTpl">
				<div class="block-header">Свободный виджет</div>
				<div class="block-content">
					
					<div class="positions">
						
					</div>
					<a href="#" class="btn addPosition">Добавить позицию</a>
					
				</div>
			</div>
			<div class="new-widget newWidgetTpl clearfix">
				<h3>Создание нового виджета</h3>

				<div class="clearfix">

					<div class="input-block">
						<span class="prepend">Тип: </span>
						<select class="widgetTypeList" data-placeholder="Выберите значение">

						</select> 
					</div>
					<div class="input-block">
						<span class="prepend">Скин: </span>
						<select class="widgetSkinList" data-placeholder="Выберите значение">

						</select>
					</div>
					<p>&nbsp;</p>
				</div>
				<a href="#" class="btn prepareWidget">Создать виджет</a>
				<div class="widgetHolder">
				</div>
				<div class="positions clearfix"></div>






				<div class="tut"></div>

				<div class="block clearfix preparedWidget">
					<div class="block-header">Виджет</div>
					<div class="block-content clearfix">
						<div style="margin: 5px"><a class="btn generateWidgetPreview" href="# ">Предпросмотр виджета</a></div>		
						<div style="margin: 5px;"><a href="#" class="btn saveWidget">Сохранить виджет</a></div>
						<div class="widgetInfo"> 

							<div class="desc hidden">
								<div>Выбрано товаров: <span class="widgetCount">0</span></div>
								<div>Тип: <span class="widgetType"></span></div>
								<div>Скин: <span class="widgetSkin"></span></div>

								<div class="input-block">
									<span class="prepend">Код для вставки:&nbsp;&nbsp;</span> 
									<div class="fl"><input type="text" class="custom fl widgetUrl" /></div>
									<div class="fl"><input type="button" value="Скопировать" onclick="clipBoard('.widgetUrl')" /></div>
								</div>
							</div>
						</div>
						<div class="widgetPreview">

						</div>
						
					</div>

				</div>

			</div>    


		</div>
		<div class="holder"></div>
	</body>
</html>

