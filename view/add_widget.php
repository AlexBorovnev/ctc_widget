<script type="text/javascript" src="<?=HOST?>js/admin/tree.js?<?=REV?>"></script>

<script type="text/javascript" src="<?=HOST?>js/admin/shop.js?<?=REV?>"></script>
<script type="text/javascript" src="<?=HOST?>js/admin/widget.js?<?=REV?>"></script>
<script type="text/javascript" src="<?=HOST?>js/admin/offer.js?<?=REV?>"></script>
<script type="text/javascript" src="<?=HOST?>js/admin/main2.js?<?=REV?>"></script>
<script type="text/javascript" src="<?=HOST?>js/admin/main.js?<?=REV?>"></script>
<script type="text/javascript" src="<?=HOST?>js/admin/system.js?<?=REV?>"></script>
<script>

	$(function(){
			shopObj = (<?=$this->shop?>);
			mainInit(shopObj);
	});

</script>

<div class="wrapper clearfix">
	<div id="tabs">
		<ul class="tabList">


		</ul>



	</div>
</div>
<div class="hidden">
	<div class="selectedOffer selectedOfferTpl">
		<h5 class="fl mr40">Выбранный товар</h5><a href="#" class="changeOffer green">Изменить</a>
		<div class="forPreview"></div>
	</div>
	<div class="selectedRule selectedRuleTpl">
		<h5 class="fl mr40">Выбранные правила:</h5><a href="#" class="changeRule green">Изменить</a>
		<div class="forPreviewRule">
			<div><span class="b">Категории: </span><span class="ruleCategories">222</span><br></div>
			<div><span class="b">Цвета: </span><span class="ruleColors">33123</span></div>
		</div>
	</div>
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
            <div class="search">
                <input type="text" name="offer_id" placeholder="Введите артикул товара">
                <a href="#" class="btn searchProduct">Поиск</a>
            </div>
			<ol class="offerHolder">

			</ol>
		</div>
		<div class="grid13">
			<h4>Предпросмотр товара</h4>
			<div class="preview">
				<div class="previewPic">
					<img src="<?=makeLink('/images/preview.png')?>" />
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

	<div data-init-pos="" class="pos freePositionTpl dev-positions">
		<div class="header">
			<h4 class="fl mr20"><span class="positionNum"></span> позиция</h4>
            <a href="#" data-position-num="" class="removePosition">Удалить позицию</a>
        </div>
		<div class="body clearfix">
			<a href="#" class="btn choseProduct">Выбрать товар</a>
			<a href="#" class="btn createRule">Создать правило</a>
<!--			<a href="#" class="btn savePosition">Сохранить позицию</a>-->
			<div class="categoryOfferHolder clearfix"></div>
			<div class="ruleHolder clearfix"></div>
			<div class="_preview"></div>
		</div>
	</div>
	<div class="rule ruleTpl">
		<div class="block-content clearfix">
			<div class="clearfix">
				<div class="categoryHolder clearfix">
				</div>


				<div class="colorHolder clearfix">
				</div>
			</div>
			<div class="clearfix">
				<a href="#" class="btn saveRule"><span>Сохранить правило</span></a>
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

                <div class="search">
                    <input type="text" name="offer_id" placeholder="Введите артикул товара">
                    <a href="#" class="btn searchProduct">Поиск</a>
                </div>
				<ol class="offerHolder">

				</ol>
			</div>
			<div class="grid13">
				<h4>Предпросмотр товара</h4>
				<div class="preview">
					<div class="previewPic">
						<img src="<?=makeLink('/images/preview.png')?>" />
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
			<div class="widgets hidden">
				<a href="#" class="btn add-widget">+ виджет</a>
				<div>
					<ul class="widget-list hidden">

					</ul>
				</div>
			</div>

		</div>
	</div>
	<div class="pos positionTpl dev-positions">
		<div class="header"><h4><span class="positionNum"></span> позиция</h4></div>
		<div class="body">
			<a href="#" class="btn choseProduct">Выбрать товар</a>
			<div class="categoryOfferHolder"></div>
			<div class="_preview"></div>
		</div>
	</div>

	<div class="block inner smallWidget smallWidgetTpl widget">
		<div class="block-header">Маленький виджет</div>
		<div class="block-content">
			<a href="#" class="btn createRule">Создать правило</a>
			<div class="clearfix">
				<div class="rule_preview"></div>
			</div>
            
				<div class="ruleHolder"></div>
				<div class="positionHolder clearfix"  data-position-num="1">
			
			</div>


		</div>
	</div>
	<div class="block inner bigWidget bigWidgetTpl widget">
		<div class="block-header">Большой виджет</div>
		<div class="block-content">
			<a href="#" class="btn createRule">Создать правило</a>
			<div class="clearfix">
				<div class="rule_preview"></div>
			</div>
			<div class="ruleHolder"></div>
			
			<div class="positionHolder clearfix positionHolder1" data-position-num="1">

			</div>
			<div class="positionHolder clearfix positionHolder2" data-position-num="2">

			</div>
			


		</div>
	</div>
	<div class="block inner widget freeWidget freeWidgetTpl">
		<div class="block-header">Свободный виджет</div>
		<div class="block-content">

			<div class="positions">

			</div>
			<a href="#" class="btn btn-primary addPosition">Добавить позицию</a>

		</div>
	</div>
	<div class="new-widget newWidgetTpl clearfix">
		<h3>Создание нового виджета</h3>

		<div class="clearfix">
			<div class="input-block">
				<span class="prepend">Название: </span>
				<input type="text" name="title" id="widgetTitle" placeholder="Название виджета"> 
			</div>
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

		<div class="block inner clearfix preparedWidget">
			<div class="block-header">Виджет</div>
			<div class="block-content clearfix">
				<!--div style="margin: 5px"><a class="btn generateWidgetPreview" href="# ">Предпросмотр виджета</a></div-->		
				<div style="margin: 5px;"><a href="#" class="btn saveWidget">Сохранить виджет</a></div>
				<div class="widgetInfo"> 

					<div class="desc hidden">
						<div>Название виджета: <span class="widgetTitle"></span></div>
						<!--div>Выбрано товаров: <span class="widgetCount"></span></div-->
						<div>Тип: <span class="widgetType"></span></div>
						<div>Скин: <span class="widgetSkin"></span></div>

						<div class="input-block">
							<span class="prepend">Код для вставки:&nbsp;&nbsp;</span> 
							<div class="fl"><input type="text" class="custom fl widgetUrl" /></div>
							<!--<div class="fl"><input type="button" value="Скопировать" onclick="clipBoard('.widgetUrl')" /></div>-->
						</div>
						<div><a href="" class="previewUrl">Предпросмотр</a></div>
					</div>
				</div>
				<div class="widgetPreview">

				</div>

			</div>

		</div>

	</div>    


		</div>
		<div class="holder"></div>
	