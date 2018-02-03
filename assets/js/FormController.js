window.locationPost = function(url, vars){
    var f = $('<form method="post" action="'+e.url+'"></form>');
    if (arguments.length > 1) {
        for (var k in arguments[1]) {
            $('<input type="hidden" name="'+k+'" value="'+arguments[1][k]+'">').appendTo(f);
        }
    }
    $('body').append(f);
    f.submit();
};

var Action = 
{
    execute : function(object)
    {
        var form = $(object).closest('form');
        var action = $(object).data('action');        
        if (!action) {
            alert('Attribute data-action don\'t set.');
            return;
        }
        if ($(object).data('confirm')) {
            if (!confirm($(object).data('confirm'))) {
                return;
            }   
        }        
        var actionParameters = this.grabActionParameters(object);
        if (actionParameters === false) {
            this.remoteExecute(action, form);
        }
        this.remoteExecute(action, form, actionParameters);
    },
    grabActionParameters : function(object)
    {
        if (!$(object).data('action-parameters')) {            
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
        return params.join('&')
    },
    remoteExecute : function(action, form)
    {
        var extraData = (arguments.length > 2) ? arguments[2] : '';
        alert(action);
        $('.field-in-error').removeClass('field-in-error');
        var callParameters = {
            url  : $(form).attr('action'),
            headers: {
                'Osynapsy-Action': action
            },
            type : 'post',
            dataType : 'json',
            success : function(response){            
                WaitMask.remove();
                ResponseDispatcher.dispatch(response);
            },
            error: function(xhr, status, error) {                
                WaitMask.remove();
                console.log(status);
                console.log(error);
                console.log(xhr);
                alert(xhr.responseText);
            }
        };                        
        if (!this.checkForUpload()) {
            WaitMask.show();
            var options = {
                data : $(form).serialize()+'&'+extraData
            };
        } else {
            WaitMask.showProgress();
            var options  = {
                //Se devo effettuare un upload personalizzo il metodo jquery $.ajax per fargli spedire il FormData
                data :  new FormData($(form)[0]), 
                xhr  : function() {  // Custom XMLHttpRequest
                    var myXhr = $.ajaxSettings.xhr();
                    if(myXhr.upload) { // Check if upload property exists
                        myXhr.upload.addEventListener('progress', WaitMask.uploadProgress, false); // For handling the progress of the upload
                    }
                    return myXhr;
                },
                mimeType : "multipart/form-data",
                contentType : false,
                cache : false,
                processData :false
            };        
        }
        $.extend(callParameters, options);
        $.ajax(callParameters);
        console.log(callParameters);
    },
    checkForUpload : function()
    {
        if (!window.FormData){            
            return false; //No file to upload or IE9,IE8,etc browser
        }
        var upload = false;
        $('input[type=file]').each(function(){
            //Carico il metodo per effettuare l'upload solo se c'è almeno un campo file pieno
            if ($(this).val() != '') {
                upload = true; 
                return false ;
            }
        });
        return upload;        
    }
};

var ResponseDispatcher = 
{
    response : null,
    dispatch : function (response)
    {
        this.response = response;
        if (!FormController.isObject(this.response)){
            console.log('Resp is not an object : ', this.response);
            return;
        }       
        this.dispatchErrors();
        this.dispatchCommands();
    },
    dispatchErrors : function()
    {
        if (!('errors' in this.response)){
            return;
        }
        var errorMessage = '';
        $.each(this.response.errors, function(idx, val){
            if (val[0] === 'alert'){
                alert(val[1]);
            } else if (!$('#'+val[0]).hasClass('field-in-error')){                                        
                var cmp = $('#'+val[0]);                    
                if (cmp.length > 0){
                    errorMessage += DispatcherKernelResponse.showErrorOnLabel(cmp, val[1]);                        
                } else {
                    errorMessage += val[1] + '\n';
                }
            }
        });
        if (errorMessage !== '') {
            FormController.modalAlert(
                'Si sono verificati i seguenti errori',
                '<pre>' + errorMessage +'</pre>'
            );
        }        
    },
    dispatchCommands : function()
    {
        if (!('command' in this.response)) {
            return;
        }
        $.each(this.response.command, function(idx, val){            
            if (val[0] in FormController) {
                FormController[val[0]](val[1]);
            }
        });
    },
    showErrorOnLabel : function(elm, err)
    {
        if ($(elm).data('label')) {
            return err.replace('<!--'+$(elm).attr('id')+'-->',$(elm).data('label')) + '\n';
        }
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

var WaitMask = 
{    
    build : function(message, parent, position)
    {                        
        var mask = $('<div id="waitMask" class="wait"><div class="message">'+message+'</div></div>');
        mask.width($(parent).width())
            .height($(parent).height())
            .css('top', position.top+'px')
            .css('left',position.left+'px');
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
        if ($('#progress_idx').length > 0){
            //if (console) console.log(a);
            var pos = a.loaded ? a.loaded : a.position;
            var t = Math.round((pos / a.total) * 100);
            $('#progress_bar').css('width',t +'%');
            $('#progress_idx').text(t +'%');
        }
    }
};

var FormController = 
{
    repo : 
    {
        event : { init : {} },
        componentInit : {}
    },
    init : function()
    {
        $('body').on('change','.change-execute',function(){
            Action.execute(this);
        }).on('click','.cmd-execute, .click-execute',function() {
            Action.execute(this);
        }).on('click','.cmd-back',function(){        
            FormController.back();
        }).on('click','.save-history',function(){
            FormController.saveHistory();
        }).on('click','a.open-modal',function(e){
            e.preventDefault();            
            FormController.modalWindow(
                'amodal', 
                $(this).attr('title'), 
                $(this).attr('href'), 
                $(this).attr('modal-width') ? $(this).attr('modal-width') : '75%',
                $(this).attr('modal-height') ? $(this).attr('modal-height') : ($(window).innerHeight() - 250) + 'px'
            );
        });
        this.fire('init');
    },
    back : function()
    {
        if (!sessionStorage.history) {
            history.back();
        }
        var hst = JSON.parse(sessionStorage.history);
        var stp = hst.pop();        
        var frm = $('<form method="post" action="'+stp.url+'"></form>');
        for (var k in stp.parameters) {
            var fld = stp.parameters[k];
            $('<input type="hidden" name="'+fld[0]+'" value="'+fld[1]+'">').appendTo(frm);
        }
        sessionStorage.history = JSON.stringify(hst);
        $('body').append(frm);
        frm.submit();
    },    
    fire : function(evt)
    {
        if (evt in this.repo['event']){
            for (var i in this.repo['event'][evt] ){
                try{
                    this.repo['event'][evt][i]();
                } catch(err) {
                    console.log(i+' : '+err);
                }
            }
        }
    },
    goto : function(pag, par)
    {
        switch(pag) {
            case 'refresh':
            case 'reload' :
                location.reload(true);
                break;
            case 'back'   :
                this.back();
                break;
            default :
                window.location = pag;
                break;
        }

    },
    gotoPost : function (url, parameters)
    {
        var frm = $('<form method="post" action="'+url+'"></form>');
        for (k in parameters) {
            $('<input type="hidden" name="'+k+'" value="'+parameters[k]+'">').appendTo(frm);
        }
        $('body').append(frm);
        frm.submit();
    },    
    isObject : function(v)
    {
        return v instanceof Object;
    },
    execute : function(obj)
    {
        Action.execute(obj);
    },    
    execCode : function(code) {
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
        var data  = $('form').serialize();
            data += (arguments.length > 1 && arguments[1]) ? '&'+arguments[1] : '';
        if (!(typeof component === 'object')) {            
            WaitMask.show(component);
            component = Array(component);
        } else if ($(component).is(':visible')) {           
            WaitMask.show();
        }
        for (var i in component) {
            data += '&ajax[]=' + $(component[i]).attr('id');
        }
        $.ajax({
            type : 'post',
            data : data,
            success : function(rsp) {
                console.log(rsp);                
                WaitMask.remove();
                for (var i in component) {
                    var cid = '#'+$(component[i]).attr('id');
                    var cmp = $(rsp).find(cid);
                    //$(cid).html(cmp.html());
                    $(cid).replaceWith(cmp);
                }
            }
        });
    },
    register : function(evt,lbl,fnc)
    {
        this.repo['event'][evt][lbl] = fnc;
    },    
    saveHistory : function()
    {
        var hst = [];
        var arr = [];
        if (sessionStorage.history){
            hst = JSON.parse(sessionStorage.history);
        }
        $('input,select,textarea').each(function(){
            switch ($(this).attr('type')) {
                case 'submit':
                case 'button':
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
                win += '<button type="button" class="btn btn-default'+btnCloseClass+' click-execute" data-action="'+ actionCancel +'" data-dismiss="modal">Annulla</button>';
            } else {
                win += '<button type="button" class="btn btn-default'+btnCloseClass+'" data-dismiss="modal">Annulla</button>';
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
    modalAlert : function(title, message) {
        if (!title) {
            title = 'Alert';
        }
        var win = this.modal('alert', title, message, null, null);        
        return $(win);
    },
    modalConfirm : function(title, message, actionConfirm, actionCancel){
        if (!title) {
            title = 'Conferm';
        }
        return this.modal('confirm', title, message, actionConfirm, actionCancel);
    },
    modalWindow : function(id, title, url) {
        var wdt = '90%';        
        var hgt = ($(window).innerHeight() - 250) + 'px';
        if (typeof arguments[3] !== 'undefined') {
            wdt = arguments[3];
        }        
        if (typeof arguments[4] !== 'undefined') {
            hgt = arguments[4];
            console.log('height :' + hgt);
        }
        
        $('.modal').remove();
        var win  = '<div id="' + id + '" class="modal fade" role="dialog">\n';
            win += '    <div class="modal-dialog modal-lg" style="width: '+wdt+';">\n';
            win += '        <div class="modal-content">\n';
            win += '            <div class="modal-header">\n';
            win += '                <button type="button" class="close" data-dismiss="modal">&times;</button>';
            win += '                <h4 class="modal-title">' + title + '</h4>';
            win += '            </div>';
            win += '            <div class="modal-body">';
            win += '                <iframe name="'+id+'" src="'+url+'" style="width: 100%; height:'+ hgt +'; border: 0px; border-radius: 3px;" border="0"></iframe>';
            win += '            </div>';
            //win += '            <div class="modal-footer">';
            //win += '                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>';
            //win += '            </div>';
            win += '        </div>';
            win += '    </div>';
            win += '</div>';
            win = $(win);
        $('body').append(win);
        $('#'+id).modal({
            keyboard : true
        });
        return win;
    }
};

$(document).ready(function(){
    FormController.init();
});
