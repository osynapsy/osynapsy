window.locationPost = function(url,vars){
  f = $('<form method="post" action="'+e.url+'"></form>');
  if (arguments.length > 1) {
      for (k in arguments[1]) {
        $('<input type="hidden" name="'+k+'" value="'+arguments[1][k]+'">').appendTo(f);
      }
  }
  $('body').append(f);
  f.submit();
}

FormController = 
{
    repo : 
    {
        event : { init : {} },
        componentInit : {}
    },
    init : function()
    {
        $('body').on('click','.cmd-execute, .click-execute',function() {
            FormController.execute(this);
        }).on('change','.change-execute',function(){
            FormController.execute(this);
        }).on('click','a.open-modal',function(e){
            e.preventDefault();            
            FormController.modalWindow(
                'amodal', 
                $(this).attr('title'), 
                $(this).attr('href'), 
                $(this).attr('modal-width') ? $(this).attr('modal-width') : '75%',
                $(this).attr('modal-height') ? $(this).attr('modal-height') : ($(window).innerHeight() - 250) + 'px'
            );
        }).on('click','.cmd-delete',function(){
            if (confirm('Sei sicuro di voler eliminare il record corrente?')){
                FormController.exec(this,'delete');
            }
        }).on('click','.cmd-back',function(){        
            FormController.back();
        }).on('click','.save-history',function(){
            FormController.saveHistory();
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
        for (k in stp.parameters) {
            var fld = stp.parameters[k];
            $('<input type="hidden" name="'+fld[0]+'" value="'+fld[1]+'">').appendTo(frm);
        }
        sessionStorage.history = JSON.stringify(hst);
        $('body').append(frm);
        frm.submit();
    },
    showErrorOnLabel : function(elm, err)
    {
        /*if ($(elm).data('label')) {
            return err.replace('<!--'+$(elm).attr('id')+'-->',$(elm).data('label')) + '\n';
        }*/
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
    },
    dispatchKernelResp : function(resp)
    {
        //console.log(resp);
        if (!this.isObject(resp)){
            console.log('Resp is not an object : ',resp);
            return;
        }
        if ('errors' in resp){
            msg = '';
            $.each(resp.errors, function(idx, val){
                if (val[0] == 'alert'){
                    alert(val[1]);
                } else if (!$('#'+val[0]).hasClass('field-in-error')){
                    cmp = $('#'+val[0]);
                    if (cmp){
                        FormController.showErrorOnLabel(cmp, val[1]);                        
                    }
                }
            });
        }
        if ('command' in resp){
            $.each(resp.command, function(idx,val){
                //if (idx in this) {
                if (val[0] in FormController) {
                    FormController[val[0]](val[1]);
                }
            });
        }
    },
    fire : function(evt)
    {
        if (evt in this.repo['event']){
            for ( i in this.repo['event'][evt] ){
                try{
                    this.repo['event'][evt][i]();
                } catch(err) {
                    console.log(i+' : '+err);
                }
            }
        }
    },
    goto : function(pag,par)
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
        if (!$(obj).data('action')) {
            alert('Attribute data-action don\'t set.');
        }
        if (!$(obj).data('action-parameters')) {
            FormController.exec(obj, $(obj).data('action'));
            return;
        }
        var parameterLst = [];        
        var parameterRaw = String($(obj).data('action-parameters')).split(',');
        for (i in parameterRaw) {
            var parameterValue = parameterRaw[i];
            if (parameterValue === 'this.value'){
                parameterValue = $(obj).val();
            } else if (parameterValue.charAt(0) === '#' && $(parameterValue).length > 0) {
                parameterValue = $(parameterValue).val();
            } 
            parameterLst.push('actionParameters[]=' + parameterValue);
        }
        FormController.exec(obj, $(obj).data('action'), parameterLst.join('&'));
    },
    exec : function(obj, cmd)
    {
        var extraData = (arguments.length > 2) ? arguments[2] : '';
        var funcDispatcher = (arguments.length > 3) ? arguments[3] : function(resp){
            FormController.waitMask('remove');
            FormController.dispatchKernelResp(resp);
        };
        $('.field-in-error').removeClass('field-in-error');
        var ajaxpar = {
            url  : $(obj).closest('form').attr('action'),
            headers: {'Osynapsy-Action': cmd},
            type : 'post',
            dataType : 'json',
            success : funcDispatcher,
            error: function(xhr, status, error) {
                FormController.waitMask('remove');
                console.log(error);
                console.log(xhr);
                alert(xhr.responseText);
            }
        }
        var upload = false;
        if (window.FormData){
            $('input[type=file]').each(function(){
                //Carico il metodo per effettuare l'upload solo se c'è almeno un campo file pieno
                if ($(this).val() != '') {
                    upload = true; return false ;
                }
            });
        }
        if (upload){ //Se devo effettuare un upload personalizzo il metodo jquery $.ajax per fargli spedire il FormData
          this.waitMask('open','progress');
          ajaxpar['data'] = new FormData($(obj).closest('form')[0]);
          ajaxpar['xhr'] = function(){  // Custom XMLHttpRequest
             var myXhr = $.ajaxSettings.xhr();
             if(myXhr.upload) { // Check if upload property exists
                myXhr.upload.addEventListener('progress',FormController.uploadProgress, false); // For handling the progress of the upload
             }
             return myXhr;
          }
          ajaxpar['mimeType'] = "multipart/form-data";
          ajaxpar['contentType'] = false;
          ajaxpar['cache'] = false;
          ajaxpar['processData'] = false;
        } else { //No file to upload or IE9,IE8,etc browser
          this.waitMask('open');
          ajaxpar['data'] = $(obj).closest('form').serialize()+'&'+extraData;
        }
        $.ajax(ajaxpar);
                console.log(ajaxpar);
    },
    execCode : function(code) {
        eval(code);
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
            FormController.waitMask('open','wait',component);
            component = Array(component);
        } else if ($(component).is(':visible')) {
            FormController.waitMask('open','wait');
        }
        for (i in component) {
            data += '&ajax[]=' + $(component[i]).attr('id');
        }
        $.ajax({
            type : 'post',
            data : data,
            success : function(rsp) {
                console.log(rsp);
                FormController.waitMask('remove');
                for (i in component) {
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
    waitMask : function(cmd, typ)
    {
        if (cmd == 'remove') {
            $('#waitMask').remove();
            return;
        }
        var maskParent = document;
        var maskPosition = {top : '0px', left : '0px'};
        if (arguments.length > 2) {
            maskParent = arguments[2];
            maskPosition = $(maskParent).offset();
        }
        var maskMessage = 'PLEASE WAIT <span class="fa fa-refresh fa-spin"></span>';
        switch (typ) {
            case 'progress':
                maskMessage = '<div class="progress_msg">Upload in progress .... <span id="progress_idx">0%</span> completed</div>';
                maskMessage += '<div class="progress"><div id="progress_bar" style="background-color: #ceddef; width: 0%;">&nbsp;</div></div>';
                break;
        }
        var d = $('<div id="waitMask" class="wait"><div class="message">'+maskMessage+'</div></div>');
            d.width($(maskParent).width())
             .height($(maskParent).height())
             .css('top', maskPosition.top+'px')
             .css('left', maskPosition.left+'px');
        $('body').append(d);
    },
    uploadProgress : function(a){
        if ($('#progress_idx').length>0){
            //if (console) console.log(a);
            pos = a.loaded ? a.loaded : a.position;
            t = Math.round((pos / a.total) * 100);
            $('#progress_bar').css('width',t +'%');
            $('#progress_idx').text(t +'%');
        }
    },
    setValue : function(k,v)
    {
        if ($('#'+k).length > 0){
            $('#'+k).val(v);
        }
    },
    modalAlert : function(title, body) {
        var id = 'alert';
        $('.modal').remove();
        var win  = '<div id="' + id + '" class="modal fade" role="dialog">\n';
            win += '    <div class="modal-dialog modal-xs">\n';
            win += '        <div class="modal-content">\n';
            win += '            <div class="modal-header">\n';
            win += '                <button type="button" class="close" data-dismiss="modal">&times;</button>';
            win += '                <h4 class="modal-title">' + title + '</h4>';
            win += '            </div>';
            win += '            <div class="modal-body">';
            win += body;
            win += '            </div>';
            win += '            <div class="modal-footer">';
            win += '                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>';
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
            win += '                <iframe name="'+id+'" src="'+url+'?view=simple" style="width: 100%; height:'+ hgt +'; border: 0px; border-radius: 3px;" border="0"></iframe>';
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
