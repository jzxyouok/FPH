/*******************************
 * Copyright:智讯互动(www.zhixunhudong.com)
 * Author:Mr.Think
 * Description:基类
 *******************************/
/*_WXShare('http://www.baidu.com/img/bdlogo.png','100','100','房品汇fufu得正','房品汇fufu得正，做一颗有梦想的子弹！','http://www.baidu.com');*/

KISSY.use('node,dom,event,io,cookie,gallery/simple-mask/1.0/,gallery/kissy-mscroller/1.3/index,gallery/simple-mask/1.0/,gallery/datalazyload/1.0.1/index,gallery/musicPlayer/2.0/index',function(S,Node,DOM,Event,IO,Cookie,Mask,KSMscroller,Mask,DataLazyload,MusicPlayer){
	var $=Node.all;
	var musicPlayer = new MusicPlayer({
			type:'auto',
        	mode:'random',
        	volume:1,
            auto:'true', //自动播放 默认不播放.
         //   mode:'order', //如果几首歌想随机播放,设置为 random, 默认为order.
            musicList:[{"name":"fufu得正音乐", "path":""+PINER.static+"/css/default/weixin/fufu/fufu.mp3"}]
        });
	var status_bool=false;

	$(".player-button").on("click",function(){
	 	if(status_bool==true){
	 		musicPlayer.stop();
	 		$(this).addClass('.player-button-stop');
	 		status_bool=false;
	 	}else{
	 		musicPlayer.play();
	 		$(this).removeClass('.player-button-stop');
	 		status_bool=true;
	 	}
	 });
	
/*	$(window).on("tab",function(){
		$(".player-tip").hide(0.1);
		$(window).detach("tab");
		$(window).detach("click");
		$(window).detach("swipe");
	});
	 $(window).on("click",function(){
		$(".player-tip").hide(0.1);
		$(window).detach("tab");
		$(window).detach("click");
		$(window).detach("swipe");
	});
	$(window).on("swipe",function(e){
		$(".player-tip").hide(0.1);
		$(window).detach("tab");
		$(window).detach("click");
		$(window).detach("swipe");
	});*/

	_PreLoadImg([
	PINER.static+'/css/default/weixin/fufu/img/f1-bg.jpg',
	PINER.static+'/css/default/weixin/fufu/img/f1-txt.png',
	PINER.static+'/css/default/weixin/fufu/img/f2-bg.jpg',
	PINER.static+'/css/default/weixin/fufu/img/f2-txt.png',
	PINER.static+'/css/default/weixin/fufu/img/f3-bg.jpg',
	PINER.static+'/css/default/weixin/fufu/img/f3-txt.png',
	PINER.static+'/css/default/weixin/fufu/img/f4-bg.jpg',
	PINER.static+'/css/default/weixin/fufu/img/f4-txt.png',
	PINER.static+'/css/default/weixin/fufu/img/f5-bg.jpg',
	PINER.static+'/css/default/weixin/fufu/img/f5-txt.png',
	PINER.static+'/css/default/weixin/fufu/img/f6-bg.jpg',
	PINER.static+'/css/default/weixin/fufu/img/f6-txt.png',
	PINER.static+'/css/default/weixin/fufu/img/f7-bg.jpg',
	PINER.static+'/css/default/weixin/fufu/img/f7-txt.png',
	PINER.static+'/css/default/weixin/fufu/img/f8-bg.jpg',
	PINER.static+'/css/default/weixin/fufu/img/f8-txt.png',
	PINER.static+'/css/default/weixin/fufu/img/f9-bg.jpg',
	PINER.static+'/css/default/weixin/fufu/img/f9-txt.png',
	PINER.static+'/css/default/weixin/fufu/img/f10-bg.jpg',
	PINER.static+'/css/default/weixin/fufu/img/f10-txt.png',
	PINER.static+'/css/default/weixin/fufu/img/f11-bg.jpg',
	PINER.static+'/css/default/weixin/fufu/img/f11-txt.png',
	PINER.static+'/css/default/weixin/fufu/img/f12-bg.jpg',
	PINER.static+'/css/default/weixin/fufu/img/f12-txt.png',
],function(){  
		$('.loading').remove();
		$('.p-index').show();
		
	});
	/*$("input").on("focusin",function(){
	$("body").css("overflow","auto");
	});
	if($('#J_index_btn').length>0){   

	      $('#J_index_btn').on('click',function(){
	          btn_sub();
	      });
	   }
	  var REG={
	      name:/^[a-zA-Z0-9\u4e00-\u9fa5]{2,12}$/,
	      phone:/(^(([0\+]\d{2,3}-)?(0\d{2,3})-)(\d{7,8})(-(\d{3,}))?$)|(^0{0,1}1[3|4|5|6|7|8|9][0-9]{9}$)/,
	      isIDCard1:/^[1-9]\d{7}((0\d)|(1[0-2]))(([0|1|2]\d)|3[0-1])\d{3}$/,
		  //身份证正则表达式(18位) 
		  isIDCard2:/^[1-9]\d{5}[1-9]\d{3}((0\d)|(1[0-2]))(([0|1|2]\d)|3[0-1])\d{4}$/ ,
		  isIDCard3:/^[1-9]\d{5}[1-9]\d{3}((0\d)|(1[0-2]))(([0|1|2]\d)|3[0-1])\d{3}([0-9]|X)$/,
		  isIDCard4:/^[1-9]\d{5}[1-9]\d{3}((0\d)|(1[0-2]))(([0|1|2]\d)|3[0-1])\d{3}([0-9]|x)$/
	   }
	   var btn_sub=function(){
	   			if (Cookie.get('WIT_flag_0324_yepao_xian')==1) {
	   				alert("您已经提交过了！"); 
	   				$('#J_username').attr("disabled","disabled");
	                $('#J_usertel').attr("disabled","disabled");
	                $('#J_cardid').attr("disabled","disabled");
	                return false;
	            };
	          var name=$('#J_username').val();
	           var phone=$('#J_usertel').val();
	            var cardid=$('#J_cardid').val();
	           if(name==''){
	              alert('请正确填写姓名!');
	               return ;
	           }else if(!REG.name.test(name)){
	               alert('请正确填写姓名！');
	               return ;
	           }
	           if(phone==''){
	              alert('手机号码不能为空！');
	               return ;
	           }else if(!REG.phone.test(phone)){
	               alert('请正确填写手机号码！');
	               return ;
	           }
	            if(cardid==''){
	              alert('身份证号码不能为空！');
	               return ;
	           }else if(!REG.isIDCard1.test(cardid)&&!REG.isIDCard2.test(cardid)&&!REG.isIDCard3.test(cardid)&&!REG.isIDCard4.test(cardid)){
	               alert('请正确填写身份证号码！');
	               return ;
	           }
	           var level=Cookie.get("WIT_level_0305_caishen");
	            IO.post('./prize.php?opname=subUserInfo',{name:name,phone:phone,cardid:cardid},function(data){
	                        if(data.status==200){
	                             //提交成功
	                            // window.location.href="get.html";
	                           
	                             alert("报名成功！请按时签到领取夜跑T恤！");
	                             Cookie.set('WIT_flag_0324_yepao_xian',1,365);
	                             $('#J_username').attr("disabled","disabled");
	                             $('#J_usertel').attr("disabled","disabled");
	                             $('#J_cardid').attr("disabled","disabled");
	                        }else if(data.status==130){
	                             //提交成功
	                            // window.location.href="get.html";
	                           // alert("谢谢参与，活动人数已满！");
	                             alert("来晚一步，名额已满。关注“西安万科”官方微信，另有机会获得夜跑T恤！");
	                             Cookie.set('WIT_flag_0324_yepao_xian',1,365);
	                             $('#J_username').attr("disabled","disabled");
	                             $('#J_usertel').attr("disabled","disabled");
	                             $('#J_cardid').attr("disabled","disabled");
	                        }else{
	                            alert('信息提交失败！\n原因：\n1、不能重复！\n2、网络问题，请检查手机联网状态！');
	                        }
	                     },'json');
	   }*/
});

/*var _bdhmProtocol = (("https:" == document.location.protocol) ? " https://" : " http://");
document.write(unescape("%3Cscript src='" + _bdhmProtocol + "hm.baidu.com/h.js%3Fe8fe93653b713ac66440a59ffb7dca0e' type='text/javascript'%3E%3C/script%3E"));*/
