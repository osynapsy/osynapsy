var Osynapsy = new (function(){
    
    var pub = {
        kernel : {},
        history : {},
        plugin : {}
    };
    
    pub.event = 
    {
        dispatch : function(source, event)
        {
            if (Osynapsy.isEmpty($(source).attr('id'))) {
                return;
            }
            this.dispatchRemote($(source), event);
        },
        dispatchRemote : function(object, event)
        {
            var actionUrl = window.location.href;
            if (Osynapsy.isEmpty($(object).closest('form').attr('action'))) {
                actionUrl =  $(object).closest('form').attr('action');
            } 
            $.ajax({
                data : $(object).closest('form').serialize()+'&actionParameters[]=' + $(object).attr('id') + event,
                url  : actionUrl,
                headers: {
                    'Osynapsy-Action': 'dispatchLocalEvent',                    
                    'Accept': 'application/json'
                },
                type : 'post',
                dataType : 'json',
                success : function(response){            
                    Osynapsy.waitMask.remove();
                    Osynapsy.kernel.message.dispatch(response);
                },
                error: function(xhr, status, error) {                
                    Osynapsy.waitMask.remove();
                    console.log(status);
                    console.log(error);
                    console.log(xhr);
                    alert(xhr.responseText);
                }
            });
        }
    };
    
    pub.action = 
    {
        execute : function(object)
        {
            var form = $(object).closest('form');
            var action = $(object).data('action');        
            if (Osynapsy.isEmpty(action)) {
                alert('Attribute data-action don\'t set.');
                return;
            }
            if (!Osynapsy.isEmpty($(object).data('confirm'))) {
                if (!confirm($(object).data('confirm'))) {
                    return;
                }   
            }          
            this.remoteExecute(action, form, this.grabActionParameters(object));
        },
        grabActionParameters : function(object)
        {
            if (Osynapsy.isEmpty($(object).data('action-parameters'))) {
                return false;
            }
            var values = [];        
            var params = String($(object).data('action-parameters')).split(',');
            for (var i in params) {
                var value = params[i];
                if (value === 'this.value'){
                    value = $(object).val();
                } else if (value.charAt(0) === '#' && $(value).length > 0) {
                    value = $(value).val();
                } 
                values.push('actionParameters[]=' + encodeURIComponent(value));
            }
            return values.join('&');
        },        
        remoteExecute : function(action, form, actionParameters)
        {
            var extraData = Osynapsy.isEmpty(actionParameters) ? '' : actionParameters;
            var actionUrl = Osynapsy.isEmpty($(form).attr('action')) ? window.location.href : $(form).attr('action');
            $('.field-in-error').removeClass('field-in-error');
            var callParameters = {
                url  : actionUrl,
                headers: {
                    'Osynapsy-Action': action,
                    'Accept': 'application/json'
                },
                type : 'post',
                dataType : 'json',
                success : function(response){            
                    Osynapsy.waitMask.remove();
                    Osynapsy.kernel.message.dispatch(response);
                },
                error: function(xhr, status, error) {                
                    Osynapsy.waitMask.remove();
                    console.log(status);
                    console.log(error);
                    console.log(xhr);
                    alert(xhr.responseText);
                }
            };            
            if (!this.checkForUpload()) {               
                var options = {
                    beforeSend : function() {
                        Osynapsy.waitMask.show();
                    },
                    data : $(form).serialize()+'&'+extraData
                };
            } else {                
                var options  = {
                    beforeSend : function() {
                        Osynapsy.waitMask.showProgress();
                    },
                    xhr : function(){  // Custom XMLHttpRequest
                        var xhr = $.ajaxSettings.xhr();
                        if(xhr.upload) { // Check if upload property exists
                            xhr.upload.addEventListener('progress',Osynapsy.waitMask.uploadProgress, false); // For handling the progress of the upload
                        }
                        return xhr;
                    },
                    //Se devo effettuare un upload personalizzo il metodo jquery $.ajax per fargli spedire il FormData
                    data :  new FormData($(form)[0]),
                    mimeType : "multipart/form-data",
                    contentType : false,
                    cache : false,
                    processData :false
                };        
            }
            $.extend(callParameters, options);
            $.ajax(callParameters);
        },
        checkForUpload : function()
        {
            if (!window.FormData){            
                return false; //No file to upload or IE9,IE8,etc browser
            }
            var upload = false;
            $('input[type=file]').each(function(){
                //Carico il metodo per effettuare l'upload solo se c'è almeno un campo file pieno
                if (!Osynapsy.isEmpty($(this).val())) {
                    upload = true; 
                    return false ;
                }
            });
            return upload;        
        }
    };
    
    pub.appendToUrl = function(value)
    {
        window.history.pushState(null, null, value);
    };
    
    pub.coalesce = function()
    {
        if (arguments.length === 0) {
            return null;
        }
        for (var i in arguments) {
            if (!Osynapsy.isEmpty(arguments[i])) {
                return arguments[i];
            }
        }
        return null;
    };
    
    pub.hashCode = function(string)
    {
        var hash = 0, i, chr;
        if (string.length === 0) {
            return hash;
        }
        for (i = 0; i < string.length; i++) {
            chr   = string.charCodeAt(i);
            hash  = ((hash << 5) - hash) + chr;
            hash |= 0; // Convert to 32bit integer
        }
        return hash;
    };
    
    pub.history = 
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
        }
    };
    
    pub.isEmpty = function (value)
    {
        if (typeof value === 'undefined') {
            return true;
        }
        switch(value) {
            case []:                
            case {}:                
            case null:                
            case '':            
            case false:
                return true;
            default:
                return false;
        }
    };
    
    pub.isObject = function(v)
    {
        return v instanceof Object;
    };
    
    pub.notification = function(message)
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
    
    pub.addWorker = function(name, url)
    {
        if (!window.Worker) {
            console.log('questo browser non supporta i worker');
        }
        var myWorker = new SharedWorker(url);
                
        // Get the proxy worker port for communication
        var myWorkerPort = myWorker.port;
        // Send a "hello" message to the worker
        myWorkerPort.postMessage( {type: 'hello', says: 'Hello worker !'} );        
        myWorkerPort.onmessage = function( event )
        {
            var message = event.data;
            Osynapsy.notification(message.says);
        };
    };
    
    pub.typingEvent = function(obj)
    {
        if (pub.typingTimeout !== undefined) {
            clearTimeout(pub.typingTimeout);
        }
        pub.typingTimeout = setTimeout(function(){ 
            var code = $(obj).attr('ontyping');
            if (code) {
                eval(code);
            }
        }, 500);  
    };
    
    pub.kernel.message = 
    {
        response : null,
        dispatch : function (response)
        {
            this.response = response;
            if (!Osynapsy.isObject(this.response)){
                console.log('Resp is not an object : ', this.response);
                return;
            }       
            this.dispatchErrors(this.response);
            this.dispatchCommands(this.response);
        },
        dispatchErrors : function(response)
        {
            if (!('errors' in response)){
                return;
            }
            var errors = [];
            var self = this;
            $.each(response.errors, function(idx, val){
                if (val[0] === 'alert'){
                    alert(val[1]);
                    return true;
                }
                var cmp = $('#'+val[0]);
                if ($(cmp).hasClass('field-in-error')){
                    return true;                
                }
                if ($(cmp).length > 0) {
                    $(cmp).addClass('field-in-error').on('change', function() { $(this).removeClass('field-in-error'); });                    
                }
                errors.push(cmp.length > 0 ? self.showErrorOnLabel(cmp, val[1]) : val[1]);
            });
            if (errors.length === 0) {
                return;
            }
            pub.modal.show(
                'Si sono verificati i seguenti errori',
                '<ul><li>' + errors.join('</li><li>') +'</li></ul>'
            );
        },
        dispatchCommands : function(response)
        {
            if (!('command' in response)) {
                return;
            }
            $.each(response.command, function(idx, val){            
                if (val[0] in FormController) {
                    FormController[val[0]](val[1]);
                }
            });
        },
        showErrorOnLabel : function(elm, err)
        {
            /*if ($(elm).data('label')) {
                return err.replace('<!--'+$(elm).attr('id')+'-->',$(elm).data('label')) + '\n';
            }*/
            if ($(elm).closest('[data-label]').length > 0) {
                return err.replace('<!--'+$(elm).attr('id')+'-->', '<strong>' + $(elm).closest('[data-label]').data('label') + '</strong>');
            }
            return err.replace('<!--'+$(elm).attr('id')+'-->', '<i>'+ $(elm).attr('id') +'</i>');
            var par = elm.closest('.form-group');
            if (par.hasClass('has-error')) {
                return;
            }
            par.addClass('has-error');
            $('label',par).append(' <span class="error">'+ err +'</span>');
            elm.change(function(){
                var par = $(this).closest('.form-group');
                $('span.error',par).remove();
                par.removeClass('has-error');
            });
        }
    };
    
    pub.modal = 
    {
        build : function(id, title, body, actionConfirm, actionCancel)
        {
            this.remove();
            var btnCloseClass = '';
            var win  = '<div id="' + id + '" class="modal fade" role="dialog">\n';
                win += '    <div class="modal-dialog modal-xs">\n';
                win += '        <div class="modal-content">\n';
                win += '            <div class="modal-header bg-light">\n';
                win += '                <h5 class="modal-title">' + title + '</h5>';   
                win += '                <button type="button" class="close" data-dismiss="modal">&times;</button>';               
                win += '            </div>';
                win += '            <div class="modal-body" style="padding: 20px">';
                win += body;
                win += '            </div>';
                win += '            <div class="modal-footer">';
                if (!Osynapsy.isEmpty(actionConfirm)) {
                    var action = actionConfirm.replace(')','').split('(');
                    btnCloseClass = ' pull-left';
                    win += '<button type="button" class="btn btn-default click-execute pull-right" data-dismiss="modal" data-action="'+ action[0] +'" data-action-parameters="' + (action[1] === 'undefined' ? '' : action[1]) +'">Conferma</button>';
                }
                if (!Osynapsy.isEmpty(actionCancel)) {
                    win += '<button type="button" class="btn btn-secondary btn-default'+btnCloseClass+' click-execute" data-action="'+ actionCancel +'" data-dismiss="modal">Annulla</button>';
                } else {
                    win += '<button type="button" class="btn btn-secondary btn-default'+btnCloseClass+'" data-dismiss="modal">Annulla</button>';
                }
                win += '            </div>';
                win += '        </div>';
                win += '    </div>';
                win += '</div>';
            $('body').append($(win));
            $('#'+id).modal({
                keyboard : true
            });
            return $(win);
        },
        remove : function()
        {
            $('.modal').remove();
        },
        show : function(title, message, actionConfirm, actionCancel){
            if (!title) { title = 'Alert'; }
            var modalId = actionConfirm !== null ? 'alert' : 'confirm';
            return this.build(modalId, title, message, actionConfirm, actionCancel);
        },
        confirm : function(object)
        {
            return this.build('confirm','Confirm',object.data('confirm'), object.data('action'));
        }
    };
    
    pub.page = {
        init : function()
        {
            Osynapsy.setParentModalTitle();
            $('body').on('change','.change-execute, .onchange-execute', function(){
                Osynapsy.action.execute(this);
            }).on('click','.cmd-execute, .click-execute, .onclick-execute',function(event) {
                //event.stopPropagation();
                Osynapsy.action.execute(this);
            }).on('keydown','.onenter-execute',function(event){
                event.stopPropagation();                
                switch (event.keyCode) {
                    case 13 : //Enter
                    case 9:                                
                        FormController.execute(this);
                        return false;
                    break;                             
                }
            }).on('click','.cmd-back',function(){        
                Osynapsy.history.back();
            }).on('click','.save-history',function(){
                Osynapsy.history.save();
            }).on('click','a.open-modal',function(e){
                e.preventDefault();            
                FormController.modalWindow(
                    'amodal', 
                    $(this).attr('title'), 
                    $(this).is('.postdata') ? [$(this).attr('href'), $(this).closest('form')] : $(this).attr('href'), 
                    $(this).attr('modal-width') ? $(this).attr('modal-width') : '75%',
                    $(this).attr('modal-height') ? $(this).attr('modal-height') : ($(window).innerHeight() - 250) + 'px',
                    $(this).attr('modal-dimension')
                );
            }).on('keyup', '.typing-execute', function(){                
               Osynapsy.typingEvent(this);
            }).on('click change', '.dispatch-event', function(ev){
                var eventClass = 'dispatch-event-' + ev.type;
                if ($(this).hasClass(eventClass)) {
                    Osynapsy.event.dispatch(this, event.type.charAt(0).toUpperCase() + event.type.slice(1));
                }                
            });
            FormController.fire('init');
        }
    };
    
    pub.post = function(url, vars)
    {
        var form = $('<form method="post" action="'+url+'"></form>');
        if (!Osynapsy.isEmpty(vars)) {
            for (var i in vars) {
                $('<input type="hidden" name="'+vars[i][0]+'" value="'+vars[i][1]+'">').appendTo(form);
            }
        }
        $('body').append(form);
        form.submit();
    };
    
    pub.refreshComponents = function(components)
    {        
        var cmps = Array.isArray(components) ? components : [components];       
        var data  = $('form').serialize();
            data += (arguments.length > 1 && arguments[1]) ? '&'+arguments[1] : '';
        var fncOnSuccess = arguments.length > 2 ? arguments[2] : null;
        if (cmps.length === 1) {            
            Osynapsy.waitMask.show($('#' + cmps[0]));            
        } else if ($(components).is(':visible')) {           
            Osynapsy.waitMask.show();
        }
        for (var i in cmps) {
            data += '&ajax[]=' + cmps[i];
        }
        $.ajax({
            url  : window.location.href,
            type : 'post',
            data : data,
            dataType : 'html',
            headers: {
                'Osynapsy-Html-Components': cmps.join(';'),
                'Accept': 'text/html'
            },
            success : function(response)
            {
                Osynapsy.waitMask.remove();
                var successRefresh = false;
                for (var i in cmps) {
                    var componentID = '#'+ cmps[i];
                    var componentRemote = $(response).find(componentID);                    
                    if (componentRemote) {                        
                        $(componentID).replaceWith(componentRemote);
                        successRefresh = true;
                    }                    
                }
                if (!successRefresh){
                    console.log(response);
                } else if (typeof fncOnSuccess === 'function') {                    
                    fncOnSuccess();
                }
            },
            error : function(response)
            {
                Osynapsy.waitMask.remove();
                console.log(response);
            }
        });
    };
    
    pub.waitMask = 
    {    
        build : function(message, parent, position)
        {                        
            var mask = $('<div id="waitMask" class="wait"><div class="message">'+message+'</div></div>');
            mask.width($(parent).width())
                .height($(parent).height());
            if (position) {
                mask.css('top', position.top+'px').css('left',position.left+'px');
            }
            $('body').append(mask);
        },
        show : function()
        {        
            var message = 'PLEASE WAIT <span class="fa fa-refresh fa-spin"></span>';
            var position = {top : '0px', left : '0px'};
            var parent = document;
            if (arguments.length > 0) {
                parent = arguments[0];
                position = $(parent).offset();                
            }            
            this.build(message, parent, position);
        },
        showProgress : function()
        {
            var message = '';
            message += '<div class="progress_msg">Upload in progress .... <span id="progress_idx">0%</span> completed</div>';
            message += '<div class="progress"><div id="progress_bar" style="background-color: #ceddef; width: 0%;">&nbsp;</div></div>';
            this.build(message, document, {top : '0px', left : '0px'})
        },
        remove : function()
        {        
            $('#waitMask').remove();     
        },
        uploadProgress : function(a){
            console.log(a);
            if ($('#progress_idx').length > 0){
                //if (console) console.log(a);
                var pos = a.loaded ? a.loaded : a.position;
                var t = Math.round((pos / a.total) * 100);
                $('#progress_bar').css('width',t +'%');
                $('#progress_idx').text(t +'%');
            }
        }
    };
    
    pub.setParentModalTitle = function()
    {
        if (!window.frameElement) {
            return;
        }
        parent.$('.modal-title', parent.$('#amodal')).html(document.title);
    };
    
    return pub;
});

var FormController = 
{
    repo : 
    {
        event : { init : {} },
        componentInit : {}
    },
    init : function()
    {
        Osynapsy.page.init();
    },
    back : function()
    {
        Osynapsy.history.back();
    },    
    fire : function(evt)
    {
        if (evt in this.repo['event']){
            for (var i in this.repo['event'][evt] ){
                try{
                    this.repo['event'][evt][i]();
                } catch(err) {                    
                    console.log(err);
                }
            }
        }
    },
    goto : function(url, par)
    {
        switch(url) {
            case 'refresh':
            case 'reload' :
                location.reload(true);
                break;
            case 'back'   :
                Osynapsy.history.back();
                break;
            default :
                window.location = url;
                break;
        }
    },    
    execute  : function(object)
    {
        Osynapsy.action.execute(object);
    },
    execCode : function(code)
    {
        eval(code.replace(/(\r\n|\n|\r)/gm,""));
    },
    observe : function(target, fnc){
        var observer = new MutationObserver(fnc);
        if (!(target instanceof Array)) {
            target = [target];
        }
        for (i in target ) {
            observer.observe(target[i], {attributes: true});
        }
    },
    refreshComponent : function(component)
    {        
        var cmps = Array.isArray(component) ? component : [component];
        var data  = $('form').serialize();
            data += (arguments.length > 1 && arguments[1]) ? '&'+arguments[1] : '';
        if (!(typeof component === 'object')) {            
            Osynapsy.waitMask.show(component);            
        } else if ($(component).is(':visible')) {           
            Osynapsy.waitMask.show();
        }
        for (var i in cmps) {
            data += '&ajax[]=' + $(cmps[i]).attr('id');        
        }
        $.ajax({
            url  : window.location.href,
            type : 'post',
            data : data,
            success : function(rsp) {                      
                Osynapsy.waitMask.remove();
                for (var i in cmps) {
                   var cid = '#'+ $(cmps[i]).attr('id');
                   var cmp = $(rsp).find(cid);                
                   $(cid).replaceWith(cmp);                
                }
            }
        });
    },
    register : function(evt,lbl,fnc)
    {
        this.repo['event'][evt][lbl] = fnc;
    },    
    setValue : function(k,v)
    {
        if ($('#'+k).length > 0){
            $('#'+k).val(v);
        }
    },
    modal : function(id, title, body, actionConfirm, actionCancel)
    {
        $('.modal').remove();
        var btnCloseClass = '';
        var win  = '<div id="' + id + '" class="modal fade" role="dialog">\n';
            win += '    <div class="modal-dialog modal-xs">\n';
            win += '        <div class="modal-content">\n';
            win += '            <div class="modal-header">\n';
            win += '                <button type="button" class="close" data-dismiss="modal">&times;</button>';
            win += '                <h4 class="modal-title">' + title + '</h4>';
            win += '            </div>';
            win += '            <div class="modal-body" style="padding: 20px">';
            win += body;
            win += '            </div>';
            win += '            <div class="modal-footer">';
            if (actionConfirm) {
                var action = actionConfirm.replace(')','').split('(');
                btnCloseClass = ' pull-left';
                win += '<button type="button" class="btn btn-default click-execute pull-right" data-dismiss="modal" data-action="'+ action[0] +'" data-action-parameters="' + (action[1] === 'undefined' ? '' : action[1]) +'">Conferma</button>';
            }
            if (actionCancel) {
                win += '<button type="button" class="btn btn-light btn-default'+btnCloseClass+' click-execute" data-action="'+ actionCancel +'" data-dismiss="modal">Annulla</button>';
            } else {
                win += '<button type="button" class="btn btn-light btn-default'+btnCloseClass+'" data-dismiss="modal">Annulla</button>';
            }
            win += '            </div>';
            win += '        </div>';
            win += '    </div>';
            win += '</div>';
        $('body').append($(win));
        $('#'+id).modal({
            keyboard : true
        });
        return $(win);
    },
    modalAlert : function(title, message)
    {
        if (!title) {
            title = 'Alert';
        }
        var win = this.modal('alert', title, message, null, null);        
        return $(win);
    },
    modalConfirm : function(title, message, actionConfirm, actionCancel)
    {
        if (!title) {
            title = 'Conferm';
        }
        return this.modal('confirm', title, message, actionConfirm, actionCancel);
    },
    modalWindow : function(id, title, url)
    {               
        $('.modal').remove();
        var modalHeight = Osynapsy.isEmpty(arguments[4]) ? ($(window).innerHeight() - 250) + 'px' : arguments[4];
        var modalWidth  = Osynapsy.isEmpty(arguments[3]) ? null : arguments[3];        
        if ($.isArray(url)) {
            var form = url[1];
            url = url[0];
        }        
        var modalHtml  = '<div id="' + id + '" class="modal fade" role="dialog">\n';
            modalHtml += '    <div class="modal-dialog modal-lg">\n';
            modalHtml += '        <div class="modal-content">\n';
            modalHtml += '            <div class="modal-header bg-light">\n';            
            modalHtml += '                <h5 class="modal-title">' + title + '</h5>';
            modalHtml += '                <button type="button" class="close" data-dismiss="modal">&times;</button>';
            modalHtml += '            </div>';
            modalHtml += '            <div class="modal-body">';
            modalHtml += '                <i class="fa fa-spinner fa-spin" style="font-size:24px; position:absolute; margin-top:20px; margin-left: 20px; color:silver;"></i>';
            modalHtml += '                <iframe onload="$(this).css(\'visibility\',\'\');" name="'+id+'" style="visibility:hidden; width: 100%; height:'+ modalHeight +'; border: 0px; border-radius: 3px;" border="0"></iframe>';
            modalHtml += '            </div>';            
            modalHtml += '        </div>';
            modalHtml += '    </div>';
            modalHtml += '</div>';
        var modalWindow = $(modalHtml);        
        if (!Osynapsy.isEmpty(modalWidth) && window.screen.availWidth > 1000) {            
            $('.modal-dialog', modalWindow).css('max-width', modalWidth);                            
        }
        $('body').append(modalWindow);
        //$('iframe', '#'+id).on('load', function(){});
        if (Osynapsy.isEmpty(form)) {
            $('iframe', '#'+id).attr('src',url);            
        } else {
            var action = form.attr('action');
            var target = form.attr('target');
            var method = form.attr('method');
            form.attr('action', url);
            form.attr('target', id);
            form.attr('method', 'POST');
            form.submit();
            console.log(action, target, method);
            form.attr('action', action?action:'');
            form.attr('target', target?target:'');
            form.attr('method', method?method:'');
        }
        $('iframe', '#'+id).on('load', function(){
            $(this).prev().hide();
        });
        $('#'+id).modal({keyboard : true});        
        return modalWindow;
    }
};

$(document).ready(function(){
    FormController.init();
});
