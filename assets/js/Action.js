window.Osynapsy = window.Osynapsy || {'action' : {}};

class ActionRequest 
{
    constructor(action, actionParameters, form, onSuccess = () => {}, maskOn = document)
    {
        this.action = action;
        this.actionParameters = actionParameters;
        this.form = form;        
        this.onSuccess = onSuccess;
        this.accept = 'application/x-osyjson';
        this.progress = null;
        this.onMessage = null;
        this.maskOn = maskOn;
    }

    build() 
    {        
        const fileUploadIsRequired = this.uploadRequired();
        const request = {
            url: this.getActionUrl(),
            data: this.formDataFactory(),
            headers: {
                'X-Osynapsy-Action': this.action, 
                'Accept': this.accept
            },
            type: 'post',
            dataType: 'json',
            beforeSend: () => {
                if (fileUploadIsRequired) {
                    Osynapsy.waitMask.showProgress();
                    return;
                }
                if (this.maskOn) {                    
                    Osynapsy.waitMask.show(this.maskOn);
                }
            },
            success: function (response) {
                Osynapsy.waitMask.remove();
                if (!Osynapsy.isEmpty(this.onSuccess)) {
                    this.onSuccess(response);
                    return;
                }
                Osynapsy.action.dispatchServerResponse(response);
            },
            error: function(xhr, status, error) {
                Osynapsy.waitMask.remove();
                if (console) {
                    console.trace();
                    console.log(status);
                    console.log(error);
                    console.log(xhr.responseText);
                }
                alert(xhr.responseText);                
            }
        };
        if (fileUploadIsRequired) {
            request.uploadProgress = Osynapsy.waitMask.uploadProgress;
        }
        if (this.progress) {
            request.progress = this.progress;
        }
        if (this.onMessage) {
            request.onMessage = this.onMessage;
        }
        return request;
    }

    formDataFactory() 
    {
        const formData = Osynapsy.isEmpty(this.form) ? new FormData() : new FormData(this.form);
        if (this.actionParameters && typeof this.actionParameters.forEach === 'function') {
            this.actionParameters.forEach((value) => { 
                formData.append('actionParameters[]', value); 
            });
        }
        return formData;
    }

    getActionUrl()
    {
        return (Osynapsy.isEmpty(this.form) || Osynapsy.isEmpty(this.form.getAttribute('action'))) 
            ? window.location.href 
            : this.form.getAttribute('action');
    }

    uploadRequired()
    {
        let uploadIsRequired = false;
        if (!Osynapsy.isEmpty(this.form)) {
            this.form.querySelectorAll('input[type=file]').forEach((inputFileElement) => {                
                if (inputFileElement.files.length > 0) {
                    uploadIsRequired = true;                    
                }
            });
        }
        return uploadIsRequired;
    }
    
    enableStream()
    {
        this.accept = 'application/x-ndjson';
        this.onMessage = Osynapsy.action.dispatchServerResponse;
        this.progress = function(evt) { console.log('progress', new Date()); }; 
    }
}

class ActionObjectRequest 
{
    constructor(object, onSuccess)
    {
        this.object = object;       
        this.onSuccess = onSuccess;
        this.confirmed = true;
        if (!this.object.hasAttribute('data-action')) {
            this.confirmed = false;
            alert('The attribute "data-action" is not set.');            
        } else if (!Osynapsy.isEmpty(object.dataset.confirm)) {            
            this.confirmed = confirm(object.dataset.confirm);            
        }
    }
    
    build() {
        const form = this.object.closest('form');
        const action = this.object.dataset.action;
        const actionParameters = this.object.hasAttribute('data-action-parameters') ? this.actiontParametersFactory() : [];
        let maskOn = this.object.classList.contains('no-mask') ? null : (this.object.closest('.mask') ?? document);        
        const request = new ActionRequest(action, actionParameters, form, this.onSuccess, maskOn);
        if (this.object.hasAttribute('data-stream')) {
            request.enableStream();            
        }            
        return request.build();
    }
    
    actiontParametersFactory()
    {
        const object = this.object;
        if (Osynapsy.isArray(object)) {
            return object;
        }
        if (!Osynapsy.isObject(object) || Osynapsy.isEmpty(object.dataset) || Osynapsy.isEmpty(object.dataset.actionParameters)) {
            return [];
        }
        let parameters = String(object.dataset.actionParameters).split(',');
        let result = parameters.map((parameter) => {            
            if (parameter === 'this.value') {
                return object?.value ?? null;
            }            
            if (parameter.charAt(0) === '#' && document.getElementById(parameter.substring(1))) {
                return document.getElementById(parameter.substring(1)).value;
            }
            return parameter;
        });
        return result;
    }
    
    isConfirmed() {
        return this.confirmed;
    }
}

Osynapsy.action =
{
    execute : function(object)
    {        
        let Request = new ActionObjectRequest(object);        
        if (Request.isConfirmed()) {
            return Osynapsy.ajax.execute(Request.build());
        }
    },    
    dispatchServerResponse : function (response)
    {
        if (!Osynapsy.isObject(response)){
            console.log('Resp is not an object : ', response);
            return;
        }        
        if (('command' in response)) {
            Osynapsy.action.executeCommands(response);
        }
        if (('error' in response)){
            Osynapsy.action.dispatchError(response['error']);
        }
    },
    executeCommands: function(response)
    {
        let command;

        try {
            response.command.forEach(function(val) {
                command = val;

                const [path, ...args] = command;
                const parts = path.split('.');
                const method = parts.pop();

                let target;

                if (parts[0] === 'jQuery' || parts[0] === '$') {
                    const selector = parts.slice(1).join('.');
                    target = $(selector);
                } else {
                    target = window;
                    for (const property of parts) {
                        target = target?.[property];
                    }
                }

                if (target && typeof target[method] === 'function') {
                    target[method](...args);
                } else {
                    console.warn('Comando non disponibile:', path);
                }
            });

        } catch (error) {
            console.error('Errore eseguendo il comando:', command, error);
        }
    },
    dispatchError : function(rawMessage)
    {
        let regexp = /<!--.*?-->/gm;
        let errorMsg = rawMessage[0];
        let self = this;
        let fieldsInError = errorMsg.match(regexp, 'm');
        if (!Array.isArray(fieldsInError) || fieldsInError.length === 0) {
            alert(rawMessage);
            return;
        }        
        fieldsInError.forEach(function(rawId) {
            let id = rawId.replace('<!--','').replace('-->','');
            try {
                let elm = document.getElementById(id);
                errorMsg = self.showErrorOnLabel(elm, errorMsg);
                elm.classList.add('field-in-error');
                Osynapsy.element(elm).on('blur', null, function() {
                    this.classList.remove('field-in-error');
                });
            } catch(excp) {
                errorMsg = errorMsg.replace(rawId, id);
                console.log(excp);
            }
        });               
        Osynapsy.modal.alert('Alert', '<pre>' + errorMsg.trim() + '</pre>');        
    },    
    showErrorOnLabel : function(elm, err)
    {
        let elementId = elm.getAttribute('id');
        let fieldLabel = elm.hasAttribute('placeholder') ? elm.getAttribute('placeholder') : elm.closest('[data-label]') ? elm.closest('[data-label]').dataset.label : elementId;                
        return err.replace('<!--' + elementId + '-->', '<strong>' + fieldLabel + '</strong>');
    },
    source : null
};
