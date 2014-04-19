
function buildTree(treeId, cats, shopId, placeholder, is_full){
    var $tpl,
        $ul,
        active =false,
        isFull = is_full|| false,
        listHeader = $("<div class=\""+treeId+" tree\" onclick=\"tree_toggle(arguments[0], "+shopId +");\"></div>");
    showLoading(true);
    //$ul = $("<ul class=\"Container\" ></ul>");
    if (placeholder != undefined){
        $tpl = placeholder;
        if(placeholder.find('.active').length>0){
            active =true;
        }
    } else {
        $tpl = $("<ul class=\"Container\" ></ul>");
    }
	for(var i in cats){
		var cat = cats[i];
        $tpl.append(buildNode(cat, active, isFull));
	}
    if (placeholder == undefined){
        $tpl = listHeader.append($tpl);
    }
    showLoading(false);
	return $tpl;
}

function buildNode(item, active, downloaded) {
    var $tpl,
        content;
    if (item.childs != undefined) {
        if (item.pid == 0) {
            var root = buildCommonNode(item, downloaded, active);
            $tpl = root.append($('<ul class="Container"></ul>'));
        } else {
            $tpl = $('<ul class="Container"></ul>');
        }
        for (var i in item.childs) {
            var childItem = item.childs[i];
            var innerNode = buildNode(childItem, active, downloaded);
            if ($tpl.find('.Container').length>0){
                $tpl.find('.Container').append(innerNode);
            } else {
                $tpl.append(innerNode);
            }
        }
    } else {
        $tpl = buildCommonNode(item, downloaded, active);

    }
    return $tpl;
}

function buildCommonNode(item, downloaded, active){
    var content;
    if (item.title != undefined) {
        if (active) {
            content = $('<div class="Content active">' + item.title + '</div>');
        } else {
            content = $('<div class="Content">' + item.title + '</div>');
        }
    }
    $tpl = $("<li></li>");

    $tpl.addClass('Node');
    $tpl.addClass('ExpandLeaf');

    if(item.pid == 0){
        $tpl = buildRootNode($tpl);
    }
    if (item.childCount > 0){
        $tpl = buildNotLeafNode($tpl);
    }

    $tpl.data('pid', item.pid);
    $tpl.data('cid', item.cid);
    $tpl.data('childCount', item.childCount);
    $tpl.data('download', downloaded);
    $tpl.append(content);
    return $tpl;

}

function buildRootNode($tpl){
    $tpl.addClass('IsRoot');
    $tpl.removeClass('ExpandLeaf');
    $tpl.addClass('ExpandClosed');
    return $tpl;
}

function buildNotLeafNode($tpl){
    $tpl.append('<div class="Expand"></div>');
    $tpl.addClass('ExpandClosed');
    $tpl.removeClass('ExpandLeaf');
    return $tpl;
}
//tree handler

function tree_toggle(event, shopId) {
	event = event || window.event;
	var clickedElem = event.target || event.srcElement;
    var newClass = 'ExpandClosed';
    if (!hasClass(clickedElem, 'Expand')) {
		return // клик не там
	}
	// Node, на который кликнули
	var node = clickedElem.parentNode
	if (hasClass(node, 'ExpandLeaf')) {
		return // клик на листе
	}

	// определить новый класс для узла
    if (!hasClass(node, 'ExpandOpen')){
        newClass = 'ExpandOpen';
        if ($(node).data('download') == false ){
            showLoading(true);
            getCategoryList(shopId, function (response){ var list = buildCategoryList(response); shopObject.renderCategoryTree(list, $(node));}, $(node).data('cid'));
        }
    }
	// заменить текущий класс на newClass
	// регексп находит отдельно стоящий open|close и меняет на newClass
	var re =  /(^|\s)(ExpandOpen|ExpandClosed)(\s|$)/
	node.className = node.className.replace(re, '$1'+newClass+'$3')
}

function hasClass(elem, className) {
	return new RegExp("(^|\\s)"+className+"(\\s|$)").test(elem.className)
}