var widgets = [];
var shops = [];
var offers = [];
var categories = [];
var widgetTypes = [];
var widgetSkins = [];
var api;
var serverHost = '/handler'; //for server use	

var _offers = [];
var _colorList = [];
var selectedColors = [];


$(function(){  


	api = new _api(serverHost);

	api.call('getShopList', {}, function(response){
		for(var i in response.list){
			shops.push(new _shop(response.list[i]));
		}
	});

	api.call('getWidgetTypeList', {}, function(response){
		for(var i in response.list){
			widgetTypes.push(new _widgetType(response.list[i]));
		}
		
	});
	
	api.call('getWidgetSkinList', {}, function(response){
		for(var i in response.list){
			widgetSkins.push(new _widgetSkin(response.list[i]));
		}
		
	});
	
	api.call('getColorList', {}, function(response){
		_colorList = response.list;
		$(".colorTpl").append(buildColorList());
		
	})

	//$(".createRule").click(function(e){
//			e.preventDefault();
//			toastr.info('Are you the 6 fingered man?')
			//Toast.message('Не подключено');
//	});
	
	

});










