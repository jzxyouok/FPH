//如果做下拉加载，则每次加载以后应该调用一下fixPosition()来修正图片的位置
$(function(){
	/*	
	document.onreadystatechange=function(){
		if(document.readyState=='complete'){
			fixPosition();
		}
	}
	*/
	// 图片位置修正
	function fixPosition(){
		
		$(".img-box").each(function(ele,idx){

			var $box=$(this);
			var oImg=$box.find('img');
			if( oImg.height() > oImg.width() ){
				oImg.css({
					"width":"auto",
					"height" :"100%"
				});
			}
			// 宽图,上下居中
			oImg.css( {
				"top" : ( $box.height()-oImg.height() ) / 2
				// "left" : ( $box.width()-oImg.width() ) / 2
			} );

		});
	}

	
});