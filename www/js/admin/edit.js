$(document).ready(function () {

})

function _widgetType(obj) {
}

function _widgetSkin(obj) {
}
var initEditor = {
    obj: {},
    count: 0,
    filters: ['param', 'categoryId'],
    catTree: [],
    status: 'ok',
    init: function (obj) {
        this.obj = obj;

        //this.catTree = buildTree('myTree', obj.workList.categoryList, this.obj.shopId, undefined, true);
        //$('.paramsHolder').append(obj.workList.paramsList);

        //if (obj.commonRule) {
        this.initCommonRuleSection();
        //}
        this.count = $('[name="widget_count"]').val();
        //this.initParams();
        this.initTreeForSinglePosition();
        this.initEvents('.block-content');
        this.manageAddBlockButton();
        //showLoading(false);
    },
    initParams: function (selector, color) {
        for (var i in color) {
            $(selector + '[data-color-name="' + color[i] + '"]').addClass('active');
        }
    },
    initTreeForSinglePosition: function () {
        var base = this;
        //$('.dev-insert-block .treeHolder').append(this.catTree.clone(true));

        for (var i in base.obj.positions) {
            var catIds = [];
            if (base.obj.positions[i].freeWidgetRules != undefined && base.obj.positions[i].freeWidgetRules.categoryId){
                catIds = $.map(base.obj.positions[i].freeWidgetRules.categoryId, function(value, index) {
                    return [value];
                });
            }
            $('.dev-block-' + i + ' .treeHolder li').each(function () {
                if ($(this).parents('.dev-offer-category').length > 0 && base.obj.positions[i].source != undefined && base.obj.positions[i].source.category_id != undefined && ($(this).data('cid') == base.obj.positions[i].source.category_id)) {
                    $(this).find('.Content').addClass('b');
                    $(this).parents('.Node').removeClass('ExpandClosed').addClass('ExpandOpen');
                    getOfferList($(this).data('cid'), base.obj.shopId, $(this).parents('.dev-block-' + i), base.obj.positions[i].source.offer_id);
                }
                if ($(this).parents('.dev-category-rule').length > 0 && catIds && catIds.indexOf($(this).data('cid')) != -1) {
                    $(this).find('.Content').addClass('b');
                    $(this).parents().parent().removeClass('ExpandClosed').addClass('ExpandOpen');
                }
            })
        }
    },
    initSectionForRule: function (filter, value) {
        switch (filter) {
            case 'categoryId':
                if (value){
                    var catIds = $.map(value, function(value, index) {
                        return [value];
                    });
                    $('.ruleHolder li').each(function () {
                        if (catIds.indexOf($(this).data('cid')) != -1) {
                            $(this).find('.Content').addClass('b');
                            $(this).parents('.Node').removeClass('ExpandClosed').addClass('ExpandOpen');
                        }
                    })
                }
                break;
        }
    },

    initCommonRuleSection: function () {
        var base = this,
            rule = base.obj.commonRule || [];
        for (var i in base.filters) {
            if (rule[base.filters[i]]) {
                base.initSectionForRule(base.filters[i], rule[base.filters[i]]);
            } else {
                base.initSectionForRule(base.filters[i]);
            }
        }
    },

    initEvents: function (selector) {
        var base = this;
        $(selector + ' .dev-category-rule').on('click', ".Content", function () {
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
            getParamList($(this), 'b');
        });
        $(selector + ' .dev-offer-category').on('click', ".Content", function () {
            var cid = $(this).parent().data('cid'),
                pid = $(this).parent().data('pid'),
                $holderOffer = $(this).parents('.dev-positions'),
                $holderCategory = $(this).parents('.dev-offer-category'),
                childCount = $(this).parent().data('childCount');
            $holderOffer.find(".previewPic img").attr('src', '../../images/preview.png');
            $holderOffer.find(".offerInfo").empty();
            if (childCount == 0) {
                $holderCategory.find(".Content.b").removeClass('b');
                $(this).addClass('b');
                $holderOffer.find(".noOffers").remove();
                $holderOffer.find(".offerHolder li").remove();
                getOfferList(cid, base.obj.shopId, $holderOffer);
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
            var data = {},
                $title = $('[name=widget_name]');
            $title.val($title.val().trim());
            base.status = 'ok';
            if (!$title.val()) {
                toastr.error('Введите название виджета что бы продолжить');
                $title.focus();
                return;
            }
            var positions = base.getPositions();

//            if (base.status == 'error_rule'){
//                toastr.error('Не выбрано правило');
//                return;
//            }
            if ($(':hidden[name="type_id"]').val() == 3) {//free
                data = {
                    'shopId': base.obj.shopId,
                    'skinId': $('[name=skin_id]').val(),
                    'typeId': $('[name=type_id]').val(),
                    'positions': positions,
                    'widgetId': $('[name=widget_id]').val(),
                    'title': $title.val()
                }
            }
            else {
                data = {
                    'shopId': base.obj.shopId,
                    'skinId': $('[name=skin_id]').val(),
                    'typeId': $('[name=type_id]').val(),
                    'commonRule': base.getCommonRule(),
                    'positions': positions,
                    'widgetId': $('[name=widget_id]').val(),
                    'title': $title.val()
                };
            }
            api.call('setWidget', data, function (response) {
                toastr.info('Виджет сохранен, id = ' + response.widgetId);
                widgetId = response.widgetId;
            });

        });
        $(selector + ' .paramsHolder').on('click', '.paramContainer .param', function () {
            var paramName = $(this).data('param-name');
            var paramValue = $(this).data('param-value');
            $(this).toggleClass('active');
            if($(this).hasClass('active')){
                if (selectedParams[paramName] == undefined){
                    selectedParams[paramName] = [];
                }
                selectedParams[paramName].push(paramValue);
            }
            else{
                var ind = selectedParams[paramName].indexOf(paramValue);
                if(ind != -1){
                    selectedParams[paramName].splice (ind, 1);
                }
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
        $('.upProduct').on('click', function(e){
            e.preventDefault();
            var position = parseInt($(this).attr('data-position'), 10);
            var nextPosition = -1;
            $('.upProduct').each(function(){
                var pos = parseInt($(this).attr('data-position'), 10);
                if (pos < position && pos > nextPosition){
                    nextPosition = pos;
                }
            });
            var next = $('.dev-block-' + nextPosition).parent();
            if (nextPosition != -1 && next.length > 0){
                var current = $('.dev-block-' + position).parent().clone(true);
                $('.dev-block-' + position).parent().remove();
                current = base.changePositionHtml(current, nextPosition, position);
                next = base.changePositionHtml(next, position, nextPosition);
                current.insertBefore(next);
                $(document).scrollTop($('.dev-block-' + nextPosition).position().top);
            }
        });
        $('.downProduct').on('click', function(e){
            e.preventDefault();
            var position = parseInt($(this).attr('data-position'), 10);
            var prevPosition = 16;
            $('.downProduct').each(function(){
                var pos = parseInt($(this).attr('data-position'), 10);
                if (pos > position && prevPosition > pos){
                    prevPosition = pos;
                }
            });
            var prev = $('.dev-block-' + prevPosition).parent();
            if (prevPosition != position && prev.length > 0){
                var current = $('.dev-block-' + position).parent().clone(true);
                $('.dev-block-' + position).parent().remove();

                current = base.changePositionHtml(current, prevPosition, position);
                prev = base.changePositionHtml(prev, position, prevPosition);
                current.insertAfter(prev);
                $(document).scrollTop($('.dev-block-' + prevPosition).position().top);
            }
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
    changePositionHtml: function(item, newPosition, prevPosition){
        item.find('.dev-positions').removeClass('dev-block-' + prevPosition);
        item.find('.dev-positions').addClass('dev-block-' + newPosition);
        item.find('[name="item_position"]').val(newPosition);
        item.find('.removeProduct').attr('data-position', newPosition);
        item.find('.upProduct').attr('data-position', newPosition);
        item.find('.downProduct').attr('data-position', newPosition);
        return item;
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
        $holder.find('.upProduct').attr('data-position', position);
        $holder.find('.downProduct').attr('data-position', position);
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
                if (this.count >= freeWidgetPositions) {
                    $('.addBlockContent').removeClass('active');
                }
                return true;
        }
    },
    getPositions: function () {
        var positions = [],
            base = this;
        $('.dev-positions').each(function (i) {
            var position = $(this).find('[name="item_position"]').val(),
                $type = $(this).find('[name="rule_type"]'),
                widgetType = $(':hidden[name="type_id"]').val();
                if (position){
                    positions[i] = [];
                    $type.each(function(){
                        var source = base.getSource(position, $(this).val()),
                            count = 0;
                        if (widgetType == 3 && $(this).val() == 1){
                            for (var j in source){
                                if (source[j].length == 0){
                                    count++;
                                }
                            }
                            if (count == base.filters.length){
                                base.status = 'error_rule';
                                source = [];
                            }
                        }
                        if (source.length > 0 || (source.param != undefined || source.categoryId !=undefined)){
                            positions[i].push({type: $(this).val(), params: source});
                        }
                    });
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
            if (offer) {
                return {offerId: offer['attributes']['id'], categoryId: offer.categoryId};
            }
        }
        return params;
    },
    getRule: function (selector) {
        var param = $(selector).find('.paramContainer .param.active'),
            categories = $(selector).find('.dev-category-rule li .Content.b'),
            paramValue = {},
            categoriesValue = [],
            params = {},
            paramExist = false;
        param.each(function () {
            if (paramValue[$(this).data('param-name')] == undefined){
                paramValue[$(this).data('param-name')] = [];
            }
            paramValue[$(this).data('param-name')].push($(this).data('param-value'));
            paramExist =true;
        });
        categories.each(function () {
            categoriesValue.push($(this).parent().data('cid'));
        });
        if (paramExist) {
            params.param = paramValue;
        }
        ;
        if (categoriesValue.length>0) {
            params.categoryId = categoriesValue;
        }

        return params;
    }
}