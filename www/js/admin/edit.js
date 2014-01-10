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
    count: 0,
    filters: ['color', 'categoryId'],
    catTree: [],
    init: function (obj) {
        this.catTree = buildTree('myTree', obj.workList.categoryList);
        $('.colorHolder').append(obj.workList.colorList);
        this.obj = obj;
        if (obj.commonRule) {
            this.initCommonRuleSection();
        }
        this.count = $('[name="widget_count"]').val();
        this.initColor();
        this.initTreeForSinglePosition();
        this.initEvents('.block-content');
        this.manageAddBlockButton();

    },
    initColor: function (selector, color) {
        for (var i in color) {
            $(selector + '[data-color-name="' + color[i] + '"]').addClass('active');
        }
    },
    initTreeForSinglePosition: function () {
        var base = this;
        $('.dev-insert-block .treeHolder').append(this.catTree.clone(true));
        for (var i in base.obj.positions) {
            $('.dev-block-' + i + ' .treeHolder').append(this.catTree.clone(true));
            $('.dev-block-' + i + ' .treeHolder li').each(function () {
                if (base.obj.positions[i].source.category_id != undefined && ($(this).data('cid') == base.obj.positions[i].source.category_id)) {
                    $(this).find('.Content').addClass('b');
                    $(this).parent().parent().removeClass('ExpandClosed').addClass('ExpandOpen');
                    getOfferList($(this).data('cid'), base.obj.shopId, $(this).parents('.dev-block-' + i), base.obj.positions[i].source.offer_id);
                    return false;
                } else if (base.obj.positions[i].source.categoryId != undefined && (base.obj.positions[i].source.categoryId.indexOf($(this).data('cid')) != -1)) {
                    $(this).find('.Content').addClass('b');
                    $(this).parent().parent().removeClass('ExpandClosed').addClass('ExpandOpen');
                }
            })
            if (base.obj.positions[i].source.color != undefined) {
                base.initColor('.dev-block-' + i + ' .dev-editor-color', base.obj.positions[i].source.color);
            }
        }
    },
    initSectionForRule: function(filter, value){
        switch (filter){
            case 'categoryId':
                $('.ruleHolder').append(this.catTree.clone(true));
                $('.ruleHolder li').each(function () {
                    if (value.indexOf($(this).data('cid')) != -1) {
                        $(this).find('.Content').addClass('b');
                        $(this).parent().parent().removeClass('ExpandClosed').addClass('ExpandOpen');
                    }
                })
                break;
            case 'color':
                this.initColor('.dev-editor-color', value);
        }
    },

    initCommonRuleSection: function () {
        var base = this,
            rule = base.obj.commonRule;

        for (var i in base.filters){
            if (rule[base.filters[i]]){
                base.initSectionForRule(base.filters[i], rule[base.filters[i]]);
            }
        }
    },

    initEvents: function (selector) {
        var base = this;
        $(selector + ' .ruleHolder').on('click', ".Content", function () {
            var cid = $(this).parent().data('cid');
            var pid = $(this).parent().data('pid');
            if (pid != 0) {
                $(this).toggleClass('b');
                if (!$(this).hasClass('b')) {
                    $(this).parents('.IsRoot').children().removeClass('b');
                }
            }
            else {
                $(this).prev().trigger('click');
                $(this).toggleClass('b');
                if ($(this).hasClass('b')) {
                    $(this).parent().find('li .Content').addClass('b');
                } else {
                    $(this).parent().find('li .Content').removeClass('b');
                }
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
                    'widgetId': $('[name=widget_id]').val(),
                    'title': $('[name=widget_name]').val()
                }
            }
            else {
                data = {
                    'shopId': base.obj.shopId,
                    'skinId': $('[name=skin_id]').val(),
                    'typeId': $('[name=type_id]').val(),
                    'commonRule': base.getCommonRule(),
                    'positions': base.getPositions(),
                    'widgetId': $('[name=widget_id]').val(),
                    'title': $('[name=widget_name]').val()
                };
            }
            api.call('setWidget', data, function (response) {
                toastr.info('Виджет сохранен, id = ' + response.widgetId);
                widgetId = response.widgetId;
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
        $('.removeProduct').on('click', function (e) {
            var position = $(this).attr('data-position');
            e.preventDefault();
            $('.dev-block-' + position).parent().remove();
            base.count--;
            $('.positionCount span').text(base.count);
            $('.addBlockContent').trigger('checkPositions');
        });
        $('.addItem').on('click', function (e) {
            e.preventDefault();
            if ($(this).hasClass('active')) {
                base.addBlock(['dev-insert-block', 'dev-item-block']);
            }
        });
        $('.addBlockContent').on('checkPositions', function () {
            base.manageAddBlockButton();
        });
        $('.addRule').on('click', function (e) {
            e.preventDefault();
            if ($(this).hasClass('active')) {
                base.addBlock(['dev-insert-block', 'dev-rule-block']);
            }
        });
    },
    addBlock: function (selector) {
        var position = $('.widget .dev-positions [name="item_position"]').last().val(),
            $offerBlock = $('.' + selector.join('.')).clone(true);
        $offerBlock.removeClass('hidden ' + selector.join(' ')).insertBefore($('.addBlock'));
        var $holder = $('.block.widget').find('.block.inner.chooseProduct').last();
        position++;
        $holder.find('.dev-positions').addClass('dev-block-' + position);
        $holder.find('[name="item_position"]').val(position);
        $holder.find('.removeProduct').attr('data-position', position);
        this.count++;
        $('.positionCount span').text(this.count);
        $('.addBlockContent').trigger('checkPositions');
    },
    manageAddBlockButton: function () {
        var typeWidget = $('[name=type_id]').val();
        $('.addBlockContent').addClass('active');
        switch (typeWidget) {
            case '1':
                if (this.count >= 1) {
                    $('.addBlockContent').removeClass('active');
                }
                return true;
            case '2':
                if (this.count >= 2) {
                    $('.addBlockContent').removeClass('active');
                }
                return true;
            case '3':
                if (this.count >= 7) {
                    $('.addBlockContent').removeClass('active');
                }
                return true;
        }
    },
    getPositions: function () {
        var positions = [],
            base = this;
        $('.dev-positions').each(function () {
            var position = $(this).find('[name="item_position"]').val(),
                type = $(this).find('[name="rule_type"]').val();
            if (position) {
                positions.push({type: type, params: base.getSource(position, type)});
            }
        });
        return positions;
    },
    getCommonRule: function () {
        return this.getRule('.commonRule');
    },
    getSource: function (position, type) {
        var params = [];
        if (type == 1) {
            return this.getRule('.dev-block-' + position);
        } else if (type == 2) {
            var offer = $('.dev-block-' + position).find('.offerItem.active').data('offer');
            if (offer){
                return new Array(offer['attributes']['id']);
            }
        }
        return params;
    },
    getRule: function (selector) {
        var colors = $(selector).find('.dev-editor-color.active'),
            categories = $(selector).find('li .Content.b'),
            colorsValue = [],
            categoriesValue = [],
            params = {};
        colors.each(function () {
            colorsValue.push($(this).data('colorName'));
        });
        categories.each(function () {
            categoriesValue.push($(this).parent().data('cid'));
        });
        if (colorsValue) {
            params.color = colorsValue;
        };
        if (categoriesValue) {
            params.categoryId = categoriesValue;
        };
        return params;
    }
}