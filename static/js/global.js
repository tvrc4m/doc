;(function($){
    function VK(){

    }
    VK.prototype.ajax = function(url,type,data,before,success,error) {
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
            success:function(data){
                console.log(data)
                if(data.errno==0){
                    if(typeof(success)=='function') return success(data);
                }else{
                    self.notice(data.errmsg);
                }
            },
            complete:function(){
                vk.loading(0);
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

    VK.prototype.check_empty = function(name,message) {
        if(!name || !name.trim().length){
            this.notice(message);
            return true;
        }
        return false;
    };
    
    window.vk=new VK();

})(jQuery);