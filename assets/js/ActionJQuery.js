var Osynapsy = Osynapsy || {'action' : {}};

Osynapsy.action =
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
            headers: {'Osynapsy-Action': action, 'Accept': 'application/json'},
            type : 'post',
            dataType : 'json',
            success : function(response) {                
                Osynapsy.waitMask.remove();
                Osynapsy.action.dispatchServerResponse(response, this);
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
                beforeSend : function() { Osynapsy.waitMask.show(); },
                data : $(form).serialize()+'&'+extraData
            };
        } else {
            //callParameters['headers']['Content-Type'] = 'multipart/form-data';
            var options  = {
                beforeSend : function() { Osynapsy.waitMask.showProgress(); },
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
        //Osynapsy.ajax.execute(callParameters);
    },
    checkForUpload : function()
    {
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
        $.each(response.command, function(idx, val){
            console.log(val);
            if (val[0] in Osynapsy) {
                Osynapsy[val[0]](val[1]);
            }
        });
    },
    showErrorOnLabel : function(elm, err)
    {
        if ($(elm).closest('[data-label]').length > 0) {
            return err.replace('<!--'+$(elm).attr('id')+'-->', '<strong>' + $(elm).closest('[data-label]').data('label') + '</strong>');
        }
        return err.replace('<!--'+$(elm).attr('id')+'-->', '<i>'+ $(elm).attr('id') +'</i>');
        var par = elm.closest('.form-group');
        if (par.hasClass('has-error')) {
            return false;
        }
        par.addClass('has-error');
        $('label',par).append(' <span class="error">'+ err +'</span>');
        elm.change(function(){
            var par = $(this).closest('.form-group');
            $('span.error',par).remove();
            par.removeClass('has-error');
        });
    },
    source : null
};
