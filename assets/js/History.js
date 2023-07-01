var Osynapsy = Osynapsy || {};

Osynapsy.History =
{
    save : function()
    {
        var hst = [];
        var arr = [];
        if (sessionStorage.history){
            hst = JSON.parse(sessionStorage.history);
        }
        $('input,select,textarea').not('.history-skip').each(function(){
            switch ($(this).attr('type')) {
                case 'submit':
                case 'button':
                case 'file':
                    return true;
                case 'checkbox':
                    if (!$(this).is(':checked')) {
                        return true;
                    }
                    break;
            }
            if ($(this).attr('name')) {
                arr.push([$(this).attr('name'), $(this).val()]);
            }
        });
        hst.push({url : window.location.href, parameters : arr});
        sessionStorage.history = JSON.stringify(hst);
    },
    back : function()
    {
        if (!sessionStorage.history) {
            history.back();
        }
        var hst = JSON.parse(sessionStorage.history);
        var stp = hst.pop();
        if (Osynapsy.isEmpty(stp)) {
            history.back();
            return;
        }
        sessionStorage.history = JSON.stringify(hst);
        Osynapsy.post(stp.url, stp.parameters);
    },
    popLastStep : function()
    {
        if (!sessionStorage.history) {
            return;
        }
        let history = JSON.parse(sessionStorage.history);
        let lastUri = history.pop();
        sessionStorage.history = JSON.stringify(history);
        return lastUri;
    }
};
