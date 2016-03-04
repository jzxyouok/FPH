$(document).ready(function() {

	$("#s1-ewm").fadeIn(1200);

	//首屏轮播效果
	// var index = 0; //当前显示的图片，默认为0		
	// var slideBox = $(".slide-box");
	// var oImg=slideBox.find('img');
	// setInterval(function() {
	// 	//根据元素个数循环调用showImage();方法
	// 	++index < oImg.length ? showImage(index) : showImage(index = 0);
	// }, 3e3);
	//处理图片
	function showImage(index) {
		var oW=oImg.width();
		slideBox.stop().animate({"left":-oW*index},1000,'swing');
	};

	var oSections=$(".section");
	var topArr=[];
	oSections.each(function(idx,ele){
		topArr.push({
			section:ele,
			top:$(ele).offset().top+$(ele).height()/2
		});
	});
	//滚动动画
	for(var i=1;i<oSections.length-1;i++){
		$(oSections[i]).find('a').css('opacity',0);
	}
	$(window).on('scroll',function(){
		var top=$(window).scrollTop()+$(window).height();
		if(topArr.length){
			for(var i=1;i<topArr.length-1;i++){
				if(top>=topArr[i].top){
					var targetItem=topArr.splice(i,1);
					//动画
					fadeInUp( $(targetItem[0].section).find('a') );
				}
			}
		}
	});

	function fadeInUp(items){
		var posArr=[];
		for(var i=0;i<items.length;i++){
			posArr.push({
				top:$(items[i]).position().top,
				left:$(items[i]).position().left
			});
		}
		$(items).css({'position':'absolute'});
		for(var i=0;i<items.length;i++){
			$(items[i]).css({
				top:posArr[i].top+100,
				left:posArr[i].left,
				opacity:0
			});
		}
		for(var i=0;i<items.length;i++){
			$(items[i]).delay(500).stop().animate({
				top:posArr[i].top,
				opacity:1
			},800,'swing');
		}
	}
});
