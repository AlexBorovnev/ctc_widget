function _widget($w, shop){
	var self = this;
	this.shop = shop;
	this.$w = $w;
	//	this.ids = [];
	this.offers = [];
	this.selectedOffers = [];
	this.positions = [];
	
	
	
	
	this.generateWidgetPreview = function(){
		
		//if($(".widgetTypeList").val() != 3){
//			toastr.error('Создание больших и маленьких виджетов временно не работает');
//			return;
//		}
		
		var idArray = this.selectedOffers.map(function(item){return item.attributes.id});
		if(idArray.length == 0){
			toastr.error('Необходимо выбрать товар');
			return;
		}
		var host = "http://146.185.169.28/test.loc/www/?widget_id=";
		host += idArray.join(',');

		
		var iframe = "<iframe src=\""+host+" \" width=\"100%\" height=\"600px\"></iframe>";
		$w.find(".widgetPreview").find('iframe').remove();
		$w.find(".widgetPreview").append(iframe);
		
		$w.find(".saveWidget").removeClass('hidden')
		
		$wInfo = $w.find('.widgetInfo');                                 	
        $wInfo.find(".desc.hidden").removeClass('hidden');
		$wInfo.find(".widgetUrl").val(host);
		$wInfo.find(".widgetType").html($(".widgetTypeList option:selected").html());
		$wInfo.find(".widgetSkin").html($(".widgetSkinList option:selected").html());
		$wInfo.find(".widgetCount").html(self.selectedOffers.length);
		
        
	} 
	this.addProduct = function(){
		if($w.find(".offerItem.active").length == 0){
			toastr.error('необходимо выбрать товар');
			return;
		}
		var $btn = $w.find('.addProduct');

		var offer = $w.find(".offerItem.active").data('offer');
		self.selectedOffers.push(offer);

		
		toastr.info('Товар выбран');
	}
	
	this.saveWidget = function(){
		var data = {
			'shopId': self.shop.id,
			'skinId': self.getSkinType(),
			'typeId': self.getWidgetType(),
			'commonRuler': self.getCommonRule(),
			'positions': self.getPositions()
		};
		
		api.call('setWidget', data, function(response){
			console.log(response);
			toastr.info('Виджет сохранен, id = ' + response.widgetId);
			self.shop.$shop.find('.lastWidget').removeClass('lastWidget').find('a').html('Виджет ' + response.widgetId);
		});
	}
	
	this.init = function(){
		
	
		
		
		
		
		//$w.find('.prepareWidget').bind('click', function(e){
//			e.preventDefault();
//			
//			var type = $w.find(".widgetTypeList").val();
//			var $widgetTpl = '';
//			switch(type){
//				case "1":
//					$widgetTpl = $('.smallWidgetTpl').clone(true).removeClass('.smallWidgetTpl');
//				break;
//				case "2":
//					$widgetTpl = $('.bigWidgetTpl').clone(true).removeClass('.bigWidgetTpl');
//				break;
//				case "3":
//					$widgetTpl = $('.freeWidgetTpl').clone(true).removeClass('.freeWidgetTpl');
//				break;
//			}
//			console.log($widgetTpl);
			//$widgetTpl.find('.choseProduct').bind('click',  function(e){
//				e.preventDefault();
//				$(this).next().slideDown();
//			})
//			
//			$w.find('.widgetHolder').append($widgetTpl);
//			
			//$(this).unbind('click');
//			$w.find('.createRule').bind('click', function(e){
//				e.preventDefault();
//				
//				$(this).next().slideDown();
//				
//			})	
//		})    
		
	}
	
	this.init();
}

function _smallWidget($w, shop){
	
}
_smallWidget.prototype = _widget;

function _bigWidget($w, shop){
	
}
_bigWidget.prototype = _widget;
function _freeWidget($w, shop){
	
}
_freeWidget.prototype = _widget;
