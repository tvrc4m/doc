;(function($){
    function VK(){

    }
    VK.prototype.ajax = function(url,type,data,before,success,complete,error) {
        var self=this;
        $.ajax({
            url:url,
            type:type,
            dataType:"json",
            data:data,
            beforeSend:function(){
                vk.loading(1);
                if(typeof(before)=='function') return before();
            },
            success:function(res){
                console.log(res)
                if(res.errno==0){
                    // 判断是否需要跳转
                    if(typeof(res.data.redirect)!='undefined' && res.data.redirect){
                        // 先文字提示后跳转
                        if(typeof(res.data.message)!='undefined' && res.data.message){
                            self.notice(res.data.message,function(){
                                self.redirect(res.data.redirect);
                            })
                        }else{
                            // 直接跳转
                            self.redirect(res.data.redirect);
                        }
                    }else if(res.data.message){
                        // 单纯弹出提示文字
                        self.notice(res.data.message);
                    }
                    if(typeof(success)=='function') return success(res.data);
                }else{
                    self.notice(res.errmsg);
                }
            },
            complete:function(){
                vk.loading(0);
                if(typeof(complete)=='function') return complete();
            },
            error:function(){
                if(typeof(error)=='function') return error();
            }
        })
    };

    VK.prototype.post = function(url,data,before,success,error) {
        this.ajax(url,'POST',data,before,success,error);
    };

    VK.prototype.loading = function(show) {
        
    };

    VK.prototype.check2radio = function(cls) {
        $(cls).on('click',function(){
            $(cls).not(this).prop("checked",null);
        })
    };

    VK.prototype.activeTab = function(cls,name) {
        $(cls).on('click',function(){
            $(this).addClass("active");
            $(cls).not(this).removeClass("active");
            if(name) $("input[name="+name+"]").val($(this).data("bind"));
        })
    };

    VK.prototype.val = function(name) {
        return $("input[name="+name+"]").val();
    };

    /**
     * 弹层
     * @param  {string} id 元素id
     * @return
     */
    VK.prototype.modal = function(id) {
        $.fancybox({
            'padding': 20,
            'autoSize': true,
            'overlayShow':true,
            'overlayOpacity':0.7,
            'autoHeight': true,
            'type': 'inline',
            'scrolling': 'no',
            'href': id
        });
    };

    VK.prototype.close = function() {
        $.fancybox.close();
    };
    /**
     * notice提示
     * @param  {string}   message  提示消息
     * @param  {Function} callback 提示消失之后要调用的方法(如果需要的话)
     * @return
     */
    VK.prototype.notice = function(message,callback) {
        $.fancybox(message, {
            minWidth:"200",
            minHeight:"auto",
            margin:10,
            padding:10,
            closeBtn:false,
            helpers:{overlay:false},
            wrapCSS:"fancybox-notice",
            afterShow:function () {
                window.setTimeout(function () {
                    $.fancybox.close(); 
                    typeof(callback)=='function' && callback();
                }, 1200); 
            }
        });
    };

    VK.prototype.parse = function(target,templateid,data) {
        $(target).append(template(templateid,data));
    };

    /**
     * url跳转
     * @param  {string} url 跳转链接 
     * @return 
     */
    VK.prototype.redirect = function(url) {
        document.location.href=url;
    };

    VK.prototype.check_empty = function(name,message) {
        if(!name || !name.trim().length){
            this.notice(message);
            return true;
        }
        return false;
    };
    
    window.vk=new VK();

})(jQuery);