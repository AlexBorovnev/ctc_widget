<script type="text/javascript" src="<?= HOST ?>js/admin/tree.js?<?= REV ?>"></script>
<script type="text/javascript" src="<?=HOST?>js/admin/shop.js?<?=REV?>"></script>
<script type="text/javascript" src="<?= HOST ?>js/admin/main.js?<?= REV ?>"></script>
<script type="text/javascript" src="<?=HOST?>js/admin/main2.js?<?=REV?>"></script>
<script type="text/javascript" src="<?= HOST ?>js/admin/system.js?<?= REV ?>"></script>
<script type="text/javascript" src="<?= HOST ?>js/admin/offer.js?<?= REV ?>"></script>
<script type="text/javascript" src="<?= HOST ?>js/admin/edit.js?<?= REV ?>"></script>
<div id="shop<?=$this->widget['shopId']?>">
<div class="block widget widgetTpl">
    <div class="block-header"><h3>Редактирование</h3></div>
    <div class="block-content">

    </div>
</div>
<div class="new-widget newWidgetTpl clearfix" >
    <div class="block clearfix preparedWidget" style="display:block;">
        <div class="block-header">Виджет</div>
        <div class="block-content clearfix">
            <!--div style="margin: 5px"><a class="btn generateWidgetPreview" href="# ">Предпросмотр виджета</a></div-->
            <div style="margin: 5px;"><a href="#" class="btn saveWidget">Сохранить виджет</a></div>
            <div class="widgetInfo">

                <div class="desc">
                    <div>Выбрано товаров: <span class="widgetCount"><?=$this->widget['count'];?></span></div>
                    <div>Тип: <span class="widgetType"><?=$this->widget['typeName']?></span></div>
                    <div>Скин: <span class="widgetSkin"><?=$this->widget['skinName']?></span></div>

                    <div class="input-block">
                        <span class="prepend">Код для вставки:&nbsp;&nbsp;</span>

                        <div class="fl"><input type="text" class="custom fl widgetUrl"/></div>
                        <div class="fl"><input type="button" value="Скопировать" onclick="clipBoard('.widgetUrl')"/>
                        </div>
                    </div>
                </div>
            </div>
            <div class="widgetPreview">

            </div>

        </div>

    </div>

</div>

<?php if($this->widget['typeId'] == \model\Widgets::WIDGET_TYPE_FREE):?>
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
<?php else: ?>
<div class="positionTpl">
    <div class="header"><h4><span class="positionNum"></span> позиция</h4></div>
    <div class="body">
        <a href="#" class="btn choseProduct">Выбрать товар</a>

        <div class="categoryOfferHolder"></div>
    </div>
</div>
<?php endif; ?>
<div class="rule ruleTpl">
    <div class="block-content clearfix">
        <div class="categoryHolder clearfix"></div>

        <div class="colorHolder clearfix">
        </div>

    </div>

</div>
<div class="block choose-product chooseProduct chooseProductTpl clearfix">
    <div class="block-header"><span>Выбранный товар</span> </div>
    <?php foreach($this->widget['positions'] as $key => $rule):?>
        <div class="block-content clearfix dev-block-<?=$key;?>">
        <?php if ($rule['typeId'] == \model\Rules::RULE_TYPE_SINGLE):?>
            <?php $rule['source']['common_data'] = unserialize($rule['source']['common_data'])?>
            <div class="categoryTpl">
            <div class="grid13">
                <h4>Выбор категории</h4>
                <div class="treeHolder"></div>
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
                    <a href="#" class="btn addProduct">Выбрать</a>
                </div>
            </div>

            <?php elseif ($rule['typeId'] == \model\Rules::RULE_TYPE_RULE):?>
<!--                <div class="grid13">-->
<!--                    <h4>Выбор категории</h4>-->
<!--                    <div class="treeHolder"></div>-->
<!--                </div>-->
            <?php endif; ?>
        </div>
        <div class="block-footer">

        </div>
    <?php endforeach;?>

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

<?php switch ($this->widget['typeId']):
case \model\Widgets::WIDGET_TYPE_SMALL:
    ?>

    <div class="block smallWidget smallWidgetTpl widget">
        <div class="block-header">Маленький виджет</div>
        <div class="block-content">
            <a href="#" class="btn createRule">Сохранить правило</a>
            <div class="grid13">
            <h4>Выбор категории</h4>
            <div class="ruleHolder editor"></div>
            </div>
            <div class="colorTpl editor clearfix">
                <h4>Выбор цвета</h4>
            </div>
        </div>
    </div>
    <?php break;
case \model\Widgets::WIDGET_TYPE_BIG:?>
<div class="block bigWidget bigWidgetTpl widget">
    <div class="block-header">Большой виджет</div>
    <div class="block-content">
        <a href="#" class="btn createRule">Сохранить правило</a>

        <h4>Выбор категории</h4>
        <div class="grid13">
        <div class="ruleHolder editor"></div>
            </div>

        <div class="colorTpl clearfix editor">
            <h4>Выбор цвета</h4>
            <div class="grid13">
        </div>
            </div>
    </div>
</div>
<?php break;
case \model\Widgets::WIDGET_TYPE_FREE: ?>
<div class="block widget freeWidget freeWidgetTpl">
    <div class="block-header">Свободный виджет</div>
    <div class="block-content">

        <div class="positions">

        </div>
        <a href="#" class="btn addPosition">Добавить позицию</a>

    </div>
</div>
<?php break;
endswitch; ?>
<input type="hidden" name="shop_id" value="<?= $this->widget['shopId'] ?>">
<input type="hidden" name="type_id" value="<?= $this->widget['typeId'] ?>">
<input type="hidden" name="skin_id" value="<?= $this->widget['skinId'] ?>">


<div class="holder"></div>
</div>
<script type="text/javascript">
    $(document).ready(function () {
        initEditor.init({commonRule: <?=json_encode($this->widget['commonRule'])?>,
            positions: <?=json_encode($this->widget['positions'])?>,
            categoryList: buildCategoryList(<?=json_encode($this->categories)?>),
            shopId: <?= $this->widget['shopId'] ?>});
    })
</script>