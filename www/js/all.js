$(function(){
	makeButton( "input[type=button], input[type=submit], button, a.btn" );
	$( ".check" ).button();
	
});

function makeButton(selector){
	$(selector).button().find('span').css('font-weight', 'bold');
}