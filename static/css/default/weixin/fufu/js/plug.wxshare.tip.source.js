

/*
//这里是按钮事件可以使用关闭时候的回调函数
<a href="javascript:wit_wechatShare_.openMask(callbackFun)">打开分享</a>

//必须导入的js库
<script src="http://g.tbcdn.cn/kissy/k/1.4.1/seed-min.js" data-config="{combine:true}"></script>
<script src="js/wit_share.js"></script>

//回调函数可以自由编写
<script type="text/javascript">
    var callbackFun=function(){
          alert("这里是回调函数 哇哈哈！");
        }

</script>
*/
  var callback=function(){}
  var wit_wechatShare_=null;
  var wit_wechatShare=function(Mask){
      this.Mask=Mask;
      this.imgUrl='http://cdn.w-i-t.cn/13_qdvanke1212/img/img-guide.png';//默认图片可以使用 wit_wechatShare_.imgUrl修改图片路径
     
   };
KISSY.use('node,dom,event,gallery/simple-mask/1.0/',function(S,Node,DOM,Event,Mask){
   var $=Node.all;
    ShareMask = Mask({
        zIndex:999,
        opacity:0.9
    });
    
    wit_wechatShare.prototype.openMask=function(callback){
               this.Mask.removeMask();
               this.Mask.addMask();
                $('body').append('<img src="'+ this.imgUrl+'" alt="" id="J_share-tips" '+
                  'style="position: fixed;left:50%;margin-left:-140px;;top:0px;width:280px;height: auto;z-index:1000;'+
                  ' background-repeat: no-repeat;background-position:center 0px;z-index:99999999"/>');
                var self=this;
                $('#J_share-tips').on('click',function(){
                    
                     self.Mask.removeMask();
                    $(this).remove();

                   callback();
                });
          };
        
        wit_wechatShare_=new wit_wechatShare(ShareMask);

  });


