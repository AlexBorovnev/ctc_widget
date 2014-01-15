<script type="text/javascript" src="<?= HOST ?>js/admin/tree.js?<?= REV ?>"></script>
<script type="text/javascript" src="<?= HOST ?>js/admin/shop.js?<?= REV ?>"></script>
<script type="text/javascript" src="<?= HOST ?>js/admin/main.js?<?= REV ?>"></script>
<script type="text/javascript" src="<?= HOST ?>js/admin/main2.js?<?= REV ?>"></script>
<script type="text/javascript" src="<?= HOST ?>js/admin/system.js?<?= REV ?>"></script>
<script type="text/javascript" src="<?= HOST ?>js/admin/offer.js?<?= REV ?>"></script>
<script type="text/javascript" src="<?= HOST ?>js/admin/edit.js?<?= REV ?>"></script>
<script>
    $(function () {
        shopObj = (<?=$this->shop?>);
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
                    <div class="block-content">
                        <div class="categoryHolder clearfix">
                            <h4>Выбор категории</h4>

                            <div class="grid13">
                                <div class="ruleHolder editor dev-category-rule"></div>
                            </div>
                        </div>
                        <div class="colorHolder clearfix editor">
                            <h4>Выбор цвета</h4>
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
                                        <div class="treeHolder itemHolder  dev-offer-category"></div>
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
                                            <img src="<?= $rule['source']['picture'] ?>"/>
                                        </div>
                                        <div class="offerInfo">
                                            <div><?= $rule['source']['common_data']['model'] ?>
                                                &nbsp;&nbsp; <?= $rule['source']['common_data']['vendor'] ?></div>
                                            <div>Цена: <span class='b'><?= $rule['source']['price'] ?></span></div>
                                            <div>
                                                Доступно: <?= $rule['source']['is_available'] ? 'Да' : 'Нет на складе' ?></div>
                                            <div>Цвет: <?= $rule['source']['color'] ?></div>
                                            <div>ID: <?= $rule['source']['offer_id'] ?></div>
                                            <div>CODE: <?= $rule['source']['common_data']['vendorCode'] ?></div>
                                        </div>
                                    </div>
                                    <? else: ?>
                                    <div class="preview">
                                        <div class="previewPic">
                                            <img src="../../images/preview.png"/>
                                        </div>
                                        <div class="offerInfo">
                                        </div>
                                    </div>
                                    <?php endif; ?>
                                </div>
                                <input type="hidden" value="<?= \model\Rules::RULE_TYPE_SINGLE?>" name="rule_type">
                            </div>

                        <?php if (!empty($rule['freeWidgetRules'])): ?>
                            <div class="block-content clearfix">
                                <div class="categoryHolder clearfix dev-category-rule">
                                    <h4>Выбор категории</h4>

                                    <div class="grid13">
                                        <div class="ruleHolder treeHolder editor"></div>
                                    </div>
                                </div>
                                <div class="colorHolder clearfix editor">
                                    <h4>Выбор цвета</h4>
                                </div>
                                <input type="hidden" value="<?= \model\Rules::RULE_TYPE_RULE ?>" name="rule_type">
                            </div>
                        <?php endif; ?>
                        <input type="hidden" value="<?= $key; ?>" name="item_position">
                    </div>
                    <div class="block-footer">
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
<div
    class="block inner choose-product chooseProduct chooseProductTpl clearfix  dev-insert-block dev-item-block hidden">
    <div class="block-header">Позиция в виджете. Спопособ выбора
        товара: <?= \model\Rules::RULE_TYPE_SINGLE_TITLE ?></div>
    <div class="block-content clearfix dev-positions">
        <div class="block-content clearfix">
        <div class="categoryTpl">
            <div class="grid13">
                <h4>Выбор категории</h4>
                <div class="treeHolder itemHolder  dev-offer-category"></div>
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
        <div class="block-content clearfix">
            <div class="categoryHolder clearfix">
                <h4>Выбор категории</h4>

                <div class="grid13">
                    <div class="ruleHolder treeHolder editor dev-category-rule"></div>
                </div>
            </div>
            <div class="colorHolder clearfix editor">
                <h4>Выбор цвета</h4>
            </div>
            <input type="hidden" value="<?= \model\Rules::RULE_TYPE_RULE ?>" name="rule_type">
        </div>
        <? endif;?>
        <input type="hidden" value="<?= \model\Rules::RULE_TYPE_SINGLE ?>" name="rule_type">
        <input type="hidden" value="" name="item_position">
    </div>
    <div class="block-footer">
        <a href="#" data-position="" class="btn removeProduct">Удалить позицию</a>
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