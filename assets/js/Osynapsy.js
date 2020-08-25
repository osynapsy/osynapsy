var Osynapsy = new (function(){

    var pub = {modal : {}, action : {}};

    pub.createElement = function (tag, attributes)
    {
        let element = document.createElement(tag);
        for (let id in attributes) {
            element[id] = attributes[id];
        }
        return element;
    };

    pub.ajax = 
    {
        execute : function(options)
        {
            let request = new XMLHttpRequest();
            if (!('headers' in options)) {
                options['headers'] = {};
            }            
            if (!('type' in options)) {
                options['type'] = 'get';
            }
            if (!('url' in options)) {
                options['url'] = window.location;
            }
            if (!('data' in options)) {
                options['data'] = null;
            }
            if ('uploadProgress' in options) {
                if (request.upload) {
                    request.upload.addEventListener("progress", options.uploadProgress, false);                    
                } else {
                    if (console.log) console.log('Borwser not support upload progress');
                }
            }
            if ('success' in options) {
                request.addEventListener("load",  function(event) {                    
                    try {
                        let data = event.target.responseText;
                        switch(options.dataType) {
                            case 'json':
                                data = JSON.parse(event.target.responseText);
                                break;                                                            
                        }                        
                        options.success(data);
                    } catch (err) {
                        options.error(event.target, 'error', event.target.responseText);
                    }
                });
            }
            if ('progress' in options) {
                request.addEventListener("progress", options.progress);
            }
            if ('error' in options) {
                request.addEventListener("error", options.error);
            }
            if ('abort' in options) {
                request.addEventListener("abort", options.abort);
            }            
            request.open(options.type, options.url);                        
            for (let header in options.headers) {
                request.setRequestHeader(header, options.headers[header]);
            }            
            if ('beforeSend' in options) {
                options.beforeSend();
            }            
            request.send(options['data']);
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
                Osynapsy.action.dispatchServerResponse(data);
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

    pub.goto = function(url)
    {
        switch(url) {
            case 'refresh':
            case 'reload' :
                location.reload(true);
                break;
            case 'back' :
                Osynapsy.include('History.js', function(){
                    Osynapsy.History.back();
                });
                break;
            default :
                window.location = url;
                break;
        }
    };

    pub.init = function()
    {
        Osynapsy.setParentModalTitle();
        Osynapsy.include('Modal.js', function() { if(console) console.log('Modal module is loaded'); });
        Osynapsy.include('ActionNew.js', function() { if(console) console.log('ActionNew module is loaded'); });
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
            Osynapsy.include('History.js', function() { Osynapsy.History.back(); });
        }).on('click','.save-history', function(){
            Osynapsy.include('History.js', function() { Osynapsy.History.save(); });
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
        Osynapsy.plugin.init();
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

    pub.observe = function(target, fnc)
    {
        var observer = new MutationObserver(fnc);
        if (!(target instanceof Array)) {
            target = [target];
        }
        for (var i in target) {
            observer.observe(target[i], {attributes: true});
        }
    };

    pub.execCode = function(code)
    {
        if (Osynapsy.action.source) {
            var self = Osynapsy.action.source;
        }
        eval(code.replace(/(\r\n|\n|\r)/gm,""));
    };

    pub.plugin = {
        repo : {},
        register : function(name, oninit)
        {
            this.repo[name] = oninit;
        },
        init : function()
        {
            for (let pluginId in this.repo) {
                try {
                    this.repo[pluginId]();
                } catch (error) {
                    console.log(error);
                }
            }
        }
    };

    pub.on = function (event, elem, callback, capture)
    {
        if (typeof (elem) === 'function') {
            capture = callback;
            callback = elem;
            elem = window;
        }
        capture = capture ? true : false;
        elem = typeof elem === 'string' ? document.querySelector(elem) : elem;
        if (!elem) return;
        elem.addEventListener(event, callback, capture);
    };

    return pub;
});

Osynapsy.on("DOMContentLoaded", function() {
    Osynapsy.init();
});