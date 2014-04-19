function _offer(){

	this.id = 0;
	this.type = "";
	this.available = "";

	this.categoryId = 0;
	this.currencyId = 0; 
	this.delivery = "";
	this.model = "";
	this.oldprice = 0; 
	this.params = {};
	this.picture = "";
	this.price = "";
	this.url = "";
	this.vendor = "";
	this.vendorCode = ""

}

function constructOffer(data){
	var offer = new _offer();
	offer.id = data.attributes.id;
	offer.type = data.attributes.type;
	offer.available = data.attributes.available;

	offer.categoryId = data.categoryId;
	offer.currencyId = data.currencyId; 
	offer.delivery = data.delivery;
	offer.model = data.model;
	offer.oldprice = data.oldPrice; 
	offer.params = data.params;
	offer.picture = data.picture;
	offer.price = data.price;
	offer.url = data.url;
	offer.vendor = data.vendor;
	offer.vendorCode = data.vendorCode;

	return offer;		
}

function getOfferList(cid, shopId, $holder, currItem){
	
	api.call('getOfferList', {'shopId': shopId, 'categoryId' : [cid]}, function(response){	
		if(response.list.length == 0){
            $holder.find(".offerHolder").empty();
			$holder.find(".offerHolder").before('<div class="noOffers">товаров нет</div>');
			return;
		}
		var offers = [];
		
		for(var i in response.list){
			var item = response.list[i];
			var offer = $.parseJSON(item);
			offers.push(offer);
		}
		buildOfferList(offers, $holder, currItem);

	});
}

function buildOfferList(offers, $holder, currItem){
	var $ul = $holder.find("ol.offerHolder");
    $ul.empty();
	for(var i in offers){

		var $li = $('<li class="offerItem">'+ offers[i].offer +'</li>');
		$li.data('offer', offers[i]);
        if(offers[i].attributes.id == currItem){
            $li.addClass('active');
        }
        if (offers[i].isAvailable == 0){
            $li.addClass('notAvailable');
        }
		$ul.append($li);
	}
	$holder.find('.offerItem').unbind('click');
	$holder.find('.offerItem').bind('click', function(){
		$holder.find('.offerItem').removeClass('active');
		$(this).addClass('active');

		var offer = $(this).data('offer');
		$holder.find('.previewPic img').attr('src', offer.picture);
		var $info = $("<div></div>")
		var isAvailable = (offer.isAvailable=='1')?'Да':'Нет на складе';
		$info.append("<div>"+offer.title+"</div>")
		$info.append("<div>Цена: <span class='b'>"+offer.price+"</span></div>")
		$info.append("<div>Доступно: "+isAvailable+"</div>")
        var offerParamText = '';
        for (var i in offer.param){
            offerParamText += '&nbsp;&nbsp;&nbsp;' + offer.param[i].title + ': ' + offer.param[i].value + '</br>';
        }
		$info.append("<div>Параметры: "+offerParamText+"</div>")
		$info.append("<div>ID: " + offer.attributes.id + "</div>");
        $info.append("<div>Категория: " + offer.title + "</div>");
        if (offer.vendorCode != undefined){
            $info.append("<div>CODE: "+offer.vendorCode+"</div>")
        }
		$holder.find(".offerInfo").empty();
		$holder.find(".offerInfo").append($info);
        $holder.data('cid', offer.categoryId);
	});
}