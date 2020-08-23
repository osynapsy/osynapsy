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
