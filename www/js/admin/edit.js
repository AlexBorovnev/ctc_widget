$(document).ready(function () {

})

function _widgetType(obj) {
}

function _widgetSkin(obj) {
}
function buildColorList() {
    var $ul = $("<ul></ul>");
    for (var i in _colorList) {
        var colorName = _colorList[i];
        var color = "#";
        switch (colorName) {
            case 'Бежевый':
                color += "F5F5DC";
                break;
            case 'Белый':
                color += "FFF";
                break;
            case 'Голубой':
                color += "00BFFF";
                break;
            case 'Желтый':
                color += "FFFF00";
                break;
            case 'Зеленый':
                color += "00FF00";
                break;
            case 'Золотой':
                color += "FFD700";
                break;
            case 'Коричневый':
                color += "964B00";
                break;
            case 'Красный':
                color += "FF0000";
                break;
            case 'Мультицвет':
                color += "MULTICOLOR";
                break;
            case 'Не указан':
                color += "";
                break;
            case 'Оранжевый':
                color += "FF4F00";
                break;
            case 'Розовый':
                color += "FFC0CB";
                break;
            case 'Серебряный':
                color += "C0C0C0";
                break;
            case 'Серый':
                color += "808080";
                break;
            case 'Синий':
                color += "3A75C4";
                break;
            case 'Фиолетовый':
                color += "8B00FF";
                break;
            case 'Черный':
                color += "000";
                break;
        }

//		if(color == 'MULTICOLOR')
        $ul.append('<li class="color" data-color-name="' + colorName + '" data-color="' + color + '">' + colorName + '</li>')

    }
    return $ul;
}

function getCategoryList(shopId, cb) {
    api.call('getCategoryList', {shopId: shopId}, cb);
}
function buildCategoryList(categories) {console.log(categories);
    if (categories.length == 0) {
        toastr.error('нет категорий для отображения');
        return;
    }
    var cats = {};
    for (var i in categories.list) {
        var c = categories.list[i];
        var cid = c.category_id;
        var pid = c.parent_id;
        var t = {
            cid: cid,
            pid: pid,
            title: c.title
        };
        if (pid == 0) {

            cats[cid] = t;
        }
        else {
            if (cats[pid]['childs'] == undefined)
                cats[pid]['childs'] = {};
            cats[pid]['childs'][cid] = t;
        }
    }
    return cats;
}
var initEditor = {
    obj: {},
    init: function (obj) {
        var $tree = buildTree('myTree', obj.categoryList);
        this.obj = obj;
        if (obj.commonRule) {
            this.initCommonRuleSection($tree);
        }
        this.initTreeForSinglePosition($tree);
        this.initEvents('.block-content');

    },
    initTreeForSinglePosition: function (catTree) {
        var base = this;
        for (var i in base.obj.positions) {
            $('.dev-block-' + i + ' .treeHolder').append(catTree.clone(true));
            $('.dev-block-' + i + ' .treeHolder li').each(function () {
                if (base.obj.positions[i].source.category_id != undefined && $(this).data('cid') == base.obj.positions[i].source.category_id) {
                    $(this).find('.Content').addClass('b');
                    $(this).parent().parent().removeClass('ExpandClosed').addClass('ExpandOpen');
                    getOfferList($(this).data('cid'), base.obj.shopId, $(this).parents('.dev-block-' + i), base.obj.positions[i].source.offer_id);
                    return false;
                }
            })
        }
    },


    initCommonRuleSection: function (catTree) {
        var base = this;
        $('.ruleHolder').append(catTree.clone(true));
        if (base.obj.commonRule.categoryId){
            $('.ruleHolder li').each(function(){
                if(base.obj.commonRule.categoryId.indexOf($(this).data('cid')) != -1 ){
                    $(this).find('.Content').addClass('b');
                    $(this).parent().parent().removeClass('ExpandClosed').addClass('ExpandOpen');
                }
            })
        }
        if (base.obj.commonRule.color){

        }
    },

    initEvents: function (selector) {
        var base = this;
        $(selector + ' .treeHolder').on('click', ".Content", function () {
            var cid = $(this).parent().data('cid');
            var pid = $(this).parent().data('pid');
            var $holder = $(this).parents(selector);
            $holder.find(".previewPic img").attr('src', '../../images/preview.png');
            $holder.find(".offerInfo").empty();

            if (pid != 0) {
                $holder.find(".Content").removeClass('b');
                $(this).addClass('b');
                $holder.find(".noOffers").remove();
                $holder.find(".offerHolder li").remove();
                getOfferList(cid, base.obj.shopId, $holder);
            }
            else {
                $(this).prev().trigger('click');
                var event = {};
                event.target = $(this).prev()[0];
                tree_toggle(event);
            }
        });
        $(selector + ' .treeHolder').on('click', ".saveWidget", function(e){
            e.preventDefault();
            var data = {};
            if(widgetType == 3){//free
                data = {
                    'shopId': base.shopId,
                    'skinId': $('[name=skin_id]').val(),
                    'typeId': $('[name=type_id]').val(),
                    'positions': positions
                }
            }
            else{
                data = {
                    'shopId': base.shopId,
                    'skinId': $('[name=skin_id]').val(),
                    'typeId': $('[name=type_id]').val(),
                    'commonRule': self.getCommonRule(),
                    'positions': self.getPositions()
                };
            }

            api.call('setWidget', data, function(response){
                toastr.info('Виджет сохранен, id = ' + response.widgetId);
                widgetId = response.widgetId;
                self.widgetPreview();
            });

        });
    }

}

