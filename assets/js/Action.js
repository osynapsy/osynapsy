var Osynapsy = Osynapsy || {'action' : {}};

Osynapsy.action =
{
    execute : function(object)
    {
        let form = object.closest('form');
        let action = object.hasAttribute('data-action') ? object.dataset.action : null;
        if (Osynapsy.isEmpty(action)) {
            alert('Attribute data-action don\'t set.');
            return;
        }
        if (!Osynapsy.isEmpty(object.dataset.confirm)) {
            if (!confirm(object.dataset.confirm)) {
                return;
            }
        }
        this.remoteExecute(action, form, object);
    },
    remoteCallParametersFactory : function(object)
    {
        if (Osynapsy.isArray(object)) {
            return object;
        }
        if (!Osynapsy.isObject(object) || Osynapsy.isEmpty(object.dataset) || Osynapsy.isEmpty(object.dataset.actionParameters)) {
            return [];
        }
        let parameters = String(object.dataset.actionParameters).split(',');
        let result = parameters.map(function(parameter) {
            let firstCharOfParameter = parameter.charAt(0);
            if (parameter === 'this.value') {
                return object.value;
            } else if (firstCharOfParameter === '#' && document.getElementById(parameter.substring(1))) {
                return document.getElementById(parameter.substring(1)).value;
            }
            return parameter;
        });
        return result;
    },
    remoteExecute : function(action, form, object, onSuccess)
    {
        this.source = object;
        let actionUrl = this.getActionUrl(form);
        let actionParameters = this.remoteCallParametersFactory(object);
        let formData = (Osynapsy.isEmpty(form) ? new FormData() : new FormData(form));
        let fileUploadIsRequired = this.isUpload(form);
        actionParameters.forEach(function(value) { formData.append('actionParameters[]', value); });
        var requestParameters = {
            url  : actionUrl,
            data : formData,
            headers: {'X-Osynapsy-Action': action, 'Accept': 'application/json-osynapsy'},
            type : 'post',
            dataType : 'json',
            beforeSend : function() {
                //$('.field-in-error').removeClass('field-in-error');
                if (fileUploadIsRequired) {
                    Osynapsy.waitMask.showProgress();
                    return;
                }
                if (Osynapsy.isObject(object) && object.classList && !object.classList.contains('no-mask')) {
                    Osynapsy.waitMask.show();
                }
            },
            success : function(response) {
                Osynapsy.waitMask.remove();
                if (!Osynapsy.isEmpty(onSuccess)) {
                    onSuccess(response);
                    return;
                }
                Osynapsy.action.dispatchServerResponse(response, this);
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
            requestParameters['uploadProgress'] = Osynapsy.waitMask.uploadProgress;
        }
        return Osynapsy.ajax.execute(requestParameters);
    },
    getActionUrl : function(form) {
        if (Osynapsy.isEmpty(form) || Osynapsy.isEmpty(form.getAttribute('action'))){
            return window.location.href;
        }
        return form.getAttribute('action');
    },
    isUpload : function(form)
    {
        var uploadIsRequired = false;
        if (!Osynapsy.isEmpty(form)) {
            form.querySelectorAll('input[type=file]').forEach(function(inputFileElement){
                if (!Osynapsy.isEmpty(inputFileElement.value)) {
                    uploadIsRequired = true;
                }
            });
        }
        return uploadIsRequired;
    },
    dispatchServerResponse : function (response)
    {
        if (!Osynapsy.isObject(response)){
            console.log('Resp is not an object : ', response);
            return;
        }
        if (('error' in response)){
            this.dispatchError(response['error']);
        }
        if (('command' in response)) {
            this.executeCommands(response);
        }
    },
    dispatchError : function(rawMessage)
    {
        let regexp = /<!--.*?-->/gm;
        let errorMsg = rawMessage[0];
        var self = this;
        let fieldsInError = errorMsg.match(regexp, 'm');
        if (!Array.isArray(fieldsInError) || fieldsInError.length === 0) {
            console.log(rawMessage);
            alert(rawMessage);
            return;
        }        
        fieldsInError.forEach(function(rawId) {
            let id = rawId.replace('<!--','').replace('-->','');
            try {
                let elm = document.getElementById(id);
                errorMsg = self.showErrorOnLabel(elm, errorMsg);
                elm.classList.add('field-in-error');
                Osynapsy.element(elm).on('change', null, function() {
                    this.classList.remove('field-in-error');
                });
            } catch(excp) {
                errorMsg = errorMsg.replace(rawId, id);
                console.log(excp);
            }
        });
        if (typeof $().modal === 'function') {
            Osynapsy.modal.alert('Alert', '<pre>' + errorMsg.trim() + '</pre>');
            return;
        }
        alert('Si sono verificati i seguenti errori : ' + errorMsg);
    },    
    executeCommands : function(response)
    {
        let command;
        try {
            response.command.forEach(function(val, idx){
                command = val;
                if (command[0] in Osynapsy) {
                    Osynapsy[command[0]](command[1]);
                } else {
                    console.log(command[0] + ' -  ' +command[1]);
                }
            });
        } catch (error) {
            console.log(command);
            alert(error);
        }
    },
    showErrorOnLabel : function(elm, err)
    {
        let elementId = elm.getAttribute('id');
        let fieldLabel = elm.hasAttribute('placeholder') ? elm.getAttribute('placeholder') : elm.closest('[data-label]') ? elm.closest('[data-label]').dataset.label : elementId;                
        return err.replace('<!--' + elementId + '-->', '<strong>' + fieldLabel + '</strong>');
    },
    source : null
};
