var widgets = [],
	shops = [],
	offers = [],
	categories = [],
	widgetTypes = [],
	widgetSkins = [],
	api,
	serverHostApi = '/handler', //for server use
	preview
	_offers = [],
	_colorList = [],
	selectedColors = [],
	previewPath = '',
    serverHost = '';
    widgetId = '';
    freeWidgetPositions = 15;


function mainInit(shopObj){
    serverHostApi = shopObj.hostServer + 'handler';
    serverHost = shopObj.hostServer;
	api = new _api(serverHostApi);

	api.call('getShopList', {}, function(response){
			//for(var i in response.list){
			//			shops.push(new _shop(response.list[i]));
			//		}
			shops.push(new _shop(shopObj.shop));

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

    $('.searchProduct').on('click', function(e){
        e.preventDefault();
        var offerId = $(this).parent().find('[name="offer_id"]').val(),
            shopId = shopObj.id,
            holder = $(this).parents('.dev-positions');
        api.call('getOffer', {'shopId': shopId, 'offerId': [offerId], 'allOffer': true}, function(response){
            if(response.list.length == 0){
                toastr.error('Такого товара не существует')
                return;
            }
            //if tree must closed uncomment this line
            //holder.find('.b').removeClass('b').parents('.IsRoot').removeClass('ExpandOpen').addClass('ExpandClosed');
            var offers = [];
            for(var i in response.list){
                var item = response.list[i];
                var offer = $.parseJSON(item);
                offers.push(offer);
            }
            buildOfferList(offers, holder);
        });
    })
}








