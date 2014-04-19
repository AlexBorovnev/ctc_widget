function State(){
	var self = this;
	this.isRuleSelected = false;
	this.isOfferSelected = false;
	
	this.isRuleValid = false;
	this.isOfferValid = false;
	
	this.isRuleSaved = false;
	this.isOfferSaved = false;
	this.hasPosition = false;
}
var shopObject;

function _shop(data){
	this.id = data.id;
	this.title = data.title;
	this.url = data.url;
	this.$shop = '';
	this.widgets = [];
	this.wTitle = '';
	//var id, title, url;
	var self = this,
	$categoryTpl,
	$categoryOfferTpl,
	$rule,
	$position,
	$freePosition,
	$smallWidget,
	$bigWidget,
	$freeWidget,
	$preparedWidget,

	posNum = 1,
    indexNum = 1,
	selectedOffers = [],
	selectedCategories = [],
    selectedParams = {},
	positions = [],
	widgetType = 0,
	widgetId = 0;

	this.addPosition = function(position){
		positions.push(position);
	}
	this.getWidgetType = function(){
		return $shop.find(".widgetTypeList").val();
	}

	this.getSkinType = function(){
		return $shop.find(".widgetSkinList").val();
	}
	this.getPositions = function(){
		var params = [];
		var selectedOffers = [];
		$shop.find('.offerItem.active').each(function(){
					selectedOffers.push($(this).data('offer'));
				})
		for(var i in selectedOffers){
			var offer = selectedOffers[i];
			var tpl = {'type': '2', 'params': {offerId: offer.attributes.id, categoryId: offer.categoryId}};
			params.push([tpl]);
		}
		return params;
	} 
	this.getFreePositions = function(){
		var $poses = $shop.find('.pos:visible');
		var l = $poses.length;
		var out = [];
		while(l--){
			var $p = $($poses[l]);
			var $offer = $p.find('.categoryOfferHolder').find('.offerItem.active');
				var offerId = '';
                var categoryId = '';
                var num = $p.attr('data-init-pos');
                var preparedPosition = [];
				if($offer.length > 0){
					offerId = $offer.data('offer').attributes.id;
                    categoryId = $offer.data('offer').categoryId;
					var position = {'type': 2, 'params': {offerId: offerId, categoryId: categoryId}, 'num': num};
					preparedPosition.push(position);

				}
				
				var $categories = $p.find('.ruleHolder .tree .Content.active');//cid
				var $params = $p.find('.ruleHolder .paramTpl .param.active');
				var catIds = [];
				var param = {};
                var paramExist = false;
				$categories.each(function(){
						catIds.push($(this).parent().data('cid'));
				});

                $params.each(function(){
                    if (param[$(this).data('param-name')] == undefined){
                        param[$(this).data('param-name')] = [];
                    }
                    param[$(this).data('param-name')].push($(this).data('param-value'));
                    paramExist = true;
				});


				var params = {},
                    rule = false;
				if(catIds.length > 0){
					params['categoryId'] = catIds;
                    rule = true;
				}
                if (paramExist){
                    params['param'] = param;
                    rule = true;
                }
                var position = {};
                if (rule){
                    position = {'type': 1, 'params': params, 'num': num};
                }
                preparedPosition.push(position);
                out.push(preparedPosition);
		}
		return out;	
	}

	this.getCommonRule = function(){
		if(this.getWidgetType == 3){
			return null;
		}
		var rule = {'param': [], 'categoryId': []};
		rule.categoryId = selectedCategories;
        rule.param = selectedParams;
		if(rule.param.length == 0 && rule.categoryId.length == 0)
			return [];
		return rule;
	}
	this.widgetPreview = function(){
		var host = serverHost + 'widget_id/' + widgetId;
		var iframe = "<iframe src=\""+host+" \" width=\"100%\" height=\"600px\"></iframe>";
		$shop.find(".widgetPreview").find('iframe').slideUp(function(){$(this).remove});
		$shop.find(".widgetPreview").append(iframe);

		$wInfo = $shop.find('.widgetInfo');                                     
		$wInfo.find(".desc.hidden").removeClass('hidden');
		$wInfo.find(".widgetUrl").val(host);
		$wInfo.find(".previewUrl").attr('href', host);
		$wInfo.find(".widgetType").html($(".widgetTypeList option:selected").html());
		$wInfo.find(".widgetSkin").html($(".widgetSkinList option:selected").html());
		$wInfo.find(".widgetCount").html(selectedOffers.length);
		$wInfo.find('.widgetTitle').html(self.wTitle);
	}
	this.initEvents = function(){
		$shop.on('click', '.changeOffer', function(e){
			e.preventDefault();
			
			var parent = $(this).parents('.pos');
			if(parent.length == 0)
				parent = $(this).parents('.widget');
			var previewOffer = parent.find('.selectedOffer');
			var holder = parent.find('.categoryOfferHolder');
			
			previewOffer.remove();
			holder.show();
		});
		
		$shop.on('click', '.changeRule', function(e){
			e.preventDefault();
			
			var parent = $(this).parents('.pos');
			if(parent.length == 0)
				parent = $(this).parents('.widget');
			var previewRule = parent.find('.selectedRule');
			var holder = parent.find('.ruleHolder .rule');
			
			
			var state = parent.data('state');
			state.isRuleSelected = true;
			parent.data('state', state);
			
			previewRule.remove();
			holder.show();
		});
		
		$shop.on('click', ".saveWidget", function(e){
				e.preventDefault();
				var data = {};
				if(widgetType == 3){//free
					var positions = self.getFreePositions();
					if(positions == undefined){
						return;
					}
					if(positions.length == 0)
					{
						toastr.error('Необходимо создать как минимум 1 позицию');
						return;
					}
                    
					data = {
						'shopId': self.id,
						'title' : self.wTitle,
						'skinId': self.getSkinType(),
						'typeId': self.getWidgetType(),
						'positions': positions
					}
				}
				else{
					data = {
						'shopId': self.id,
						'title' : self.wTitle,
						'skinId': self.getSkinType(),
						'typeId': self.getWidgetType(),
						'commonRule': self.getCommonRule(),
						'positions': self.getPositions()
					};

					if(data.title == ''){
						toastr.error('Укажите название виджета');
						return;
					}
				}
                if(widgetId){
                    data.widgetId = widgetId;
                }
				api.call('setWidget', data, function(response){
						toastr.info('Виджет сохранен, id = ' + response.widgetId);
						widgetId = response.widgetId;
						self.widgetPreview();
				});

		});
		
		$shop.on('click', ".addProduct", function(e){
				e.preventDefault();

				var $pos = $(this).parents('.pos');
				var state = $pos.data('state');
				
				var l = $(this).parents('.categoryOfferHolder').find('.offerItem.active').length;
				if(l == 0)
					toastr.error('Выберите товар');
				else
					toastr.info('Товар выбран');

				var $tpl = $(this).parents('.categoryOfferHolder');
				$tpl.hide();
				var $selectedOfferTpl = $(".selectedOfferTpl").clone(true).removeClass('selectedOfferTpl');
				
				var $preview = $(this).parent('.preview').clone(true);
				$preview.find('.btn').remove();
				$selectedOfferTpl.find('.forPreview').append($preview);
//                $(this).unbind('click');
				$tpl.parent().find('._preview').append($selectedOfferTpl);
				$(this).parents('.pos').find('.choseProduct').hide();
				$(this).parents('.pos').find('.savePosition').show();
				state.isOfferSaved = true;
				state.isOfferSelected = false;
				$pos.data('state', state);
		})
		
		$shop.on('click', ".categoryHolder .Content", function(){
                var $pos = $(this).parents('.pos');
                if($pos.length == 0)$pos = $(this).parents('.widget');
				var state = $pos.data('state');
				if(state == null) state = new State();
				 
				var cid = $(this).parent().data('cid'),
				pid = $(this).parent().data('pid');

				var childs = $(this).parent().data('childs');
				var domChilds = $(this).parent().children('.Container').find('.Content')

				$(this).toggleClass('active');
				if($(this).hasClass('active')){
					state.isRuleValid = true;
                    domChilds.addClass('active');
				}
				else{
                    $(this).parents('.Node').children('.Content').removeClass('active');
                    $(this).parent().find('.Content').removeClass('active');
				}
				selectedCategories = [];
				var $activeChilds = $(this).parents('.tree').find('.Content.active');
				var t = $activeChilds.length;
				while(t--){
					var $active = $($activeChilds[t]);
					var _cid = $active.parent().data('cid');
					selectedCategories.push(_cid);
				}

				selectedCategories = arrayUnique(selectedCategories);
                getParamList($(this), 'active');
				$pos.data('state', state);
		});
		
		$shop.on('click', ".categoryOfferHolder .Content", function(){

				var cid = $(this).parent().data('cid');
				var pid = $(this).parent().data('pid');
                var childCount = $(this).parent().data('childCount');
				var $holder = $(this).parents('.categoryOfferHolder');
                var parentNode = $(this).parent();
				$holder.find(".previewPic img").attr('src', previewPath);
				$holder.find(".offerInfo").empty();
				if(childCount == 0){
					$holder.find(".Content").removeClass('b');
					$(this).addClass('b');
					$holder.find(".noOffers").remove();
                    getOfferList(cid, self.id, $holder);
				}
				else{
					var event = {};
                    $holder.find(".offerHolder").empty();;
					event.target = $(this).prev()[0];
					tree_toggle(event, self.id);
				}
		});

		$shop.on('click', '.paramContainer .param', function(){
				var $pos = $(this).parents('.pos');
                var paramName = $(this).data('param-name');
                var paramValue = $(this).data('param-value');
                if($pos.length == 0)$pos = $(this).parents('.widget');
				var state = $pos.data('state');
				if(state == null) state = new State();
				$(this).toggleClass('active');
				if($(this).hasClass('active')){
					state.isRuleValid = true;
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
				$pos.data('state', state);
		})
		
		$shop.on('click', ".createRule", function(e){
				e.preventDefault();
				var $pos = $(this).parents('.pos');
				var state = $pos.data('state');
				if(state == null)
					state = new State();
				if(state.isOfferSelected){
					if(confirm("Сохранить выбранный товар?")){
						
						var $parent = $(this).parents('.pos');
						var $selOffer = $parent.find('.offerItem.active');
						if($selOffer.length > 0){//save
							$parent.find('.addProduct').trigger('click');
						}
						else{
							toastr.error('Выберите товар');
							return;
						}
					}
					else{
						state.isOfferSelected = false;
						var $parent = $(this).parents('.pos');
						var $but = $parent.find('.choseProduct');
						var css = $but.data('oldCss');
						$but.css(css);
						var $holder = $parent.find('.categoryOfferHolder');
						$holder.slideUp();

						$holder.find('.Content.b').removeClass('b');
						$holder.find('.ExpandOpen').removeClass('ExpandOpen').addClass('ExpandClosed');
						$holder.find('.offerHolder').empty();
						$holder.find(".previewPic img").attr('src', previewPath);
						$holder.find(".offerInfo").empty();
						
					}
				}
				if(state.isRuleSelected){
					return;
				}
				state.isRuleSelected = true;
				
				var $tpl = $(this).parent().find(".ruleHolder");
				var css = $(this).css(['background', 'background-color', 'color']);
				$(this).data('oldCss', css);
				
				$(this).css({'background': 'none', 'background-color': '#ff7676', 'color': "#000"});
				$tpl.slideDown();
				$pos.data('state', state);
				$(this).parent().find('.saveRule').show();
		});
		
		$shop.on('click', ".choseProduct", function(e){
				e.preventDefault();
				var $pos = $(this).parents('.pos');
				var state = $pos.data('state');
				if(state == null)
					state = new State();
				if(state.isRuleSelected){
					if(confirm("Сохранить выбранные правила ?")){
						if(!state.isRuleValid){
							toastr.error('Необходимо выбрать правило перед сохранением');
							return;
						}
						var $parent = $(this).parents('.pos');
						var $button = $parent.find('.saveRule');
						$button.trigger('click');	
						
					}
					else{
						//отмена 
						state.isRuleSelected = false;
						var $parent = $(this).parents('.pos');
						var $but = $parent.find('.createRule');
						var css = $but.data('oldCss');
						$but.css(css);
						var $holder = $parent.find('.ruleHolder');
						$holder.slideUp();
						$holder.find('.saveRule').hide();
						
						$holder.find('.Content.active').removeClass('active');
						$holder.find('.color.active').removeClass('active');
					}
				}
				if(state.isOfferSelected){
					return;
				}
				state.isOfferSelected = true;
				$pos.data('state', state);
				var $tpl = $(this).parent().find('.categoryOfferHolder');
				var css = $(this).css(['background', 'background-color', 'color']);
				$(this).data('oldCss', css);
				$(this).css({'background': 'none', 'background-color': '#ff7676', 'color': "#000"});

				$tpl.slideDown();
				//$(this).off('click');
				
		});
		
		$shop.on('click', ".addPosition", function(e){
				e.preventDefault();

				if(posNum > freeWidgetPositions){
					toastr.error('Максимальное количество позиций - ' + freeWidgetPositions);
					return;
				}
				var $newPos = $freePosition.clone(true);
                $newPos.find('.removePosition').attr('data-position-num', posNum);
                $newPos.attr('data-init-pos', indexNum);
                $newPos.find('.positionNum').html(posNum++);
				$newPos.find('.savePosition').hide();
				$newPos.find('.saveRule').hide();
                indexNum++;
                var state = new State();
                
                $newPos.data('state', state);
                
				$(this).prev().append($newPos);
		})
		
        $shop.on('click', '.removePosition', function(e){
            e.preventDefault();
            if (confirm('Удалить позицию?')){
                var removePosition = parseInt($(this).attr('data-position-num'), 10);
                var indexPosition = parseInt($(this).parents('.pos').attr('data-init-pos'), 10);
                $(this).parents('.pos').remove();
                for (var i in positions){
                    if (positions[i].num == indexPosition){
                        positions.splice(i, 1);
                    }
                }
                $('.positionNum').each(function(){
                    var changePosition = parseInt($(this).html(), 10);
                    if (changePosition > removePosition){
                        $(this).html(changePosition -1);
                        $('.removePosition[data-position-num="'+ changePosition +'"]').attr('data-position-num', changePosition -1);
                    }
                })
                posNum--;
            }

        });
        
		$shop.on('click', '.saveRule', function(e){
				e.preventDefault();
				var $selectedRule = $(".selectedRuleTpl").clone(true).removeClass('selectedRuleTpl');
				var $tpl = $(this).parents('.rule');
				var $pos = $(this).parents('.pos');
                if($pos.length == 0)$pos = $(this).parents('.widget');
				var state = $pos.data('state');
				if(state == null) state = new State();
				
				var cats = [];
				var params = [];
				$tpl.find('.param.active').each(function(){
                    var paramName = $(this).data('param-name');
                    var paramVlaue = $(this).data('param-value');
                    if (params[paramName] != undefined){
                        params[paramName].push(paramVlaue);
                    } else {
                        params[paramName] = [];
                        params[paramName].push(paramVlaue);
                    }
				});

				$tpl.find('.Content.active').each(function(){
						cats.push($(this).html());
				});
				if(cats.length === 0 && params.length === 0){
					toastr.error('Укажите правило');
					return;
				}
				state.isRuleValid = true;
				state.isRuleSelected = false;
				$pos.data('state', state);
				$tpl.hide();
				
				$selectedRule.find('.ruleCategories').html(cats.join(', '));
                var paramHtml = '';
                for (var i in params){
                    paramHtml += i + ': ' + params[i].join(', ') + '</br>';
                }
				$selectedRule.find('.ruleColors').html(paramHtml);
				$(this).parents('.pos').find('._preview').append($selectedRule);
				$(this).parents('.widget').find('.rule_preview').append($selectedRule);
				$(this).parents('.pos').find('.savePosition').show();
				var $but = $(this).parents('.pos').find('.createRule');
				if($but.length == 0)
					$but = $(this).parents('.widget').find('.createRule');
				$but.hide();
		});
		
		$shop.on('click', ".savePosition", function(e){
            	e.preventDefault();
				
		});
	}       
    
	this.initTemplates = function(list){
		var $tree = buildTree('myTree', list, self.id);
		//            $(".treeTpl").append($tree);

		$categoryTpl = $('.categoryTpl').clone(true).removeClass('categoryTpl');//.hide();
		$categoryTpl.find('.treeHolder').append($tree.clone(true));
		//$(".holder").append($categoryTpl);

		$categoryOfferTpl = $('.categoryOfferTpl').clone(true).removeClass('categoryOfferTpl');//.hide();
		$categoryOfferTpl.find('.treeHolder').append($tree.clone(true));

		$paramsTpl = $(".paramTpl").clone(true).removeClass('paramTpl');

		$rule = $(".ruleTpl").clone(true).removeClass('ruleTpl');
		$rule.find('.categoryHolder').append($categoryTpl.clone(true));
		$rule.find('.paramsHolder').append($paramsTpl.clone(true));

		$position = $('.positionTpl').clone(true).removeClass('positionTpl')
		$position.find('.categoryOfferHolder').append($categoryOfferTpl.clone(true));

		$freePosition = $('.freePositionTpl').clone(true).removeClass('freePositionTpl');
		$freePosition.find('.categoryOfferHolder').append($categoryOfferTpl.clone(true));
		$freePosition.find('.ruleHolder').append($rule.clone(true));

		$smallWidget = $(".smallWidgetTpl").clone(true).removeClass('smallWidgetTpl');
		$pos = $position.clone(true);
		$pos.find('.positionNum').html(1);
		$smallWidget.find(".positionHolder").append($pos);
		$smallWidget.find('.ruleHolder').append($rule.clone(true));

		$bigWidget = $(".bigWidgetTpl").clone(true).removeClass('bigWidgetTpl');
		$pos1 = $position.clone(true);
		$pos1.find('.positionNum').html(1)
		$bigWidget.find(".positionHolder1").append($pos1);
		$pos2 = $position.clone(true);
		$pos2.find('.positionNum').html(2)
		$bigWidget.find(".positionHolder2").append($pos2);
		$bigWidget.find('.ruleHolder').append($rule.clone(true));

		$freeWidget = $('.freeWidgetTpl').clone(true).removeClass('freeWidgetTpl');


		var $curWidget;
		$shop.find('.prepareWidget').click(function(){
				var $title = $shop.find('#widgetTitle');
				var titleVal = $title.val().trim();
				$title.val(titleVal);
				if(titleVal.length == 0){
					toastr.error('Введите название виджета что бы продолжить');
					$title.focus();
					return;
				}
				if(titleVal.length > 40){
					toastr.error('Название виджета слишком длинное, макс. 40 символов');
					$title.focus();
					return;
				}
				self.wTitle = titleVal;
				var type = $shop.find(".widgetTypeList").val();
				widgetType = type;
				if(type == 1){//small
					$curWidget = $smallWidget;
				}
				else if(type == 2){//big
					$curWidget = $bigWidget;
				}
				else if(type == 3){//free
					$curWidget = $freeWidget;
				}
				$shop.find('.widgetHolder').append($curWidget.clone(true));
				$shop.find('.preparedWidget').show();
				$(this).hide();

				$title.hide().after($title.val());

				var $skinList = $shop.find(".widgetSkinList"),
				$typeList = $shop.find('.widgetTypeList'),
				skinVal = $skinList.find('option:selected').html(),
				typeVal = $typeList.find('option:selected').html();
				$skinList.next().hide().after('<span>'+skinVal+'</span>');
				$typeList.next().hide().after('<span>'+typeVal+'</span>');

		});
		this.initEvents();
	}
	this.prepareCategories = function(){
		getCategoryList(self.id, function(response){
				var list = buildCategoryList(response);
				self.initTemplates(list);
		}, 0);

	}
    this.renderCategoryTree = function(list, nodeElement){
        var catTree = buildTree('myTree', list, self.id, nodeElement);
        nodeElement.data('download', true);
    }

	this.init = function(){

		$("#tabs ul.tabList").append('<li><a href="#shop'+this.id+'">'+this.title+'</a></li>');
		$( "#tabs" ).tabs().addClass( "ui-tabs-vertical ui-helper-clearfix" );
		$( "#tabs li" ).removeClass( "ui-corner-top" ).addClass( "ui-corner-left" ).addClass( "ui-corner-right" );
		var $shopTpl = $('.tabTpl').clone().removeClass('tabTpl').attr('id', 'shop'+this.id);
		//$tabTpl.find('.new_widget').hide();
		$("#tabs").append($shopTpl);
		self.$shop = $('#shop'+this.id);
		$shop = self.$shop;

		$shop.find('a.add-widget').bind('click', function(e){
				var $w = $('.newWidgetTpl').clone().removeClass('newWidgetTpl');
				$shop.find('.widgets').after($w);
				$shop.find('.widget-list').append('<li class="lastWidget"><a href="#">новый виджет</a></a>');
				$(".widgetTypeList").chosen(chosenOpts)//.change(function (evt) {
				$(".widgetSkinList").chosen(chosenOpts);

				var widget = new _widget($w, self);
				self.widgets.push(widget);
				self.prepareCategories();


				$(this).unbind('click');
		});
        shopObject = self;
	}

	this.getWidgetList= function(){
		$shop = self.$shop;
		api.call('getWidgetList', {'shopId': self.id}, function(response){

		});
	}

	this.init();
}


