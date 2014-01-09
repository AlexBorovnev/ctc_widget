$(document).ready(function () {

})

function _widgetType(obj) {
}

function _widgetSkin(obj) {
}

function getCategoryList(shopId, cb) {
    api.call('getCategoryList', {shopId: shopId}, cb);
}
function buildCategoryList(categories) {
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
        var $catTree = buildTree('myTree', obj.workList.categoryList);
        $('.colorHolder').append(obj.workList.colorList);
        this.obj = obj;
        if (obj.commonRule) {
            this.initCommonRuleSection($catTree);
        }
        this.initColor();
        this.initTreeForSinglePosition($catTree);
        this.initEvents('.block-content');

    },
    initColor: function(color){
        for (var i in color){
            $('.dev-editor-color[data-color-name="' + color[i] + '"]').addClass('active');
        }
    },
    initTreeForSinglePosition: function (catTree) {
        var base = this;
        for (var i in base.obj.positions) {
            $('.dev-block-' + i + ' .treeHolder').append(catTree.clone(true));
            $('.dev-block-' + i + ' .treeHolder li').each(function () {
                if (base.obj.positions[i].source.category_id != undefined && ($(this).data('cid') == base.obj.positions[i].source.category_id)) {
                    $(this).find('.Content').addClass('b');
                    $(this).parent().parent().removeClass('ExpandClosed').addClass('ExpandOpen');
                    getOfferList($(this).data('cid'), base.obj.shopId, $(this).parents('.dev-block-' + i), base.obj.positions[i].source.offer_id);
                    return false;
                } else if (base.obj.positions[i].source.categoryId != undefined && (base.obj.positions[i].source.categoryId.indexOf($(this).data('cid')) != -1)){
                    $(this).find('.Content').addClass('b');
                    $(this).parent().parent().removeClass('ExpandClosed').addClass('ExpandOpen');
                    return false;
                }
            })
            if (base.obj.positions[i].source.color != undefined){
                base.initColor(base.obj.positions[i].source.color);
            }
        }
    },


    initCommonRuleSection: function (catTree) {
        var base = this;
        $('.ruleHolder').append(catTree.clone(true));
        if (base.obj.commonRule.categoryId) {
            $('.ruleHolder li').each(function () {
                if (base.obj.commonRule.categoryId.indexOf($(this).data('cid')) != -1) {
                    $(this).find('.Content').addClass('b');
                    $(this).parent().parent().removeClass('ExpandClosed').addClass('ExpandOpen');
                }
            })
        }
        if (base.obj.commonRule.color) {
            this.initColor([base.obj.commonRule.color]);
        }
    },

    initEvents: function (selector) {
        var base = this;
        $(selector + ' .ruleHolder').on('click', ".Content", function () {
            var cid = $(this).parent().data('cid');
            var pid = $(this).parent().data('pid');
            var $holder = $(this).parents('.ruleHolder');
            if (pid != 0) {
                $(this).toggleClass('b');
            }
            else {
                $(this).prev().trigger('click');
                var event = {};
                event.target = $(this).prev()[0];
                tree_toggle(event);
            }
        });
        $(selector + ' .itemHolder').on('click', ".Content", function () {
            var cid = $(this).parent().data('cid'),
                pid = $(this).parent().data('pid'),
                $holder = $(this).parents('.dev-positions');
            $holder.find(".previewPic img").attr('src', '../../images/preview.png');
            $holder.find(".offerInfo").empty();
            if (pid != 0) {
                    $holder.find(".Content.b").removeClass('b');
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
        $(selector + ' .preparedWidget').on('click', ".saveWidget", function (e) {
            e.preventDefault();
            var data = {};
            if ($(':hidden[name="type_id"]').val() == 3) {//free
                data = {
                    'shopId': base.obj.shopId,
                    'skinId': $('[name=skin_id]').val(),
                    'typeId': $('[name=type_id]').val(),
                    'positions': base.getPositions(),
                    'widgetId': $('[name=widget_id]').val()
                }
            }
            else {
                data = {
                    'shopId': base.obj.shopId,
                    'skinId': $('[name=skin_id]').val(),
                    'typeId': $('[name=type_id]').val(),
                    'commonRule': base.getCommonRule(),
                    'positions': base.getPositions(),
                    'widgetId': $('[name=widget_id]').val()
                };
            }
            api.call('setWidget', data, function (response) {
                toastr.info('Виджет сохранен, id = ' + response.widgetId);
                widgetId = response.widgetId;
                //self.widgetPreview();
            });

        });
        $(selector + ' .colorHolder').on('click', '.dev-editor-color', function () {
            $(this).toggleClass('active');
            if ($(this).hasClass('active')) {
                selectedColors.push($(this).data('colorName'));
            }
            else {
                var ind = selectedColors.indexOf($(this).data('colorName'));
                if (ind != -1)
                    selectedColors.slice(ind, 1);
            }
        });
        $('.removeProduct').on('click', function(e){
            var position = $(this).attr('data-position'),
                count = $('.positionCount span').text();
            e.preventDefault();
            $('.dev-block-' + position).parent().remove();
            count--;
            $('.positionCount span').text(count)
        })
    },
    getPositions: function(){
        var positions = [],
            base = this,
            data = [];
        $('.dev-positions').each(function(){
            var position = $(this).find('[name="item_position"]').val(),
                type = $(this).find('[name="rule_type"]').val();
            positions.push ( {type: type, params: base.getSource(position, type)});
        });
        return positions;
    },
    getCommonRule: function(){
        return this.getRule('.commonRule');
    },
    getSource: function (position, type){
        var params = [];
        if (type == 1){
            return this.getRule('.dev-block-'+ position);
        }else if (type == 2){
            var offer = $('.dev-block-'+ position).find('.offerItem.active').data('offer');
            return new Array(offer['attributes']['id']);
        }
        return params;
    },
    getRule: function (selector){
        var colors = $(selector).find('.dev-editor-color.active'),
            categories = $(selector).find('li .Content.b'),
            colorsValue = [],
            categoriesValue = [],
            params = {};
        colors.each(function(){
            colorsValue.push($(this).data('colorName'));
        });
        categories.each(function(){
            categoriesValue.push($(this).parent().data('cid'));
        });
        if (colorsValue){
            params.color = colorsValue;
        };
        if (categoriesValue){
            params.categoryId = categoriesValue;
        };
        return params;
    }

}