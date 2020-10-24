var Osynapsy = Osynapsy || {'action' : {}};

Osynapsy.action =
{
    execute : function(object)
    {
        let form = object.closest('form');
        let action = object.dataset.action;
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
    remoteExecute : function(action, form, object)
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
            headers: {'Osynapsy-Action': action, 'Accept': 'application/json'},
            type : 'post',
            dataType : 'json',
            beforeSend : function() {
                $('.field-in-error').removeClass('field-in-error');
                if (fileUploadIsRequired) {
                    Osynapsy.waitMask.showProgress();
                    return;
                }
                if (!object.classList.contains('no-mask')) {
                    Osynapsy.waitMask.show();
                }
            },
            success : function(response) {
                Osynapsy.waitMask.remove();
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
        Osynapsy.ajax.execute(requestParameters);
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
            Osynapsy.modal.alert('Si sono verificati i seguenti errori', '<ul><li>' + errors.join('</li><li>') +'</li></ul>');
            return;
        }
        alert('Si sono verificati i seguenti errori : \n' + errors.join("\n").replace(/(<([^>]+)>)/ig,""));
    },
    executeCommands : function(response)
    {
        try {
            $.each(response.command, function(idx, val){
                if (val[0] in Osynapsy) {
                    Osynapsy[val[0]](val[1]);
                }
            });
        } catch (error) {
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
