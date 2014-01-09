var widgets = [],
	shops = [],
	offers = [],
	categories = [],
	widgetTypes = [],
	widgetSkins = [],
	api,
	serverHost = '/handler', //for server use	
	preview
	_offers = [],
	_colorList = [],
	selectedColors = [],
	previewPath = '';


function mainInit(shopObj){
	api = new _api(serverHost);

	api.call('getShopList', {}, function(response){
			//for(var i in response.list){
			//			shops.push(new _shop(response.list[i]));
			//		}
			shops.push(new _shop(shopObj));

	});
	
	previewPath = $('.previewPic img').attr('src');

	api.call('getWidgetTypeList', {}, function(response){
			for(var i in response.list){
				widgetTypes.push(new _widgetType(response.list[i]));
			}
			api.call('getWidgetSkinList', {}, function(response){
					for(var i in response.list){
						widgetSkins.push(new _widgetSkin(response.list[i]));
					}
					$(".add-widget").trigger('click');
			});
	});



	api.call('getColorList', {}, function(response){
			_colorList = response.list;
			$(".colorTpl").append(buildColorList());

	})
}








