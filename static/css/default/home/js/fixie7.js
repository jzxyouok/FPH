/**************************************************
 *@content:   fixie7.js
 *@author：   xiongjianqiao@fangpinhui.com
 *@date:      2015-08-05
 *************************************************/
$(function(){
	var $btns=$("#menu").find('a');
	$btns.each(function(idx,ele){
		$(this).click(function(){
			$btns.parent().removeClass('active');
			$(this).parent().addClass('active');
			$.fn.fullpage.moveTo(idx+1);
		});
	});
	$(".s5-bg").find("img").width(514);
	$(".s5-text").find("img").width(346);
	// $(".s5-text").attr("rel","400,100,345,20");
	// $(".facetoface-service-inner").css("top",$(".facetoface-service-inner").position().top+35);
});

