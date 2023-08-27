var Osynapsy = Osynapsy || {'notification' : {}};

Osynapsy.notification = function(message)
{
    // Controlliamo se il browser supporta le notifiche
    if (!("Notification" in window)) {
        console.log("Notification API isn't supported from this browser");
        return;
    }
    switch(Notification.permission) {
        case 'denied':
            return;
        case 'granted':
            var notification = new Notification(message);
            return;
        default:
            // Se l'utente non ha accettato le notifiche, chiediamo il permesso
            Notification.requestPermission(function (permission) {
                // Se è tutto a posto, creiamo una notifica
                if (permission === "granted") {
                    Osynapsy.notification(message);
                }
            });
            break;
    }
};

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
