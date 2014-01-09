<script type="text/javascript" src="<?= HOST ?>js/admin/tree.js?<?= REV ?>"></script>
<script type="text/javascript" src="<?=HOST?>js/admin/shop.js?<?=REV?>"></script>
<script type="text/javascript" src="<?= HOST ?>js/admin/main.js?<?= REV ?>"></script>
<script type="text/javascript" src="<?=HOST?>js/admin/main2.js?<?=REV?>"></script>
<script type="text/javascript" src="<?= HOST ?>js/admin/system.js?<?= REV ?>"></script>
<script type="text/javascript" src="<?= HOST ?>js/admin/offer.js?<?= REV ?>"></script>
<script type="text/javascript" src="<?= HOST ?>js/admin/edit.js?<?= REV ?>"></script>
<div class="wrapper clearfix">
	<a href="<?=makeLink("/admin/shop/{$this->shopId}")?>">Назад</a>
	<form>
		<div id="shop<?=$this->widget['shopId']?>">
			<div class="block widget widgetTpl">
				<div class="block-header">Редактирование</div>
				<div class="block-content">

					<div class="block inner widgetData">
						<div class="block-header">Информация о виджете</div>
						<div class="block-content">
							<div class="desc">
								<!--<div>Выбрано товаров: <span class="widgetCount"><?=$this->widget['count'];?></span></div>-->
								<div>Тип: <span class="widgetType"><?=$this->widget['typeName']?></span></div>
								<div>Скин: <span class="widgetSkin"><?=$this->widget['skinName']?></span></div>
								<?php if($this->widget['typeId'] == \model\Widgets::WIDGET_TYPE_FREE):?>
									<div class="positionCount">Позиций: <span><?=count($this->widget['positions'])?></span></div>
								<?php endif; ?>
								

							</div>
						</div>
					</div>
					<?php if($this->widget['typeId'] != \model\Widgets::WIDGET_TYPE_FREE):?>
						<div class="block inner commonRule clearfix">
							<div class="block-header">Общее правило</div>
							<div class="block-content">
								<div class="categoryHolder clearfix">
									<h4>Выбор категории</h4>
									<div class="grid13">
										<div class="ruleHolder editor"></div>
									</div>


								</div>
								<div class="colorHolder clearfix editor">
									<h4>Выбор цвета</h4>

								</div>

							</div>
						</div>

					<?php endif; ?>

					<?php foreach($this->widget['positions'] as $key => $rule):?>
						<div class="block inner choose-product chooseProduct chooseProductTpl clearfix">
							<div class="block-header">Позиция в виджете</div>
							<div class="block-content clearfix dev-block-<?=$key;?> dev-positions">
								<?php if ($rule['typeId'] == \model\Rules::RULE_TYPE_SINGLE):?>
									<?php $rule['source']['common_data'] = unserialize($rule['source']['common_data'])?>
									<div class="categoryTpl">
										<div class="grid13">
											<h4>Выбор категории</h4>
											<div class="treeHolder itemHolder"></div>
										</div>
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
												<img src="<?=$rule['source']['picture'] ?>"/>
											</div>
											<div class="offerInfo">
												<div><?=$rule['source']['common_data']['model'] ?>&nbsp;&nbsp; <?=$rule['source']['common_data']['vendor'] ?></div>

												<div>Цена: <span class='b'><?=$rule['source']['price'] ?></span></div>
												<div>Цвет: <?=$rule['source']['color'] ?></div>
												<div>ID: <?=$rule['source']['offer_id'] ?></div>
												<div>CODE: <?=$rule['source']['common_data']['vendorCode'] ?></div>
											</div>

										</div>
									</div>

									<?php elseif ($rule['typeId'] == \model\Rules::RULE_TYPE_RULE):?>
                                    <div class="block-content">
                                        <div class="categoryHolder clearfix">
                                            <h4>Выбор категории</h4>
                                            <div class="grid13">
                                                <div class="ruleHolder treeHolder editor"></div>
                                            </div>


                                        </div>
                                        <div class="colorHolder clearfix editor">
                                            <h4>Выбор цвета</h4>

                                        </div>

                                    </div>

									<?php endif; ?>
                                <input type="hidden" value="<?=$rule['typeId']?>" name="rule_type">
                                <input type="hidden" value="<?=$key;?>" name="item_position">

							</div>
                            <div class="block-footer">
                                <a href="#" data-position="<?=$key;?>" class="btn removeProduct">Удалить позицию</a>
                            </div>
						</div>

						<?php endforeach;?>

					<div class="block inner preview">
						<div class="block-header">Предпросмотр</div>
						<div class="block-content clearfix">

							<div class="clearfix preparedWidget" style="display:block;">


								<!--div style="margin: 5px"><a class="btn generateWidgetPreview" href="# ">Предпросмотр виджета</a></div-->
								<div style="margin: 5px;"><a href="#" class="btn saveWidget">Сохранить виджет</a></div>
								<div class="widgetInfo">


								</div>
								<div class="widgetPreview">

								</div>

							</div>
						</div>
					</div>

					<div class="treeTpl"></div>

					<div class="tabTpl">
						<div class="wrap">
							<div class="widgets">
								<div>
									<ul class="widget-list">

									</ul>
								</div>
							</div>

						</div>
					</div>

					<input type="hidden" name="shop_id" value="<?= $this->widget['shopId'] ?>">
					<input type="hidden" name="type_id" value="<?= $this->widget['typeId'] ?>">
					<input type="hidden" name="skin_id" value="<?= $this->widget['skinId'] ?>">
                    <input type="hidden" name="widget_id" value="<?= $this->widget['widgetId'] ?>">

					<div class="holder"></div>
				</div>
			</div>
		</div>
	</form>
</div>
<script type="text/javascript">
    $(document).ready(function () {
        initEditor.init({commonRule: <?=json_encode($this->widget['commonRule'])?>,
            positions: <?=json_encode($this->widget['positions'])?>,
            workList: {
                categoryList: buildCategoryList(<?=json_encode($this->categories)?>),
                colorList: buildColorList(<?=json_encode($this->colors)?>)
            },
            shopId: <?= $this->widget['shopId'] ?>
            });
    })
</script>