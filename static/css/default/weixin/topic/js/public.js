// JavaScript Document
/*!
 * Author:yoleon
 * email:yoleon2008@gmail.com
 * 鎶勮铏芥槗锛屽師鍒涗笉鏄擄紝涓旀妱涓旂弽鎯滐紒
 * powered by 00ok
 */


var common={
	/**
	 * 寮曞椤靛姩鐢�
	 * @return {[type]} [description]
	 */
	infoAnimation:function(){
		var $info=$('.info');
		var $copyright=$info.find('.copyright'),$infoLight=$info.find('.earthLight'),$pTxt=$info.find('.pTxt');
		var $pTxt0=$pTxt.eq(0),$pTxt1=$pTxt.eq(1),$pTxt2=$pTxt.eq(2);

		//$copyright.css('opacity',0);
		common.setCss3($info,'scale(1)','all 2s ease-out',{'opacity':1});
		setTimeout(function(){
			$copyright.addClass('fadeInUp animated');
			//common.addFirstPageEvent();
		},1300);

		common.addFirstPageEvent();
		$('.topCon').hide()
	},

	/**
	 * 娣诲姞杩涘叆绗竴涓〉闈㈢殑鐐瑰嚮浜嬩欢
	 * @return {[type]} [description]
	 */
	addFirstPageEvent:function(){
		$('.info .tips').addClass('show').removeClass('hide');
		var hasTouch = 'ontouchstart' in window,
		START_EV = hasTouch ? 'touchstart' : 'mousedown';
		$('body').one(START_EV,function(){
			if(!system.index)common.playSound(true);
			common.outInfoPage();
		});
	},

	
	/**
	 * 杩涘叆绗竴椤�
	 * @return {[type]} [description]
	 */
	disFirstPage:function(){
		common.outInfoPage();
	},

	/**
	 * 杩涘叆寮曞椤�
	 */
	inInfoPage:function(){
		var $info=$('.info');
		var $firstPage=$('.page').eq(0);
		$firstPage.addClass('show');
		$info.addClass('show').removeClass('hide');
		common.setCss3($firstPage,'perspective(1000px) translate3d(0,'+0+'px,0px) rotateX(0deg) scale3d(1,1,1)','none',{'-webkit-backface-visibility':'hidden','backface-visibility':'hidden','zIndex':2});
		setTimeout(function(){
			common.setCss3($info,'perspective(1000px) translate3d(0,0px,0px) rotateX(0deg) scale3d(1,1,1)','all 0.7s ease-in-out',{'-webkit-backface-visibility':'hidden','backface-visibility':'hidden','zIndex':10});
			common.setCss3($firstPage,'perspective(1000px) translate3d(0,'+(system.H/2+50)+'px,'+(-120)+'px) rotateX(-90deg) scale3d(0.8,0.8,0.5)','all 0.7s ease-in-out');
		},30);
		window.setTimeout(function(){
			$firstPage.addClass('hide').removeClass('show');
			common.addFirstPageEvent();
		},700)
		common.topConStatus(false);
		//trace('杩涘叆寮曞椤�!!!!')
		system.isInfoPage=true;
	},

	/**
	 * 閫€鍑哄紩瀵奸〉
	 */
	 outInfoPage:function(){
	 	//alert('閫€鍑哄紩瀵奸〉!!')
	 	var $info=$('.info');
		var $firstPage=$('.page').eq(0);
		common.setCss3($firstPage,'perspective(1000px) translate3d(0,'+(system.H/2+50)+'px,'+(-150)+'px) rotateX(-90deg) scale3d(0.5,0.5,0.5)','none',{'-webkit-backface-visibility':'hidden','backface-visibility':'hidden'});
		$firstPage.addClass('show').removeClass('hide');
		setTimeout(function(){
			common.setCss3($info,'perspective(1000px) translate3d(0,'+(-system.H/2)+'px,'+(-0)+'px) rotateX(90deg) scale3d(0.51,0.51,0.51)','all 0.7s ease-in-out',{'-webkit-backface-visibility':'hidden','backface-visibility':'hidden','zIndex':0});
			common.setCss3($firstPage,'perspective(1000px) translate3d(0,'+0+'px,0px) rotateX(0deg) scale3d(1,1,1)','all 0.7s ease-in-out');
			
			setTimeout(function(){
				if(!system.index){
					/**
					 * 鐢熸垚瑙︽懜浜嬩欢
					 * @type {Hammer}
					 */
					//$info.remove();
					$info.addClass('hide');
					$('#main').removeClass('perspective');
					common.createEvent();
				}
				$firstPage.attr('style','').removeClass('firstPage');
				system.isInfoPage=false;

				common.topConStatus(true);
			},700);

		},30);
	 },
	
	//褰撳墠缃戦〉鏄惁鍦ㄥ井淇′腑娴忚
	is_weixin:function(){
		var ua = navigator.userAgent.toLowerCase();
		if(ua.match(/MicroMessenger/i)=="micromessenger")return true;
			else return false;
	},

	/**
	 * 鐢熸垚瑙︽懜浜嬩欢
	 * @type {Hammer}
	 */
	createEvent:function(){
		//$('.page').eq(0).find('.pageContent').addClass('activeHorizontal');
		system.index=$('.page').size();
		common.setPageIndex();
		common.pageEffects();

		var hammer = new Hammer(document.getElementById("main"));
		/**
		 * 寮€濮嬫嫋鍔�
		 */
		hammer.on('dragstart',function(e){
			//if(!hammer.gesture){hammer.gesture=e.gesture;trace('gesture');return;}
			if(!e.gesture || system.isInfoPage || !system.touch)return;
			var Y=e.gesture.deltaY,direction=e.gesture.direction,index;
			/**
			 * 杩斿洖娆㈣繋椤�
			 */
			if((direction!='up') && (system.currentPage==0)){
				common.inInfoPage();
				return
			};
			if(system.loop && !system.firstVisited && direction=='down' && system.currentPage==0)return;
			if(!system.loop){
				if(system.currentPage==0&&direction=='down')return;
				if(system.currentPage==(system.index-1)&&direction=='up')return;
			}
			system.touchStatus=true;
			index=(direction=='up')?system.nextPage:system.prePage;
			system.touchObj=$('.page').eq(index);
			if(direction=='up'){
				system.touchObj.addClass('active');
				common.setCss3(system.touchObj,'translate(0,'+(system.H)+'px)','none');
				system.initY=system.H;
				system.direction='up';
				system.fastChange=true;
			}else if(direction=='down'){
				system.touchObj.addClass('active');
				common.setCss3(system.touchObj,'translate(0,'+(-system.H)+'px)','none');
				system.initY=-system.H;
				system.direction='down';
				system.fastChange=false;
			}else{
				system.touchStatus=false;
			}
		})
		
		/**
		 * 鎷栧姩涓�
		 */
		hammer.on('drag',function(e){
			if(!system.touchStatus || !system.touch)return;
			var gesture=e.gesture,Y=gesture.deltaY;
			switch(system.direction){
				case 'up':
					common.setCss3(system.touchObj,'translate(0,'+(system.H+Y)+'px)','none');
				break;

				case 'down':
					common.setCss3(system.touchObj,'translate(0,'+(-(system.H-Y))+'px)','none');
				break;
			}
			common.pageTouchMoving(Y);
		})

		/**
		 * 鎷栧姩缁撴潫
		 */
		hammer.on('dragend',function(e){
			if(!system.touchStatus || !system.touch)return;
			system.touchStatus=false;
			if(!system.fastChange)system.touch=false;
			var Y=e.gesture.deltaY,ratioH=system.H*system.longRatio,dTime=e.gesture.deltaTime;
			var endY=0,d=system.duration;
			var activeObj=system.touchObj;
			
			if(Math.abs(Y)>ratioH || dTime<350){
				//鍒囨崲椤甸潰
				var $hide=$('#main .page').eq(system.currentPage);
				common.setCss3(activeObj,'translate(0,'+(endY)+'px)','all '+d+'s');
				//鍒囨崲鎴愬姛
				window.setTimeout(function(){
					$hide.addClass('hide').removeClass('show');
					activeObj.addClass('show').removeClass('hide active');
					if(!system.fastChange)system.touch=true;
					common.pageChangeEnd();
				},d*1000)
				common.pageOutStyle(system.currentPage,true,0.5);
				var index=(system.direction=='up')?system.nextPage:system.prePage;
				var cIndex=$('.page').eq(index).index('.page');
				if((system.prePage==system.index-2)&&(system.currentPage==system.index-1)){
					system.firstVisited=true;
				}
				system.currentPage=cIndex;
				common.setPageIndex();
			}else{
				endY=system.initY;
				common.setCss3(activeObj,'translate(0,'+(endY)+'px)','all '+d/2+'s');
				window.setTimeout(function(){
					common.setCss3(activeObj,'none','none');
					activeObj.removeClass('active');
				},d*1000)
				var cNum=system.currentPage;
				common.pageOutStyle(cNum,false,0.3);
			}
		})
		//hammer.drag=false;
	},

	/**
	 * 璺宠浆椤甸潰
	 * @return {[type]} [description]
	 */
	gotoPage:function(pageIndex){
		system.touchObj=$('.page').eq(pageIndex);
		var initY,endY=0,d=system.duration,activeObj=system.touchObj;
		var $hide=$('#main .page').eq(system.currentPage);
		if(pageIndex>system.currentPage){
			system.direction='up';
			initY=system.H;
		}else {
			system.direction='down';
			initY=-system.H;
		}
		activeObj.addClass('active');
		system.touchStatus=false;

		//鍒囨崲椤甸潰
		common.setCss3(activeObj,'translate(0,'+(initY)+'px)','none');
		window.setTimeout(function(){
			common.setCss3(activeObj,'translate(0,'+(endY)+'px)','all '+d+'s');
		},30)
		//鍒囨崲鎴愬姛
		window.setTimeout(function(){
			$hide.addClass('hide').removeClass('show');
			activeObj.addClass('show').removeClass('hide active');
			if(!system.fastChange)system.touch=true;
			common.pageChangeEnd();
		},d*1000)
		common.pageOutStyle(system.currentPage,true,0.5);
		system.currentPage=pageIndex;
		if((system.prePage==system.index-2)&&(system.currentPage==system.index-1)){
			system.firstVisited=true;
		}
		common.setPageIndex();
	},

	/**
	 * touchEnd 椤甸潰鍒囨崲瀹屾垚
	 * @return {[type]} [description]
	 */
	pageChangeEnd:function(){
		var n=system.currentPage;
		var cPage=$('.page').eq(n);
		/*if(cPage.hasClass('contactPage'))common.topConStatus(false);
		else common.topConStatus(true);*/
		common.pageEffects();
		common.lazyBigAfter();
	},

	/**
	 * 瑙︽懜绉诲姩
	 * @param  {[type]} Y [鍧愭爣]
	 */
	pageTouchMoving:function(Y){
		var cNum=system.currentPage,S=1-(Math.abs(Y)/system.H)*(1-system.minScale),Y=Y*system.changeRatio;
		common.setCss3($('.page').eq(cNum),'translate(0px, '+Y+'px) scale('+S+')','none');
	},

	/**
	 * 鍗曢〉闈㈤噷鐨勭壒鏁�
	 * @return {[type]} [description]
	 */
	pageEffects:function(){
		var cNum=system.currentPage,pageCon = $(".page").eq(cNum).find('.pageContent'),
		effect=pageCon.attr('data-effect'),//椤甸潰鐗规晥
		dir=pageCon.attr('data-dir');//鏂瑰悜
		dur=2;
		if(dir=='true')pageCon.attr('data-dir','false');
			else pageCon.attr('data-dir','true');
		switch(effect){
			case 'h'://妯悜绉诲姩
				var maxMoveX=pageCon.width()-640;//鏈€澶хЩ鍔ㄥ€�
				dur=maxMoveX/60<2?2:maxMoveX/60;
				if(maxMoveX>50){
					if(dir=='true')common.setCss3(pageCon,'translate('+(-maxMoveX)+'px,0px)','all '+dur+'s');
					else common.setCss3(pageCon,'translate(0,0px)','all '+dur+'s');
				}
			break;

			case 'v'://绾靛悜绉诲姩
				var maxMoveY=pageCon.height()-pageCon.parent().height();//鏈€澶хЩ鍔ㄥ€�
				dur=maxMoveY/60<2?2:maxMoveY/60;
				if(dir=='true')common.setCss3(pageCon,'translate(0px,'+(-maxMoveY)+'px)','all '+dur+'s');
					else common.setCss3(pageCon,'translate(0,0px)','all '+dur+'s');
			break;

			case 'scale'://缂╂斁鏁堟灉
				if(dir=='true')common.setCss3(pageCon,'scale(0.85,0.85)','all 3s');
					else common.setCss3(pageCon,'scale(1,1)','all '+dur+'s');
			break;

			default:
				console.log('鐪嬶紝澶╀笂鏈夌伆鏈猴紒锛�');
		}
	},

	/**
	 * 椤甸潰閫€鍑�
	 * @return {[type]} [description]
	 */
	pageOutStyle:function(n,adEd,dur){
		//return;
		var pNum=system.prePage,cNum=system.currentPage,nextNum=system.nextPage;
		var dur=dur?dur:0;
		var cPage=$('.page').eq(n);
		cPage.css({'-webkit-transform': 'none','transform': 'none',});
		if(adEd){
			var TY=system.H*system.changeRatio;
			if(system.direction=='up')TY=-TY;
			common.setCss3(cPage,'translate(0px, '+TY+'px) scale('+system.minScale+')','all '+dur+'s ease-in-out');
		}else{
			var tra='none';
			if(dur!=0)tra='all '+dur+'s';
			common.setCss3(cPage,'none',tra);
		}
	},

	/**
	 * 鍒濆鍖栧欢杩熷姞杞�
	 * @return {[type]} [description]
	 */
	initLazy:function(){
		/* 澶у浘鏂囧浘鐗囧欢杩熷姞杞� */
		var lazyNode = $('.lazy-bk');
		lazyNode.each(function(){
			var self = $(this);
			if(self.is('img')){
				self.attr('src','images/logo.jpg');
			}else{
				/*self.css({
					'background-image'	: 'url(images/public/loading_large.gif)',
					'background-size'	: '120px 120px'
				})*/
				//self.html('<div class="cubeLoading"><div class="cube1"></div><div class="cube2"></div></div>');
			}
		})
		
		/**
		 * 鍔犺浇鍓嶉潰涓夐〉
		 * @return {[type]} [description]
		 */
		setTimeout(function(){
			for(var i=0;i<3;i++){
				var node = $(".page").eq(i);
				if(node.length==0) break;
				if(node.find('.lazy-bk').length!=0){
					common.lazyChange(node);
				}else continue;
			}
		},200)
	},

	// 鍔犺浇褰撳墠椤电殑鍚庨潰绗笁椤�
	lazyBigAfter:function(){
		if($('.lazy-bk').size()==0)return;
		var currentPage=system.currentPage;
		for(var i=1;i<4;i++){
			common.lazyBigIndex(currentPage+i);
		}
		if(system.currentPage==0)return;
		common.lazyBigIndex(currentPage-1);
	},

	lazyBigIndex:function(n){
		var node = $(".page").eq(n);
		//if(node.length==0) return;
		if(node.length!=0 || node.find('.lazy-bk').length!=0){
			common.lazyChange(node);
		}
	},

	/**
	 *  鍥剧墖寤惰繜鍔犺浇鐨勬浛鎹㈠浘鐗囧嚱鏁�
	 * @param  {[type]} node [object]
	 */
	lazyChange:function(node){
		var lazy = node.find('.lazy-bk');
		lazy.each(function(){
			var self = $(this),srcImg = self.attr('data-bk');
			$('<img />').on('load',function(){
				if(self.is('img')){
					self.attr('src',srcImg)
				}else{
					var isScale,vAction,OH;
					if(self.attr('data-effect')=='v')vAction=true;
					if(self.attr('data-effect')=='scale')isScale=true;
					
					var picW=this.width,picH=this.height,objH;
					if(isScale)objH=self.parent().height();
					if(isScale || vAction)OH=picH+'px';
						else OH='100%'
					self.css({
						'width':picW+'px',
						'height':OH,
						'marginLeft':isScale?(-(picW-system.pageW)/2)+'px':'auto',
						'marginTop':(isScale && !vAction)?(objH-picH)/2+'px':'auto',
						'background-image': 'url('+srcImg+')',
						//'background-size'	: isScale?'100%':'cover',
						'background-position': isScale?'center center':'0% center'
					})
				}
				/*// 鍒ゆ柇涓嬮潰椤甸潰杩涜鍔犺浇
				var len=$(".page").size();
				for(var i =0;i<len;i++){
					var page = $(".page").eq(i);
					if($(".page").find('.lazy-bk').length==0) continue
					else{
						common.lazyChange(page);
					}
				}*/
				self.empty();
			}).attr("src",srcImg);
			self.removeClass('lazy-bk');
		})	
	},

	/**
	 * 璁剧疆鍒囨崲椤甸潰鐨勭储寮曞€�
	 */
	setPageIndex:function(){
		var CP=system.currentPage;
		system.nextPage=CP+1;
		system.prePage=CP-1;
		if(system.prePage<0)system.prePage=system.index-1;
		if(system.nextPage>=system.index)system.nextPage=0;
	},

	/**
	 * 娣诲姞闊充箰
	 */
	addSoundHtml:function(){
		//var soundInto='<audio id="media" loop="loop" src="sound/bg_music.mp3"></audio>';
		var soundInto='<audio id="media" loop="loop" src="' + music + '"></audio>';
		$('body').append(soundInto);
	},

	/**
	 * 鎺у埗闊充箰鍜岀數璇濆彿鐮佺殑鏄剧ず鐘跺喌
	 * @param  {[type]} b [description]
	 * @return {[type]}   [description]
	 */
	topConStatus:function(b){
		var $topCon=$('.topCon');
		if(b){
			if($topCon.attr('data-show')=='0'){
				$topCon.attr('data-show','1')
				$topCon.fadeIn('slow');
			}
		}else {
			if($topCon.attr('data-show')=='1'){
				$topCon.attr('data-show','0')
				$topCon.fadeOut('slow');
			}
		}
	},
	
	/**
	 * 鎺у埗闊充箰鎾斁\鏆傚仠
	 * @param  {[type]} b [description]
	 * @return {[type]}   [description]
	 */
	playSound:function(b){
		if(!b){
			document.getElementById("media").pause();
			if(browser.versions.isIphone)$('.sound').css({'-webkit-animation-play-state':'paused','animation-play-state':'paused',});
			else $('.sound').removeClass('rotateAm');
			system.soundStatus=false;
		}else{
			document.getElementById("media").play();
			if(browser.versions.isIphone)$('.sound').css({'-webkit-animation-play-state':'running','animation-play-state':'running'});
			else $('.sound').addClass('rotateAm');
			 
			system.soundStatus=true;
		}
	},

	/**
	 * 璁剧疆CSS3
	 * @param {[type]} obj    [瑕佹搷浣滅殑瀵硅薄]
	 * @param {[type]} f      [transform]
	 * @param {[type]} t      [transition]
	 * @param {[type]} attach [鍏跺畠鐨勫睘鎬у璞
	 */
	setCss3:function(obj,f,t,attach){
		obj.css({'transform':f,'transition':t,'-webkit-transform':f,'-webkit-transition':t})
		if(attach)obj.css(attach);
	}
}