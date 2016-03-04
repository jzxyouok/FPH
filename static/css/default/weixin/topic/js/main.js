// JavaScript Document
/*!
 * Author:yoleon
 * email:yoleon2008@gmail.com
 * 鎶勮铏芥槗锛屽師鍒涗笉鏄擄紝涓旀妱涓旂弽鎯滐紒
 * powered by 00ok
 */
/**
 * 绯荤粺妫€娴�
 * @type {Object}
 */
var browser={
	versions:function(){
		var ua= navigator.userAgent.toLowerCase(); 
		return {
			isIpad: ua.match(/ipad/i) == "ipad",
			isIphone: ua.match(/iphone os/i) == "iphone",
			isAndroid: ua.match(/android/i) == "android",
			isWM:ua.match(/windows mobile/i) == "windows mobile",
			bIsUc:ua.match(/ucweb/i) == "ucweb"  //鍒ゆ柇鏄惁涓� ucweb
		};
	}()
}

/**
 * 鍏ㄥ眬鍙傛暟
 * @type {Object}
 */
var system={
	/**
	 *public
	 */
	loop:true,//鏄惁寰幆鍒囨崲椤甸潰
	isSound:true,//鏄惁娣诲姞浜嗚儗鏅煶涔�(鍙傛暟[false:娌℃湁鑳屾櫙闊充箰,true:鏈夎儗鏅煶涔怾);
	duration:.35,//鍒囨崲椤甸潰鍔ㄧ敾琛ラ棿鐨勬寔缁椂闂�
	minScale:0.5,//椤甸潰鍒囨崲鐨勬渶灏忕缉鏀惧€�
	changeRatio:0.4,//涓嬩竴椤典笌褰撳墠椤电殑绉诲姩姣旂巼
	longRatio:0.1,//瑙﹀彂椤甸潰鍒囨崲鐨勬瘮鐜�
	pageW:640,//椤甸潰瀹�
	fastChange:true,//鏄惁寮€鍚揩閫熷垏鎹㈤〉闈�

	/**
	 *private
	 */
	index:null,//鍏卞灏戦〉
	currentPage:0,//褰撳墠椤�
	prePage:0,//褰撳墠椤电殑涓婁竴椤�
	nextPage:0,//褰撳墠椤电殑涓嬩竴椤�

	firstVisited:false,//绗竴娆℃祻瑙堢粨鏉�(鍔熻兘锛氭祻瑙堢粨鏉熷悗鍙紑鍚惊鐜垏鎹㈤〉闈紝鍓嶆彁鏄痩oop鍙傛暟蹇呴』涓簍rue)

	ty:0,//姝ゅ弬鏁版殏娌′綔鐢�
	touch:true,//鏄惁寮€鍚Е鎽告粦鍔ㄥ垏鎹㈤〉闈�

	touchStatus:false,//鏄惁姝ｅ湪鎷栧姩
	touchObj:null,//褰撳墠瑙︽懜瀵硅薄

	direction:null,//瑙︽懜鏂瑰悜

	initY:0,//椤甸潰婊戝姩鏀瑰彉鍓嶇殑鍒濆Y鍧愭爣

	ease:'',//鍒囨崲椤甸潰鐨勭紦鍔ㄥ嚱鏁�(ease-in-out ease-in ease-out...)

	soundStatus:false,//闊充箰鏄惁鎾斁(鍙傛暟[false:鏆傚仠,true:鎾斁]);
	isInfoPage:true,//鐢ㄦ潵鍒ゆ柇鐢ㄦ埛鏄惁鍦ㄦ煡鐪嬪紩瀵奸〉,濡傛灉涓簍rue,鍒欐殏鍋滆Е鎽稿垏鎹㈤〉闈�.鍚﹀垯寮€濮嬭Е鎽稿垏鎹㈤〉闈�
	H:null//绐楀彛楂樺害
}

/**
 * 褰撳叏椤甸潰鍔犺浇瀹屾垚
 * @return {[type]} [description]
 */


window.onload=function(){
	var init=function(){
		$('.page').addClass('hide');

		$('.loading').fadeOut(1200,function(){
			$('.loading').remove();
			pageLoading.destroy();
			pageLoading=null;
			if($('.tips').length)$('.tips').show();
			if($('.sound').length)$('.sound').show();
		});
		
		common.infoAnimation();
		//common.enterPage();
		common.initLazy();
		common.addSoundHtml();
		common.playSound(true);
		

		/**
		 * 鎺у埗澹伴煶鎾斁鎴栨殏鍋�
		 * @return {[type]} [description]
		 */
		if(system.isSound){
			$('.sound').bind('touchstart',function(){
				common.playSound(!system.soundStatus);
			})
		}
		

		/**
		 * 鑸炲彴鑷€傚簲
		 * @return {[type]} [description]
		 */
		window.addEventListener('resize',function(){
			resizeHandler();
		});

		function resizeHandler(){
			system.H=$(window).height();
		}
		resizeHandler();
	}

	/**
	 * 鍒濆鍖�
	 */
	init();

	//寮€濮嬬姝㈠睆骞曠殑婊戝姩浜嬩欢
	window.addEventListener('touchmove',banTouchScroll,false);
	function banTouchScroll(e){e.preventDefault();return false;};
}

/**
 * 娴嬭瘯鍑芥暟   涓汉鍠滃ソ!
 * @param  {[type]} src [瑕佽緭鍑虹殑瀛楃]
 * @return {[type]}     [none]
 */
function trace(src,a,b,c,d){
}