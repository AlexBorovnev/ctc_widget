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

	var $content = $('<div class="Content">'+item.title+'</div>');
	if(item.pid == 0){
		$tpl.addClass('IsRoot')
		var subCats = [];
		if(item.childs != undefined){
			for(var i in item.childs){
				var cid = item.childs[i].cid;
				subCats.push(cid);
			}
		}
		if(subCats.length > 0)
			$content.data('childs', subCats);
	}

	$tpl.data('pid', item.pid);
	$tpl.data('cid', item.cid);

	$tpl.append('<div class="Expand"></div>');
	$tpl.append($content);

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



//tree handler

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