
$(function(){

	//全局变量
	var window_w = $(window).width(),
		window_h = $(window).height(),
		//$index = $(".app-index"),
		$page = $(".app-page"),
		$page1 = $page.find(".page-1"),
		arrow = $(".arrow"),
		slide = $(".page"),
		slide_size = $(".page").size(),
		slide_current,
		startP,
		moveP,
		range;
		back = false,
		bgm = $("#bgm")[0];

	//首页指纹
	//$(".app-index .button").on("touchstart",function(){
//		var count = 0;
//		$(this).addClass("active");
//		setTimeout(function(){
//			$page.addClass("show");
//			$page1.addClass("show");
//		},1000);
//		setTimeout(function(){
//			$index.removeClass("show");
//		},2000);
//	}).on("touchend",function(){
//		$(this).removeClass("active");
//		bgm.play();
//	});

	//背景音乐
$(".player").on("click",function(){
		$(this).toggleClass("stop");
		if($(this).hasClass("stop")){
			bgm.pause();
		}else{
			bgm.play();
		}
	});
	//滑动页面
	slide.on("touchstart",function(e){
		startP = window.event.touches[0].pageY; //获取初始坐标
		slide_current = parseInt($(this).attr("page")); //当前滑动页
		return false;
	}).on("touchmove",function(e){
		moveP = window.event.touches[0].pageY; //获取滑动中坐标
		range = moveP - startP; //滑动距离
		scale = 1 - Math.abs(range)/window_h; //缩小
		disappear = 1 - Math.abs(range)/window_h*2; //渐隐
		//第1页
		if(slide_current == 1 && range < 0){
			$(this).find(".title").css({"transform":"translate(0,"+ range +"px) scale("+ scale + "," + scale + ")"});
		}
		//第2页、第3页、第4页
		if(slide_current == 2 || slide_current == 3 || slide_current == 4){
			$(this).find("h3 p").css({"transform":"translate(0,"+ -Math.abs(range) +"px)"});
			$(this).find(".content").css({"transform":"translate("+ -range +"px,0)"});
		}
		//第5页
		if(slide_current == 5){
			$(this).find(".content>div").css({"transform":"translate(0,"+ -Math.abs(range) +"px)"});
			$(this).find(".phone").css({"transform":"translate("+ -range +"px,0)"});
		}
		//第6页
		if(slide_current == 6){
			$(this).find("h3 p").css({"transform":"translate(0,"+ -Math.abs(range) +"px)"});
			$(this).find(".content").css({"transform":"translate("+ -range +"px,0)"});
		}
		//第7页
		if(slide_current == 7){
			$(this).find("h3 p").css({"transform":"translate(0,"+ -Math.abs(range) +"px)"});
			$(this).find(".content").css({"transform":"translate("+ -range +"px,0)"});
		}
		//第8页
		if(slide_current == 8){
			$(this).find("h3 p").css({"transform":"translate(0,"+ -Math.abs(range) +"px)"});
			$(this).find(".content").css({"transform":"translate("+ -range +"px,0)"});
		}
		//第9页
		if(slide_current == 9){
			$(this).find(".content>div").css({"transform":"translate(0,"+ -Math.abs(range) +"px)"});
			$(this).find(".phone").css({"transform":"translate("+ -range +"px,0)"});
		}
		//第10页
		if(slide_current == 10){
			$(this).find("h3 p").css({"transform":"translate(0,"+ -Math.abs(range) +"px)"});
			$(this).find(".content").css({"transform":"translate("+ -range +"px,0)"});
		}
		if(slide_current == 11){
			$(this).find(".button").css({"transform":"translate(0," + Math.abs(range) + "px)"});
			$(this).find("h3 p").css({"transform":"translate(0,"+ -Math.abs(range) +"px)"});
			$(this).find(".content").css({"transform":"translate("+ -range +"px,0)"});
		}
	}).on("touchend",function(){
		var slide_next;
		if(range < -100){    //上滑
			if(slide_current == slide_size){
				slide_next = 1;
				back = true;
			}else{
				slide_next = slide_current + 1;
			}
		}else if(range > 100){    //下滑
			if(slide_current == 1){
				if(back == true){
					slide_next = slide_size;
				}else{
					slide_next = undefined;
				}
			}else{
				slide_next = slide_current - 1;
			}
		}
		switch(slide_current){
			case 1://第1页
				var _title = $(this).find(".title");
				_title.attr("style","").addClass("transition");
				setTimeout(function(){
					_title.removeClass("transition");
				},300);
				break;
			case 2://第2页
				var _h3 = $(this).find("h3 p"),
					_content = $(this).find(".content");
				_h3.attr("style","").addClass("transition");
				_content.attr("style","").addClass("transition");
				setTimeout(function(){
					_h3.removeClass("transition");
					_content.removeClass("transition");
				},300);
				break;
			case 3://第3页
				var _h3 = $(this).find("h3 p"),
					_content = $(this).find(".content");
				_h3.attr("style","").addClass("transition");
				_content.attr("style","").addClass("transition");
				setTimeout(function(){
					_h3.removeClass("transition");
					_content.removeClass("transition");
				},300);
				break;
			case 4://第4页
				var _h3 = $(this).find("h3 p"),
					_content = $(this).find(".content");
				_h3.attr("style","").addClass("transition");
				_content.attr("style","").addClass("transition");
				setTimeout(function(){
					_h3.removeClass("transition");
					_content.removeClass("transition");
				},300);
				break;
				case 5://第5页
				var _content = $(this).find(".content>div"),
				_phone = $(this).find(".phone");
				_content.attr("style","").addClass("transition");
				_phone.attr("style","").addClass("transition");
				setTimeout(function(){
					_content.removeClass("transition");
				_phone.removeClass("transition");
				},300);
				break;
				case 6://第6页
				var _h3 = $(this).find("h3 p"),
					_content = $(this).find(".content");
				_h3.attr("style","").addClass("transition");
				_content.attr("style","").addClass("transition");
				setTimeout(function(){
					_h3.removeClass("transition");
					_content.removeClass("transition");
				},300);
				break;
				case 7://第7页
				var _h3 = $(this).find("h3 p"),
					_content = $(this).find(".content");
				_h3.attr("style","").addClass("transition");
				_content.attr("style","").addClass("transition");
				setTimeout(function(){
					_h3.removeClass("transition");
					_content.removeClass("transition");
				},300);
				break;
				case 8://第8页
				var _h3 = $(this).find("h3 p"),
					_content = $(this).find(".content");
				_h3.attr("style","").addClass("transition");
				_content.attr("style","").addClass("transition");
				setTimeout(function(){
					_h3.removeClass("transition");
					_content.removeClass("transition");
				},300);
				break;
				case 9://第9页
			var _content = $(this).find(".content>div"),
			_phone = $(this).find(".phone");
			_content.attr("style","").addClass("transition");
			_phone.attr("style","").addClass("transition");
			setTimeout(function(){
				_content.removeClass("transition");
				_phone.removeClass("transition");
			},300);
			break;
			case 10://第10页
				var _h3 = $(this).find("h3 p"),
					_content = $(this).find(".content");
				_h3.attr("style","").addClass("transition");
				_content.attr("style","").addClass("transition");
				setTimeout(function(){
					_h3.removeClass("transition");
					_content.removeClass("transition");
				},300);
				break;
				
				case 11://第11页
				var _h3 = $(this).find("h3 p"),
					_content = $(this).find(".content");
				_h3.attr("style","").addClass("transition");
				_content.attr("style","").addClass("transition");
				_button = $(this).find(".button");
				setTimeout(function(){
					_h3.removeClass("transition");
					_content.removeClass("transition");
					_button.removeClass("transition");
				},300);
				break;
		}
		if(slide_next != undefined){
			$(this).removeClass("show");
			$(".page-" + slide_next).addClass("show");
		}
		range = 0;
		slide_next = undefined;
	});
	//第9页
	$(".page-11 .button").on("touchstart",function(e){
		e.stopPropagation();
	}).on("touchmove",function(e){
		e.stopPropagation();
	}).on("touchend",function(e){
		e.stopPropagation();
	});
	//分享
	$(".share").on("click",function(){
		$(".share-dialog").addClass("show");
	});
	$(".share-dialog").on("click",function(){
		$(this).removeClass("show");
	});
});