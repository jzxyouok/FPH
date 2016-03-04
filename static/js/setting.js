/**
 * @name 个人中心
 */
;(function(){
    $.pinphp.setting = {
        init: function(){
            $.pinphp.setting.basic();
            $.pinphp.setting.basic_store();
            $.pinphp.setting.password();
            $.pinphp.setting.cover();
            $.pinphp.setting.address();
            $.pinphp.setting.message_list();
            $.pinphp.setting.message();
            $.pinphp.setting.target();           
        },
        //上传头像
        basic: function(){			
            $('#J_birthday')[0] && Calendar.setup({
                inputField : "J_birthday",
                ifFormat   : "%Y-%m-%d",
                showsTime  : false,
                timeFormat : "24"
            });
            $('#J_upload_avatar').uploader({

                action_url: PINER.root + '/?g=weixin&m=user&a=upload_avatar',
                input_name: 'avatar',
				onSubmit: function(id, fileName){
					$('#J_avatar').attr('src', PINER.static + '/css/default/weixin/images/upload.gif');				
				},
                onComplete: function(id, fileName, result){
                    if(result.status == '1'){
                        $('#J_avatar').attr('src', result.data);
                    }
                }
            });
        },
        //门店形象图片
        basic_store : function(){         
            $('#J_upload_store').uploader({
                //var store_id = $('#store_id').val();
                action_url: PINER.root + '/?g=m&m=stores&a=upload_avatar&id='+$('#store_id').val(),
                input_name: 'avatar',
                onSubmit: function(id, fileName){
                    $('#J_store_avatar').attr('src', PINER.static + '/css/default/weixin/images/upload.gif');             
                },
                onComplete: function(id, fileName, result){
                    if(result.status == '1'){
                        $('#J_store_avatar').attr('src', result.data);
                    }
                }
            });
        },
        //自定义封面
        cover: function(){
            $('#upload_cover').uploader({
                action_url: PINER.root + '/?m=user&a=upload_cover',
                input_name: 'cover',
                onSubmit: function(id, fileName){
                    $.pinphp.tip({content:lang.uploading_cover, time:0,  icon:'wait'});
                },
                onComplete: function(id, fileName, result){
                    if(result.status == '1'){
                        $.pinphp.tip({close:true});
                        $('#J_cover_img').html(result.data);
                    }
                }
            });
            //取消图片
            $('#J_cancle_cover').live('click', function(){
                $.getJSON(PINER.root + '/?m=user&a=cancle_cover', function(result){
                    if(result.status == '1'){
                        window.location.reload();
                    }
                });
            });
        },
        
        
        //搜索用户
        target: function(){
            $('#J_search_target').live('click', function(){
                var search_uname = $('#J_search_uname').val();
                $.ajax({
                    url: PINER.root + '/?m=message&a=search_target',
                    type: 'POST',
                    data: {
                        search_uname: search_uname
                    },
                    dataType: 'json',
                    success: function(result){
                        if(result.status == 1){
                            //列表动态添加
                            $('#J_search_list').html(result.data);
                        }
                    }
                });
            });
        }
    };
    $.pinphp.setting.init();
})(jQuery);