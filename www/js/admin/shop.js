function _shop(data){
	this.id = data.id;
	this.title = data.title;
	this.url = data.url;
	this.$shop = '';
	this.widgets = [];
	
	//var id, title, url;
	var self = this;
	
	var $categoryTpl;
	var $categoryOfferTpl;
	var $rule;
	var $position;
	var $freePosition;
	var $smallWidget;
	var $bigWidget;
	var $freeWidget;
	var $preparedWidget;
	
	var posNum = 1;
	var selectedOffers = [];
	var selectedCategories = [];
	var selectedColors = [];
	var positions = [];            
	var widgetType = 0;
	this.addPosition = function(position){
		positions.push(position);
		console.log(positions);
	}
	this.getWidgetType = function(){
		return $shop.find(".widgetTypeList").val();
	}
	
	this.getSkinType = function(){
		return $shop.find(".widgetSkinList").val();
	}
	this.getPositions = function(){
		var params = [];
		for(var i in selectedOffers){
			var offer = selectedOffers[i];
			var tpl = {'type': '2', 'params': [offer.attributes.id]}
			params.push(tpl);
		}
		return params;
	} 
	
	this.getCommonRule = function(){
		if(this.getWidgetType == 3){
			return null;
		}
		var rule = {};
		rule.color = selectedColors;
		rule.categoryId = selectedCategories;
		
		return rule;
	}
	this.initEvents = function(){
		$shop.on('click', ".generateWidgetPreview", function(e){
			e.preventDefault();
			
			
			var idArray = selectedOffers.map(function(item){return item.attributes.id});
			if(idArray.length == 0){
				toastr.error('Необходимо выбрать товар');
				return;
			}
			var host = "http://146.185.169.28/test.loc/www/?widget_id=";
			host += idArray.join(',');

			
			var iframe = "<iframe src=\""+host+" \" width=\"100%\" height=\"600px\"></iframe>";
			$shop.find(".widgetPreview").find('iframe').slideUp(function(){$(this).remove});
			$shop.find(".widgetPreview").append(iframe);
			
			$wInfo = $shop.find('.widgetInfo');                                 	
	        $wInfo.find(".desc.hidden").removeClass('hidden');
			$wInfo.find(".widgetUrl").val(host);
			$wInfo.find(".widgetType").html($(".widgetTypeList option:selected").html());
			$wInfo.find(".widgetSkin").html($(".widgetSkinList option:selected").html());
			$wInfo.find(".widgetCount").html(selectedOffers.length);
		});
		$shop.on('click', ".saveWidget", function(e){
			e.preventDefault();
			var data = {};
			if(widgetType == 3){//free
				data = {
					'shopId': self.id,
					'skinId': self.getSkinType(),
					'typeId': self.getWidgetType(),
					'positions': positions
				}						
			}
			else{
				data = {
					'shopId': self.id,
					'skinId': self.getSkinType(),
					'typeId': self.getWidgetType(),
					'commonRule': self.getCommonRule(),
					'positions': self.getPositions()
				};	
			}
			
			console.log(data);
			api.call('setWidget', data, function(response){
				console.log(response);
				toastr.info('Виджет сохранен, id = ' + response.widgetId);
			});
			
		});
		$shop.on('click', ".addProduct ", function(e){
			e.preventDefault();
			var offer = $('.offerItem.active').data('offer');
			selectedOffers.push(offer);
			console.log(selectedOffers);
			toastr.info('Товар выбран');
		})
		$shop.on('click', ".categoryHolder .Content", function(){

			var cid = $(this).parent().data('cid'),
			pid = $(this).parent().data('pid');

			var childs = $(this).data('childs');
			
				
			
            $(this).toggleClass('active')
            if($(this).hasClass('active')){
				if(childs != undefined && childs.length > 0){
					var i = childs.length;
					while(i--){
						selectedCategories.push(childs[i]);
					}
				}
				else
					selectedCategories.push(cid);
            }
            else{
            	if(childs != undefined && childs.length > 0){
            		var i = childs.length;
					while(i--){
						var ind = selectedCategories.indexOf(childs[i]);
						selectedCategories.splice(ind, 1);
					}	
            	}
            	else{
					var ind = selectedCategories.indexOf(cid);
					if(ind != -1)
						selectedCategories.splice(ind, 1);
				}
					
            }
            
			//if(pid != 0){
//			}
//			else{
//				$(this).prev().trigger('click');
//				var event = {};
//				event.target = $(this).prev()[0];
//				tree_toggle(event);
//			}
		});
		$shop.on('click', ".categoryOfferHolder .Content", function(){

			var cid = $(this).parent().data('cid');
			var pid = $(this).parent().data('pid');
            var $holder = $(this).parents('.categoryOfferHolder');
			$holder.find(".previewPic img").attr('src', '../images/preview.png');
			$holder.find(".offerInfo").empty();

			if(pid != 0){
				$holder.find(".Content").removeClass('b');
				$(this).addClass('b');
				$holder.find(".noOffers").remove();
				$holder.find(".offerHolder li").remove();
				getOfferList(cid, self.id, $holder);
			}
			else{
				$(this).prev().trigger('click');
				var event = {};
				event.target = $(this).prev()[0];
				tree_toggle(event);
			}

		});
		$shop.on('click', '.colorHolder .color', function(){
			$(this).toggleClass('active');
			if($(this).hasClass('active')){
				selectedColors.push($(this).data('colorName'));
			}
			else{
				var ind = selectedColors.indexOf($(this).data('colorName'));
				if(ind != -1)
					selectedColors.slice(ind, 1);
			}
		})
		$shop.on('click', ".createRule",function(e){
			e.preventDefault();
			$(this).parent().find(".ruleHolder").slideToggle();
		});
		$shop.on('click', ".choseProduct",function(e){
			e.preventDefault();
			$(this).parent().find('.categoryOfferHolder').slideToggle();
		});
		$shop.on('click', ".addPosition", function(e){
			e.preventDefault();
			var $newPos = $freePosition.clone(true);
			$newPos.find('.positionNum').html(posNum++);
			
			$(this).prev().append($newPos);
		})
		$shop.on('click', ".savePosition", function(e){
			
			var $p = $(this).parent().parent();
			var $offer = $p.find('.categoryOfferHolder').find('.offerItem.active');
			var offerId = '';
			if($offer.length > 0){
				offerId = $offer.data('offer').attributes.id;
				
				var position = {'type': 2, 'params': [offerId]};
				self.addPosition(position);
			}
			else{
				var $categories = $p.find('.ruleHolder .tree .Content.active');//cid
				var $colors = $p.find('.ruleHolder .colorHolder .color.active');
				var catIds = [];
				var colors = [];
				$categories.each(function(){
					var childs = $(this).data('childs');
					if(childs.length > 0){
						var i = childs.length;
						while(i--){
							catIds.push(childs[i]);
						}
						console.log(catIds);
					}
					else
						catIds.push($(this).parent().data('cid'));
				});
				
				$colors.each(function(){
					colors.push($(this).data('colorName'));
				});
				
				if($categories.length == 0){
					toastr.error('необходимо выбрать правило');
					return;
				}
				console.log(catIds);
				console.log(colors);
				var params = {};
				if(catIds.length > 0){
					params['categoryId'] = catIds;
				}
				if(colors.length > 0){
					params['color'] = colors;
				}
				
				var position = {'type': 1, 'params': params};
				
				self.addPosition(position);
			}
			
			$p.find('.ruleHolder, .categoryOfferHolder').slideUp();
			
			$(this).hide();
			$(this).next().removeClass('hidden');
			toastr.info("Позиция сохранена");
		});
	}
	
	this.initTemplates = function(list){
		var $tree = buildTree('myTree', list);
//			$(".treeTpl").append($tree);
			
			$categoryTpl = $('.categoryTpl').clone(true).removeClass('categoryTpl');//.hide();
			$categoryTpl.find('.treeHolder').append($tree.clone(true));
			//$(".holder").append($categoryTpl);
			
			$categoryOfferTpl = $('.categoryOfferTpl').clone(true).removeClass('categoryOfferTpl');//.hide();
			$categoryOfferTpl.find('.treeHolder').append($tree.clone(true));
			
			$colorTpl = $(".colorTpl").clone(true).removeClass('colorTpl');
			
			$rule = $(".ruleTpl").clone(true).removeClass('ruleTpl');
			$rule.find('.categoryHolder').append($categoryTpl.clone(true));
			$rule.find('.colorHolder').append($colorTpl.clone(true));
			
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
			//$freeWidget.find('.positionHolder').append($freePosition.clone(true));
			
			
			var $curWidget;
			$shop.find('.prepareWidget').click(function(){
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
			});	
			this.initEvents();
	}
	this.prepareCategories = function(){
		getCategoryList(self.id, function(response){
			var list = buildCategoryList(response);
			
			self.initTemplates(list);
			
		});
		
		
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
		
		//self.getWidgetList();
		
		$shop.find('a.add-widget').bind('click', function(e){
			var $w = $('.newWidgetTpl').clone().removeClass('newWidgetTpl');
			$shop.find('.widgets').after($w);
			$shop.find('.widget-list').append('<li class="lastWidget"><a href="#">новый виджет</a></a>');
			$(".widgetTypeList").chosen(chosenOpts)//.change(function (evt) {
//				console.log(123123123);
//				var html = $(this).find('option:selected').html();
//				console.log(html);
//      			
//      		});
			$(".widgetSkinList").chosen(chosenOpts);
			
			var widget = new _widget($w, self);
			self.widgets.push(widget);
			self.prepareCategories();
			
			
			$(this).unbind('click');
			
			
		});
		
		
		
		
    }

	this.getWidgetList= function(){
		$shop = self.$shop;
		api.call('getWidgetList', {'shopId': self.id}, function(response){
			console.log(response);
			//for(var i in response.list){
//				var w = response.list[i];//widget
//				var id = i;
//				var $w = $('.newWidgetTpl').clone().removeClass('newWidgetTpl').addClass('hidden').addClass('widget'+id);
//				$shop.find('.widgets').after($w);
//				$shop.find('.widget-list').append('<li><a href="#" data-id="'+id+'">Виджет '+id+'</a></a>');
//				$(".widgetTypeList").chosen(chosenOpts);
//				$(".widgetSkinList").chosen(chosenOpts);
//				
//				var widget = new _widget($w, self);
//				
//				getCategoryList(self.id, widget);
//				
//			}
//			$(".widget-list li a").bind('click', function(e){
//				e.preventDefault();
//				
//				var id = $(this).data("id");
//				$shop.find(".new-widget").hide();
//				$shop.find(".widget" + id).show();
//				
//			});
		});
		
	}
	
    this.init();
}