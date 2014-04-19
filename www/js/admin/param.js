//$(document).ready(function(){
//    $('.dev-rule').click(function(event){
//        setTimeout(getParamList(event), 100);
//    })
//})

function getParamList(currElement, activeClass){
    var catIds = [];
    var holder = currElement.parents('.rule');
    holder.find('.treeHolder, .ruleHolder').find('.'+activeClass).each(function(){
            catIds.push($(this).parents('.Node').data('cid'));
    })
//    if (!$(event.target).hasClass('active')){
//        catIds.push($(event.target).parents().data('cid'));
//    }

    if (catIds.length){
        showLoading(true);
        api.call('getParamList', {'shopId': shopObject.id, 'categoryIds': catIds, 'withValue': true}, function(response){
            buildParamBlock(response, holder)
        });
    } else {
        holder.find('.paramContainer').each(function(){
            $(this).remove();
        });
    }
}
function buildParamBlock(response, holder){
    var paramValue  = response.paramValue;
    holder.find('.paramContainer').each(function(){
        var currentParam = $(this).find('.paramBlock h4').text();
        if (paramValue[currentParam] == undefined){
            $(this).remove();
        } else {
            delete paramValue[currentParam];
        }
    });
    for (var i in paramValue){
        var paramContainer = $('<div class="paramContainer clearfix"></div>');
        var header = $('<div class="paramBlock clearfix"><h4><ul><li onclick="displayParamBlock(arguments[0])">'+i+'</li></ul></h4></div>');
        var paramBlock = $('<ul class="paramList" style="display:none;"></ul>');
        for (var j in paramValue[i]){
            var paramElement = $('<li class="param">'+paramValue[i][j]+'</li>');
            paramElement.data('param-name', i);
            paramElement.data('param-value', paramValue[i][j]);
            paramBlock.append(paramElement);
        }
        paramContainer.append(header);
        paramContainer.append(paramBlock);
        holder.find('.paramTpl').append(paramContainer);
    }
    showLoading(false);
}

function displayParamBlock(event){
    var clickedElem = event.target;
    if ($(clickedElem).hasClass('active')){
        $(clickedElem).parents('.paramContainer').find('.paramList').css('display', 'none');
        $(clickedElem).removeClass('active');
    } else {
        $(clickedElem).parents('.paramContainer').find('.paramList').css('display', '');
        $(clickedElem).addClass('active');
    }
}