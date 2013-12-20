
var widgets = [];
var shops = [];
var offers = [];
var categories = [];
var api;

var _offers = [];

var shopId = 11;

$(function(){  
	$( "#tabs" ).tabs().addClass( "ui-tabs-vertical ui-helper-clearfix" );
    $( "#tabs li" ).removeClass( "ui-corner-top" ).addClass( "ui-corner-left" );
    $( "input[type=button], input[type=submit], button, a.btn" ).button().find('span').css('font-weight', 'bold');
    $(".slider ul").carouFredSel({
    	//todo: disable left right buttons if first last element
        responsive: true,
        circular: false,
        infinite: false,
        auto 	: false,
        items: 2,
        scroll : {
            items           : 1,
           
            duration        : 250,                         
            pauseOnHover    : true
        },        
        prev	: {
            button	: ".arrow-left",
            key		: "left"
        },
        next	: {
            button	: ".arrow-right",
            key		: "right"
        }
    });
    $("select").chosen({disable_search_threshold: 10, width: "150px"});
    $( ".check" ).button();
    
    api = new _api('../handler');
    
    //api.call('getOfferList', {'data': 1, 'params': 2}, function(response){
//    	console.log(response);
//		for(var i in response){
//			var item = response[i];
//			offers.push(constructOffer(item));
//		}
//		
//		console.log(offers);
//    });
    
    var widget = new _widget();
    
    $(".generateWidgetPreview").click(function(e){
		e.preventDefault();
		
		generateWidgetPreview(widget.getSelectedIds());
    });
    
    $(".createRule").click(function(e){
		e.preventDefault();
		toastr.info('Are you the 6 fingered man?')
		//Toast.message('Не подключено');
    });
    
    $(".choseProduct").click(function(e){
		e.preventDefault();
		var $block = $(".block.choose-product");
		if($block.is(":visible")){
			$block.slideUp('fast');
		}else{
			$block.slideDown('fast');	
		}
		
    });
    
    
    api.call('getShopList', {bug:'fixItLater'}, function(response){
		shopId = response.list[0].id;
		getCategoryList(shopId);
    })
    $(".addProduct").unbind('click').bind('click', function(e){
    	e.preventDefault();
    	if($(".offerItem.active").length == 0){
			toastr.error('необходимо выбрать товар');
			return;
    	}
		var offer = $(".offerItem.active").data('offer');
		_offers.push(offer);
		toastr.info('Товар выбран');
		console.log(_offers);
    });
//    console.log(shopId);
    
   
    
  
});

function getCategoryList(shopId){
	api.call('getCategoryList', {shopId: shopId}, function(response){
    	console.log(response.list);
		var list = buildCategoryList(response);
		$(".treeHolder").append(buildTree('myTree', list));
		
		$(".Content").bind('click', function(){
			
			var cid = $(this).parent().data('cid'),
			pid = $(this).parent().data('pid');
			
			$(".previewPic img").attr('src', './img/preview.png');
			$(".offerInfo").empty();
			
			if(pid != 0){
				$(".Content").removeClass('b');
				$(this).addClass('b');
				$(".noOffers").remove();
				$(".offerHolder li").remove();
				getOfferList(cid);
			}
			else{
				
				$(this).prev().trigger('click');
				var event = {};
				event.target = $(this).prev()[0];
				tree_toggle(event);
			}
		});
		
		
    })
}

function getOfferList(cid){
	api.call('getOfferList', {'shopId': shopId, 'categoryId' : [cid]}, function(response){	
		
		console.log(response);
		var offers = [];
		if(response.list.length == 0){
			$(".offerHolder").before('<div class="noOffers">товаров нет</div>');
			return;
		}
		for(var i in response.list){
			var item = response.list[i];
			offers.push(jQuery.parseJSON(item));
		}
		
		
		buildOfferList(offers);
	});
}

function buildOfferList(offers){
	var $ul = $("ol.offerHolder");
	
	//console.log(offers);
	for(var i in offers){
		
//		console.log(jQuery.parseJson(offers[i].commod_data));
		var $li = $('<li class="offerItem">'+ offers[i].vendor +" "+ offers[i].vendorCode +'</li>');
		$li.data('offer', offers[i]);
		$ul.append($li);
	}
	$('.offerItem').unbind('click');
	$('.offerItem').bind('click', function(){
		$('.offerItem').removeClass('active');
		$(this).addClass('active');
		
		var offer = $(this).data('offer');
		console.log(offer);
		$('.previewPic img').attr('src', offer.picture);
		var $info = $("<div></div>")
		
		var isAvailable = offer.attributes.available?'Да':'Нет';
		
		$info.append("<div>"+offer.model +' '+ offer.vendor+"</div>")
		
		$info.append("<div>Цена: <span class='b'>"+offer.price+"</span></div>")
		$info.append("<div>Доступно: "+isAvailable+"</div>")
		$info.append("<div>Цвет: "+offer.param.color+"</div>")
		$info.append("<div>ID: " + offer.attributes.id + "</div>");
		$info.append("<div>CODE: "+offer.vendorCode+"</div>")
		$(".offerInfo").empty();
		$(".offerInfo").append($info);
		var offerCount = parseInt($(".widgetCount").html());
		
		$(".widgetCount").html(offerCount + 1);
    });
    
    
}

function _widget(){
//	this.ids = [];
	this.getSelectedIds = function(){
		return _offers.map(function(item){return item.attributes.id});
	}
}

function buildCategoryList(categories){
	if(categories.length == 0){
		toastr.error('нет категорий для отображения');
		return;
	}
	
    console.log('total:' + categories.list.length);
	var cats = {};
	for(var i in categories.list){
		var c = categories.list[i];
		
		var cid = c.category_id;
		var pid = c.parent_id;
		var t = {
			cid: cid,
			pid: pid,
			title: c.title
			
		};
		if(pid == 0){
			
			cats[cid] = t;
		}
		else{
			if(cats[pid]['childs'] == undefined)
				cats[pid]['childs'] = {};
			cats[pid]['childs'][cid] = t;
		}
		
	}
	console.log(cats);
	console.log('total after: ' + recursiveCount(cats));
	return cats;
}

function buildTree(treeId, cats){
	var $tpl = $("<div class=\""+treeId+" tree\" onclick=\"tree_toggle(arguments[0])\"></div>");
	var $ul = $("<ul class=\"Container\" ></ul>")
	
	for(var i in cats){
		var cat = cats[i];
		$ul.append(buildNode(cat));
	}
	$tpl.append($ul);
	return $tpl;
	//<li class="Node IsRoot
}


function buildNode(item){
	var $tpl = $("<li></li>");
	
	$tpl.addClass('Node');
	$tpl.addClass('ExpandLeaf');
	
	
	if(item.pid == 0)
		$tpl.addClass('IsRoot')
		
	$tpl.data('pid', item.pid);
	$tpl.data('cid', item.cid);
	
	$tpl.append('<div class="Expand"></div>');
	$tpl.append('<div class="Content">'+item.title+'</div>')
	
	if(item.childs != undefined){
		$tpl.removeClass('ExpandLeaf');
		$tpl.addClass('ExpandClosed');
		var innerContainer = $('<ul class="Container"></ul>');
		for(var i in item.childs){
			var childItem = item.childs[i];
			var innerNode = buildNode(childItem);	
			
			innerContainer.append(innerNode);
		}
		
		
		$tpl.append(innerContainer);
//		$tpl.addClass('ExpandClosed');
		
	}
	
	return $tpl;
}


function recursiveCount(list){
	var count = 0;
	for(var i in list){
		var item = list[i];
		//console.log(item);
//		console.log(item.cid);
		if(item.cid != 0)
			count++;
		if(item.childs != undefined || item.childs != {})
			count += recursiveCount(item.childs);
	}
	return count;
}

function _api(host){
	
	this.call = function(methodName, params, callback){
//		params['methodName'] = methodName;
		var data = {methodName: methodName, params: params};
		console.log(data);
		$.ajax({
			type: 'POST',
			url: host,
			data: data,
			cache: false, 
			dataType: "json",
		}).done(function(response){
			//console.log('api response');
//			console.log(response);
			if(!response.error){
				callback(response.data.data);
				return;
			}
			toastr.error('Ошибка при загрузке категорий, код ' + response.code);
			
//			console.log(jsonResponse);
//			var response = $.parseJSON(jsonResponse);
			
//			callback(jsonResponse);
		});
	}
}


function shop(){
	
}

function category(){
	
}

function model(){
	
}

function generateWidgetPreview(idArray){
	if(idArray.length == 0){
		toastr.error('Необходимо выбрать товар');
		return;
	}
	var host = "http://146.185.169.28/test.loc/www/?widget_id=";
	host += idArray.join(',');
	
	$(".widgetPreview").find('iframe').remove();
	var iframe = "<iframe src=\""+host+" \" width=\"100%\" height=\"600px\"></iframe>";
	$(".widgetPreview").append(iframe);
	
	
	$(".widgetUrl").val(host);
	
}

function clipBoard() 
{
	window.prompt("Copy to clipboard: Ctrl+C, Enter", $('.widgetUrl').val());
}

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

function tree_toggle(event) {
	event = event || window.event
	var clickedElem = event.target || event.srcElement

	if (!hasClass(clickedElem, 'Expand')) {
		return // клик не там
	}

	// Node, на который кликнули
	var node = clickedElem.parentNode
	if (hasClass(node, 'ExpandLeaf')) {
		return // клик на листе
	}

	// определить новый класс для узла
	var newClass = hasClass(node, 'ExpandOpen') ? 'ExpandClosed' : 'ExpandOpen'
	// заменить текущий класс на newClass
	// регексп находит отдельно стоящий open|close и меняет на newClass
	var re =  /(^|\s)(ExpandOpen|ExpandClosed)(\s|$)/
	node.className = node.className.replace(re, '$1'+newClass+'$3')
}


function hasClass(elem, className) {
	return new RegExp("(^|\\s)"+className+"(\\s|$)").test(elem.className)
}



