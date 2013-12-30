var chosenOpts = {disable_search_threshold: 10, width: "150px"};
prepareLocalEnvironmet();

function prepareLocalEnvironmet(){
	//document.cookie += "server=local";
	
	if(document.cookie == 'server=local')
		serverHost = 'api.php';
	console.log('server host: ', serverHost);	
}
function recursiveCount(list){
	var count = 0;
	for(var i in list){
		var item = list[i];
		
		if(item.cid != 0)
			count++;
		if(item.childs != undefined || item.childs != {})
			count += recursiveCount(item.childs);
	}
	return count;
}

function _api(host){
	
	this.call = function(methodName, params, callback){
		//if(isEmpty(params))
//			params['bug'] = 'fixItLater';
		var data = {methodName: methodName, params: params};
		
		$.ajax({
			type: 'POST',
			url: host,
			data: data,
			cache: false, 
//			dataType: "json",
		}).done(function(response){
			response = response.replace(/<[^<]+?>(.+)?/g, '');
			response = jQuery.parseJSON(response);
			
			console.log('%cAPI CALL: ', "color: blue;font-size: 14px;", data, response);
			if(!response.error){
				callback(response.data.data);
				return;
			}
			console.error('error[' + response.code+'] loading data from server, method= ['+methodName+'], params=['+params+'] ');
			toastr.error('Ошибка при получении данных с сервера, код ' + response.code);
		});
	}
}

function clipBoard(fromSelector) {
	window.prompt("Copy to clipboard: Ctrl+C, Enter", $(fromSelector).val());
}

function isEmpty(obj) {

    // null and undefined are "empty"
    if (obj == null) return true;

    // Assume if it has a length property with a non-zero value
    // that that property is correct.
    if (obj.length && obj.length > 0)    return false;
    if (obj.length === 0)  return true;

    // Otherwise, does it have any properties of its own?
    // Note that this doesn't handle
    // toString and toValue enumeration bugs in IE < 9
    for (var key in obj) {
        if (hasOwnProperty.call(obj, key)) return false;
    }

    return true;
}