var Osynapsy = new (function(){

    var pub = {modal : {}, action : {}, worker : {}, notification : {}};

    pub.createElement = function (tag, attributes)
    {
        let element = document.createElement(tag);
        for (let id in attributes) {
            if (id === 'class') {
                element.classList.add(attributes[id]);
            } else {
                element[id] = attributes[id];
            }
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
                request.upload.addEventListener("progress", options.uploadProgress, false);
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
                        options.error(event.target, 'error', err.message);
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
            Object.entries(options.headers).forEach(function(header) {
                request.setRequestHeader(header[0], header[1]);
            });
            if ('beforeSend' in options) {
                options.beforeSend();
            }
            request.send(options['data']);
            return request;
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
            if (Osynapsy.isEmpty(source.getAttribute('id'))) {
                return;
            }
            this.dispatchRemote(source, event);
        },
        dispatchRemote : function(object, event)
        {
            var actionUrl = window.location.href;
            var form = object.closest('form');
            if (!Osynapsy.isEmpty(form.getAttribute('action'))) {
                actionUrl = form.getAttribute('action');
            }
            var formData = new FormData(form);
            formData.append('actionParameters[]', object.getAttribute('id') + event);
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

    pub.element = function(selector)
    {
        let elements = Osynapsy.isObject(selector) ? [selector] : document.querySelectorAll(selector);

        elements.offset = function()
        {
            let self = this;
            if (self[0] === document) {
                return {top : 0, left : 0, width: document.documentElement.scrollWidth, height : document.documentElement.scrollHeight};
            }
            let rect = self[0].getBoundingClientRect();
            return {top: rect.top + window.scrollY, left: rect.left + window.scrollX, width : rect.width, height : rect.height};
        };

        elements.on = function(event, filter, rawcallback)
        {
            elements.forEach(function(element) {
                let callback = Osynapsy.isEmpty(filter) ? rawcallback : function(event) {
                    let filteredElements = Array.from(element.querySelectorAll(filter));
                    let elementIncluded = filteredElements.includes(event.target);
                    let parentElementIncluded = filteredElements.includes(event.target.closest(filter));
                    if (elementIncluded || parentElementIncluded) {
                        rawcallback.apply(event.target.closest(filter), [event]);
                    }
                };
                event.trim().split(' ').forEach(function (evt) {
                    element.addEventListener(evt, callback);
                });
            });
            return elements;
        };
        return elements;
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

    pub.isArray = function(value)
    {
        return Array.isArray(value);
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

    pub.typingEvent = function(obj)
    {
        if (pub.typingTimeout !== undefined) {
            clearTimeout(pub.typingTimeout);            
        }
        pub.typingTimeout = setTimeout(function(){
            var code = obj.getAttribute('ontyping');
            if (code) {
                eval(code);
            }
        }, 1000);
    };

    pub.goto = function(url)
    {
        switch(url) {
            case 'refresh':
            case 'reload' :
                location.reload(true);
                break;
            case 'back' :
                Osynapsy.include('History.js', () => Osynapsy.History.back());
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
        Osynapsy.include('Action.js', function() { if(console) console.log('Action module is loaded'); });
        Osynapsy.element('body').on('click','.save-history', function(){
            Osynapsy.include('History.js', function() { Osynapsy.History.save(); });
        }).on('click','.click-execute, .onclick-execute',function() {
            Osynapsy.action.execute(this);
        }).on('click', '.cmd-back', function() {
            Osynapsy.include('History.js', function() { Osynapsy.History.back(); });
        }).on('blur','.blur-execute', function(){
            Osynapsy.action.execute(this);
        }).on('change','.change-execute[data-action]', function() {
            Osynapsy.action.execute(this);
        }).on('keyup', '.typing-execute', function(){
           Osynapsy.typingEvent(this);
        }).on('keydown','.onenter-execute', event => {
            event.stopPropagation();
            switch (event.keyCode) {
                case 13 : //Enter
                case 9:
                    Osynapsy.action.execute(this);
                    return false;
                break;
            }
        }).on('click change', '.dispatch-event', ev => {
            let eventClass = 'dispatch-event-' + ev.type;
            if (this.classList.contains(eventClass)) {
                Osynapsy.event.dispatch(this, event.type.charAt(0).toUpperCase() + event.type.slice(1));
            }
        }).on('click', '.open-modal', e => {
            e.preventDefault();
            Osynapsy.modal.window(
                this.getAttribute('title'),
                this.classList.contains('postdata') ? [this.getAttribute('href'), this.closest('form')] : this.getAttribute('href'),
                this.getAttribute('modal-width'),
                this.getAttribute('modal-height')
            );
        });
        Osynapsy.plugin.init();
    };

    pub.post = function(url, values)
    {
        let form = Osynapsy.createElement('form', {'action' : url, 'method' : 'post'});
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
        let componentsIDs = Array.isArray(components) ? components : [components];
        let execOnSuccess = arguments.length > 1 ? arguments[1] : null;
        if (componentsIDs.length === 1 && document.getElementById(componentsIDs[0])) {
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
        response.then(response => response.text())
                .then(strHtmlPage => {
            Osynapsy.waitMask.remove();
            let parser = new DOMParser();
            let remoteDoc = parser.parseFromString(strHtmlPage, 'text/html');            
            componentsIDs.forEach(componentId => {                
                let remoteComponent = remoteDoc.getElementById(componentId);
                if (remoteComponent && document.getElementById(componentId)) {
                    document.getElementById(componentId).replaceWith(remoteComponent);
                }
            });
            if (remoteDoc.getElementById('responseLibs')) {
                let appended = 0;
                Array.from(remoteDoc.getElementById('responseLibs').children).forEach(elm => {                    
                    if (document.getElementById(elm.getAttribute('id'))) {                        
                        return;
                    }                    
                    document.body.append(!elm.hasAttribute('src') ? elm : Osynapsy.createElement('script', { 
                        'id' : elm.getAttribute('id'), 
                        'src' : elm.getAttribute('src')
                    }));
                    appended++;
                });
                if (appended) {
                    setTimeout(() => Osynapsy.plugin.init(), 500);
                }
            }
            if (typeof execOnSuccess === 'function') {
                execOnSuccess();
            }
        }).catch(error => {
            Osynapsy.waitMask.remove();
            console.log(error);
        });
    };

    pub.waitMask =
    {
        build : function(message, parent)
        {
            let mask = Osynapsy.createElement('div', {'id' : 'waitMask', 'class' : 'wait'});
            let position = Osynapsy.element(parent).offset();
            mask.appendChild(Osynapsy.createElement('div', {'class' : 'message'})).innerHTML = message;
            mask.style.width = position.width + 'px';
            mask.style.height = position.height + 'px';
            mask.style.top = position.top + 'px';
            mask.style.left = position.left + 'px';
            document.body.appendChild(mask);
        },
        show : function()
        {
            let message = 'PLEASE WAIT <span class="fa fa-refresh fa-sync fa-spin"></span>';
            var parent = arguments.length > 0 ? arguments[0] : document;
            this.build(message, parent);
        },
        showProgress : function()
        {
            var message = '';
            message += '<div class="progress_msg">Upload in progress .... <span id="progress_idx">0%</span> completed</div>';
            message += '<div class="progress"><div id="progress_bar" style="background-color: #ceddef; width: 0%;">&nbsp;</div></div>';
            this.build(message, document);
        },
        remove : function()
        {
            let waitMaskElement = document.getElementById('waitMask');
            if (waitMaskElement) {
                waitMaskElement.parentElement.removeChild(waitMaskElement);
            }
        },
        uploadProgress : function(progress)
        {
            if (Osynapsy.isEmpty(document.getElementById('progress_idx'))) {
                return;
            }
            let pos = progress.loaded ? progress.loaded : progress.position;
            let tot = Math.round((pos / progress.total) * 100);
            document.getElementById('progress_bar').style.width =  tot + '%';
            document.getElementById('progress_idx').innerText = tot + '%';
        }
    };

    pub.setParentModalTitle = function()
    {
        if (window.frameElement && parent.document.getElementById('amodal')) {
            parent.document.getElementById('amodal').querySelector('.modal-title').innerHTML = document.title;
        }
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
        let self = Osynapsy.action.source;
        eval(code.replace(/(\r\n|\n|\r)/gm,""));
    };

    pub.plugin = {
        repo : {},
        register : function(name, oninit)
        {
            this.repo[name] = [oninit, false];
            console.log('Plugin ' + name + ' registered');
        },
        init : function()
        {            
            for (let pid in this.repo) {                
                try {
                    if (this.repo[pid][1]) {
                       throw 'Plugin ' + pid + ' is already init';
                    }
                    this.repo[pid][0]();
                    this.repo[pid][1] = true;
                } catch (error) {
                    console.log(error);
                }
            }
        }
    };

    pub.showSystemMsg = function(msg, timeout = 2000)
    {
        if (document.getElementById('systemMsg')) {
            document.getElementById('systemMsg').remove();
        }
        let sysMsgContainer = document.createElement('div');        
        sysMsgContainer.setAttribute('id', 'systemMsg');
        sysMsgContainer.classList.add('osy-system-message');
        sysMsgContainer.innerText = msg;
        document.body.append(sysMsgContainer);
        setTimeout(function(){ sysMsgContainer.remove(); }, timeout);        
    };

    return pub;
});

Osynapsy.element(document).on("DOMContentLoaded", null, function() {
    Osynapsy.init();
});