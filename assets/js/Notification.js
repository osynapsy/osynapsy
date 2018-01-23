ONotification = {
    init : function()
    {
        this.timer(this.check, 3000);
    },
    check : function()
    {
        $.ajax({
            type : 'GET',
            url  : '/notification/',
            success : ONotification.dispatch
        });
    },
    dispatch : function(resp)
    {
        $('.notificationContainer',resp).each(function(){
            var id = '#'+$(this).attr('id');            
            var cnt = $('span.count', this).text();            
            var bod = $('.lv-body', this).html();
            if (cnt == 0) {
                $('.tmn-counts', $(id)).addClass('hidden').text(cnt);
            } else {
                $('.tmn-counts', $(id)).removeClass('hidden').text(cnt);
            }
            $('.lv-body', $(id)).html(bod);            
        });
        ONotification.timer(ONotification.check, 180000);
    },
    timer : function(fnc ,intv)
    {
        setTimeout( fnc, intv);
    }
}

ONotification.init();