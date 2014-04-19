<script type="text/javascript" src="<?= HOST ?>js/admin/tree.js?<?= REV ?>"></script>
<script type="text/javascript" src="<?= HOST ?>js/admin/shop.js?<?= REV ?>"></script>
<script type="text/javascript" src="<?= HOST ?>js/admin/main.js?<?= REV ?>"></script>
<script type="text/javascript" src="<?= HOST ?>js/admin/main2.js?<?= REV ?>"></script>
<script type="text/javascript" src="<?= HOST ?>js/admin/system.js?<?= REV ?>"></script>
<script type="text/javascript" src="<?= HOST ?>js/admin/offer.js?<?= REV ?>"></script>
<script type="text/javascript" src="<?= HOST ?>js/admin/edit.js?<?= REV ?>"></script>
<script type="text/javascript" src="<?=HOST?>js/admin/param.js?<?=REV?>"></script>
<script>
    $(function () {
        var shopObj = {shop: <?=$this->shop?>, hostServer: '<?=HOST?>'};
        mainInit(shopObj);
    });
</script>
<div class="wrapper clearfix">
<form>
<div id="shop<?= $this->widget['shopId'] ?>">
    <div class="block widget widgetTpl">
        <div class="block-header">Редактирование</div>
        <div class="block-content">
            <div class="block inner widgetData">
                <div class="block-header">Информация о виджете</div>
                <div class="block-content">
                    <div class="desc">
                        <div class="input-block">
                            <span class="prepend">Название: </span>
                            <input type="text" class="widgetName" name="widget_name" id="widgetTitle"
                                   maxlength=50 placeholder="Название виджета"
                                   value="<?= $this->widget['widgetName'] ?>">
                        </div>
                        <div class="input-block">Тип: <span
                                class="widgetType"><?= $this->widget['typeName'] ?></span></div>
                        <div class="input-block">Скин: <span
                                class="widgetSkin"><?= $this->widget['skinName'] ?></span></div>
                        <?php if ($this->widget['typeId'] == \model\Widgets::WIDGET_TYPE_FREE): ?>
                            <div class="input-block positionCount">Позиций: <span><?=
                                    count(
                                        $this->widget['positions']
                                    ) ?></span>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php if ($this->widget['typeId'] != \model\Widgets::WIDGET_TYPE_FREE): ?>
                <div class="block inner commonRule clearfix">
                    <div class="block-header">Общее правило</div>
                    <div class="block-content rule">
                        <div class="categoryHolder clearfix">
                            <h4>Выбор категории</h4>

                            <div class="grid13">
                                <div class="ruleHolder editor dev-category-rule dev-rule">
                                <?=$this->getTreeView($this->categories, $this->widget['shopId']);?>
                                </div>
                            </div>
                        </div>
                        <div class="paramsHolder clearfix editor grid13 paramTpl">
                            <? if (isset($this->widget['commonRule']['categoryId']) && isset($this->widget['commonRule']['param'])):?>
                                <?=$this->buildParamsBlock($this->widget['commonRule']['categoryId'], $this->widget['commonRule']['param'], $this->widget['shopId']);?>
                            <? endif; ?>
                    </div>
                    </div>
                </div>
            <?php endif; ?>
            <?php foreach ($this->widget['positions'] as $key => $rule): ?>
                <div class="block inner choose-product chooseProduct chooseProductTpl clearfix">
                    <div class="block-header">Позиция в виджете. Спопособ выбора
                        товара: <?= $rule['typeName']; ?></div>
                    <div class="block-content clearfix dev-block-<?= $key; ?> dev-positions">
                        <div class="block-content clearfix">
                                <div class="categoryTpl">
                                    <div class="grid13">
                                        <h4>Выбор категории</h4>
                                        <div class="treeHolder itemHolder  dev-offer-category dev-rule">
                                            <?=$this->getTreeView($this->categories, $this->widget['shopId']);?>
                                        </div>
                                    </div>
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
                                    <?php if (!empty($rule['source'])):
                                     $rule['source']['common_data'] = unserialize(
                                        $rule['source']['common_data']
                                    ) ?>
                                    <div class="preview">
                                        <div class="previewPic">
                                            <? $picture = @unserialize($rule['source']['picture']); ?>
                                            <img src="<?= ($picture !== false) ? $picture[0] : $rule['source']['picture'] ?>"/>
                                        </div>
                                        <div class="offerInfo">
                                            <div><?=trim($rule['source']['title'])?></div>
                                            <div>Цена: <span class='b'><?= $rule['source']['price'] ?></span></div>
                                            <div>
                                                Доступно: <?= $rule['source']['is_available'] ? 'Да' : 'Нет на складе' ?>
                                            </div>
                                            <? if (isset($rule['source']['common_data']['param'])): ?>
                                                <div>Параметры:</br>
                                                <? foreach ($rule['source']['common_data']['param'] as $name => $value): ?>
                                                    &nbsp;&nbsp;&nbsp;<?=$name?>:&nbsp;<?=$value?></br>
                                                <? endforeach; ?>
                                                 </div>
                                            <? endif; ?>
                                            <div>ID: <?= $rule['source']['offer_id'] ?></div>
                                            <div>Категория: <?= $rule['source']['category_title']?></div>
                                            <? if (isset($rule['source']['common_data']['vendorCode'])): ?>
                                                <? if(is_array($rule['source']['common_data']['vendorCode'])):?>
                                                    <div>CODE: <?= $rule['source']['common_data']['vendorCode'][0] ?></div>
                                                <? else: ?>
                                                    <div>CODE: <?= $rule['source']['common_data']['vendorCode'] ?></div>
                                                <? endif; ?>
                                            <?endif;?>
                                        </div>
                                    </div>
                                    <? else: ?>
                                    <div class="preview">
                                        <div class="previewPic"><img src="../../images/preview.png"/></div>
                                        <div class="offerInfo"></div>
                                    </div>
                                    <?php endif; ?>
                                </div>
                                <input type="hidden" value="<?= \model\Rules::RULE_TYPE_SINGLE?>" name="rule_type">
                            </div>
                        <?php if (!empty($rule['freeWidgetRules'])): ?>
                        <div class="block-content clearfix  rule">
                            <div class="categoryHolder clearfix dev-category-rule">
                                <h4>Выбор категории</h4>

                                <div class="grid13">
                                    <div class="ruleHolder treeHolder editor dev-rule">
                                    <?=$this->getTreeView($this->categories, $this->widget['shopId']);?>
                                    </div>
                                </div>
                            </div>
                            <div class="paramsHolder clearfix editor grid13 paramTpl">
                                <?=$this->buildParamsBlock(isset($rule['freeWidgetRules']['categoryId'])?$rule['freeWidgetRules']['categoryId']:array(),
                                    isset($rule['freeWidgetRules']['param'])?$rule['freeWidgetRules']['param']:array(),
                                    $this->shopId);
                                ?>
                            </div>
                            <input type="hidden" value="<?= \model\Rules::RULE_TYPE_RULE ?>" name="rule_type">
                        </div>
                        <?php endif; ?>
                        <input type="hidden" value="<?= $key; ?>" name="item_position">
                    </div>
                    <div class="block-footer">
                        <?php if ($this->widget['typeId'] == \model\Widgets::WIDGET_TYPE_FREE):?>
                            <a class="upProduct" data-position="<?= $key; ?>" href="#"><img src="<?= makeLink('/images/uparrow.png') ?>"></a>
                            <a class="downProduct" data-position="<?= $key; ?>" href="#"><img src="<?= makeLink('/images/downarrow.png') ?>"></a>
                        <?php endif;?>
                        <a href="#" data-position="<?= $key; ?>" class="btn removeProduct">Удалить позицию</a>
                    </div>
                </div>
            <?php endforeach; ?>
            <div class="block inner addBlock">
                <div class="block-header">Добавление блока</div>
                <div class="block-content clearfix">
                    <a href="#" class="btn addItem addBlockContent active">Добавить товар</a>
                </div>
            </div>
            <div class="block inner preview">
                <div class="block-header">Сохранение</div>
                <div class="block-content clearfix">
                    <div class="clearfix preparedWidget" style="display:block;">
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
            <input type="hidden" name="widget_count" value="<?= count($this->widget['positions']) ?>">
            <input type="hidden" name="shop_id" value="<?= $this->widget['shopId'] ?>">
            <input type="hidden" name="type_id" value="<?= $this->widget['typeId'] ?>">
            <input type="hidden" name="skin_id" value="<?= $this->widget['skinId'] ?>">
            <input type="hidden" name="widget_id" value="<?= $this->widget['widgetId'] ?>">

            <div class="holder"></div>
        </div>
    </div>
</div>
<div class="block inner choose-product chooseProduct chooseProductTpl clearfix  dev-insert-block dev-item-block hidden">
    <div class="block-header">Позиция в виджете. Спопособ выбора
        товара: <?= \model\Rules::RULE_TYPE_SINGLE_TITLE ?></div>
    <div class="block-content clearfix dev-positions">
        <div class="block-content clearfix">
        <div class="categoryTpl">
            <div class="grid13">
                <h4>Выбор категории</h4>
                <div class="treeHolder itemHolder  dev-offer-category">
                    <?=$this->getTreeView($this->categories, $this->widget['shopId']);?>
                </div>
            </div>
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
                    <img src="<?= makeLink('/images/preview.png') ?>"/>
                </div>
                <div class="offerInfo">
                </div>
            </div>
        </div>
            </div>
        <?php if ($this->widget['typeId'] == \model\Widgets::WIDGET_TYPE_FREE): ?>
        <div class="rule"><div class="block-content clearfix rule ">
            <div class="categoryHolder clearfix">
                <h4>Выбор категории</h4>

                <div class="grid13">
                    <div class="ruleHolder treeHolder editor dev-category-rule dev-rule">
                        <?=$this->getTreeView($this->categories, $this->widget['shopId']);?>
                    </div>
                </div>
            </div>
            <div class="paramsHolder clearfix editor grid13 paramTpl">
            </div>
            <input type="hidden" value="<?= \model\Rules::RULE_TYPE_RULE ?>" name="rule_type">
        </div>
        </div>
        <? endif;?>
        <input type="hidden" value="<?= \model\Rules::RULE_TYPE_SINGLE ?>" name="rule_type">
        <input type="hidden" value="" name="item_position">
    </div>
    <div class="block-footer">
        <?php if ($this->widget['typeId'] == \model\Widgets::WIDGET_TYPE_FREE):?>
            <a class="upProduct" data-position="" href="#"><img src="<?= makeLink('/images/uparrow.png') ?>"></a>
            <a class="downProduct" data-position="" href="#"><img src="<?= makeLink('/images/downarrow.png') ?>"></a>
        <?php endif; ?>
        <a href="#" data-position="" class="btn removeProduct">Удалить позицию</a>
    </div>
</div>
</form>
</div>
<script type="text/javascript">
    showLoading(true);
    $(window).load(function(){
        $('.loading').fadeOut(2000);
        $('.overlay').fadeOut(2000);
    });

    $(document).ready(function () {
        initEditor.init({commonRule: <?=json_encode($this->widget['commonRule'], JSON_NUMERIC_CHECK | JSON_FORCE_OBJECT )?>,
            positions: <?=json_encode($this->widget['positions'], JSON_NUMERIC_CHECK | JSON_FORCE_OBJECT )?>,
            shopId: <?= $this->widget['shopId'] ?>
        });
    })
</script>