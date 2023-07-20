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
            headers: {'Osynapsy-Action': action, 'Accept': 'application/json-osynapsy'},
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
        if (('errors' in response)){
            this.dispatchErrors(response);
        }
        if (('command' in response)) {
            this.executeCommands(response);
        }
    },
    dispatchErrors : function(response)
    {
        var errors = [];
        var self = this;
        response.errors.forEach(function(val, idx){            
            if (val[0] === 'alert'){
                alert(val[1]);
                return true;
            }
            let component = document.getElementById(val[0]);
            if (!component) {
                console.log('No component '+val[0]+ ' - ' + val[1]);
                return true;
            }
            errors.push(self.showErrorOnLabel(component, val[1]));
            if (component.classList.contains('field-in-error')){
                return true;
            }
            component.classList.add('field-in-error');
            Osynapsy.element(component).on('change', null, function() {
                this.classList.remove('field-in-error');
            });
        });
        if (errors.length === 0) {
            return;
        }
        if (typeof $().modal === 'function') {
            Osynapsy.modal.alert('Si sono verificati i seguenti errori', '<ul><li>' + errors.join('</li><li>') +'</li></ul>');
            return;
        }
        alert('Si sono verificati i seguenti errori : \n' + errors.join("\n").replace(/(<([^>]+)>)/ig,""));
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
        if ($(elm).closest('[data-label]').length > 0) {
            return err.replace('<!--'+$(elm).attr('id')+'-->', '<strong>' + $(elm).closest('[data-label]').data('label') + '</strong>');
        }
        return err.replace('<!--'+$(elm).attr('id')+'-->', '<i>'+ $(elm).attr('id') +'</i>');
    },
    source : null
};
