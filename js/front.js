$(document).ready(function(){
	$('#advanced-search').hide();
	
	$('#show-advanced').children('a').attr('href', 'javascript:void(0);');
	$('#show-advanced').children('a').click(function() {
		$('#advanced-search').slideToggle();
	});
});