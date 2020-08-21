var Osynapsy = new (function(){

    var pub = {
        kernel : {},
        history : {},
        plugin : {}
    };

    pub.createElement = function (tag, attributes)
    {
        let element = document.createElement(tag);
        for (let id in attributes) {
            element[id] = attributes[id];
        }
        return element;
    };

    pub.ajax = {
        execute : function(options)
        {
            let request = new XMLHttpRequest();
            if ('progress' in options) {
                request.addEventListener("progress", options.progress);
            }
            if ('success' in options) {
                request.addEventListener("load",  options.success);
            }
            if ('error' in options) {
                request.addEventListener("error", options.error);
            }
            if ('abort' in options) {
                request.addEventListener("abort", options.abort);
            }
            request.open(
                'method' in options ? options.method : 'get',
                'url' in options ? options.url : window.location
            );
            if ('headers' in options) {
                for (let header in options.headers) {
                    request.setRequestHeader(header, options.headers[header]);
                }
            }
            if ('beforeSend' in options) {
                options.beforeSend();
            }
            request.send('data' in options ? options.data : null);
        },
        get : function(url, options)
        {
            this.exec('get', url, options);
        },
        post : function(url, options)
        {
            this.exec('post', url, options);
        }
    };

    pub.action =
    {
        parametersFactory : function(object)
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
            this.source = object;
            this.remoteExecute(action, form, this.parametersFactory(object));
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
                success : function(response) {
                    Osynapsy.waitMask.remove();
                    Osynapsy.kernel.message.dispatch(response, this);
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
                    progress : Osynapsy.waitMask.uploadProgress,
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
        },
        source : null
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
            var form = object.closest('form');
            if (!Osynapsy.isEmpty(form[0].getAttribute('action'))) {
                actionUrl =  form[0].getAttribute('action');
            }
            var formData = new FormData(form[0]);
            formData.append('actionParameters[]', object[0].getAttribute('id') + event);
            let response = fetch(actionUrl, {
                method: 'post',
                headers: {
                    'Osynapsy-Action': 'dispatchLocalEvent',
                    'Accept': 'application/json'
                },
                body: formData
            });
            response.then(response => response.json())
            .then(function (data) {
                Osynapsy.waitMask.remove();
                Osynapsy.kernel.message.dispatch(data);
            })
            .catch(function (error) {
                Osynapsy.waitMask.remove();
                console.log(error);
                alert(error);
            });
        }
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
            if (typeof $().modal === 'function') {
		pub.modal.alert(
                    'Si sono verificati i seguenti errori',
                    '<ul><li>' + errors.join('</li><li>') +'</li></ul>'
		);
		return;
            }
            alert('Si sono verificati i seguenti errori : \n' + errors.join("\n").replace(/(<([^>]+)>)/ig,""));
        },
        dispatchCommands : function(response)
        {
            if (!('command' in response)) {
                return;
            }
            $.each(response.command, function(idx, val){
                if (val[0] in FormController) {
                    FormController[val[0]](val[1]);
                } else if (val[0] in Osynapsy) {
                    Osynapsy[val[0]](val[1]);
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

    pub.goto = function(url)
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
    };

    pub.modal =
    {        
        remove : function()
        {
            $('.modal').remove();
        },
        alert : function(title, message, actionConfirm, actionCancel){
            Osynapsy.modal.remove();
            Osynapsy.include('Modal.js', function() {
                modalAlert(title, message, actionConfirm, actionCancel);
            });
        },
        confirm : function(object)
        {            
            Osynapsy.modal.remove();
            Osynapsy.include('Modal.js', function() {
                modalAlert('Conferma', object.data('confirm'), object.data('action'));
            });
        },
        window : function(title, url, width, height)
        {
            Osynapsy.modal.remove(); 
            Osynapsy.include('Modal.js', function() { modalWindow(title, url, width, height); });
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
                        Osynapsy.action.execute(this);
                        return false;
                    break;
                }
            }).on('click','.cmd-back',function(){
                Osynapsy.history.back();
            }).on('click','.save-history',function(){
                Osynapsy.history.save();
            }).on('click','a.open-modal',function(e){
                e.preventDefault();
                Osynapsy.modal.window(
                    this.getAttribute('title'), 
                    this.classList.contains('.postdata') ? [this.getAttribute('href'), this.closest('form')] : this.getAttribute('href'),
                    this.getAttribute('modal-width'),
                    this.getAttribute('modal-height')                        
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

    pub.post = function(url, values)
    {
        var form = Osynapsy.createElement('form', {'action' : url, 'method' : 'post'});
        if (!Osynapsy.isEmpty(values)) {
            for (var idx in values) {
                form.appendChild(Osynapsy.createElement('input', {
                    'type' : 'hidden',
                    'name' : values[idx][0],
                    'value' : values[idx][1]
                }));
            }
        }
        document.body.appendChild(form);
        form.submit();
    };

    pub.refreshComponents = function(components)
    {
        var componentsIDs = Array.isArray(components) ? components : [components];
        var execOnSuccess = arguments.length > 1 ? arguments[1] : null;
        if (componentsIDs.length === 1) {
            Osynapsy.waitMask.show(document.getElementById(componentsIDs[0]));
        }
        let form = document.querySelector('form');
        let response = fetch(window.location.href, {
            body: new FormData(form),
            method: 'post',
            headers: {
                'Osynapsy-Html-Components': componentsIDs.join(';'),
                'Accept': 'text/html'
            }
        });
        response.then(response => response.text()).then(function(strHtmlPage) {
            Osynapsy.waitMask.remove();
            let parser = new DOMParser();
            let remoteDoc = parser.parseFromString(strHtmlPage, 'text/html');
            for (var i in componentsIDs) {
                let componentId = componentsIDs[i];
                let remoteComponent = remoteDoc.getElementById(componentId);
                if (remoteComponent) {
                    document.getElementById(componentId).replaceWith(remoteComponent);
                }
            }
            if (typeof execOnSuccess === 'function') {
                execOnSuccess();
            }
        }).catch(function(error){
            Osynapsy.waitMask.remove();
            console.log(error);
        });
    };

    pub.waitMask =
    {
        build : function(message, parent, position)
        {
            var mask = $('<div id="waitMask" class="wait"><div class="message">'+message+'</div></div>');
            mask.width($(parent).width()).height($(parent).height());
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
        uploadProgress : function(a) {
            if ($('#progress_idx').length > 0){
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

    pub.include = function(uri, onload)
    {        
        if (document.getElementById(uri)) {            
            return onload();
        }        
        let rootOsynapsyJs = document.getElementById('osynapsyjs').src.split('/');
        rootOsynapsyJs[rootOsynapsyJs.length - 1] = uri;        
        document.body.appendChild(this.createElement('script', {
            'id' : uri,
            'src' : rootOsynapsyJs.join('/'), 
            'onload' : onload
        }));
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
    execute  : function(object)
    {
        Osynapsy.action.execute(object);
    },
    execCode : function(code)
    {
        if (Osynapsy.action.source) {
            var self = Osynapsy.action.source;
        }
        eval(code.replace(/(\r\n|\n|\r)/gm,""));
    },
    observe : function(target, fnc){
        var observer = new MutationObserver(fnc);
        if (!(target instanceof Array)) {
            target = [target];
        }
        for (var i in target) {
            observer.observe(target[i], {attributes: true});
        }
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
    }
};

document.addEventListener("DOMContentLoaded", function() {
    Osynapsy.page.init();
});