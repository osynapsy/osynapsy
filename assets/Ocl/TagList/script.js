OclTagList = {
    init : function()
    {
        $('div.osy-taglist input.add').keypress(function(event){
            if (event.keyCode === 13 || event.keyCode === 44){
                event.preventDefault();
                if ($(this).val() === '') return;                          	                  	                                
                if ($(this).closest('div.osy-taglist').hasClass('osy-taglist-onextab')){
                    OclTagList.ajax_call(this);           
                } else {
                    OclTagList.add_item($(this).parent(),null,$(this).val());
                    $(this).val('');
                }                    
            }
        });
        $('div.osy-taglist').click(function(){
            $('input',this).focus();
        });
        $('div.osy-taglist').on('click','a',function(e){
            e.preventDefault();
            tag = $(this).closest('.osy-taglist');
            if (!tag.hasClass('osy-taglist-onextab')){
                OclTagList.remove_item($(this).closest('.osy-taglist-entry'));
                return;
            }
            cid = tag.attr('id');
            val = $(this).prev().text();
            $.ajax({
                type    : 'post',
                context : this,
                data : $('form').serialize() + '&ajax='+cid+'&ajax-cmd=del&tag='+val,
                success : function(rsp)
                {
                     if (rsp.trim() == 'OK'){                            
                        OclTagList.remove_item($(this).closest('.osy-taglist-entry'));
                     } else {
                        oform.command.alert(rsp,'Errore');
                     }
                }
            });
        });	
    },
    ajax_call : function(obj)
    {
       if ($('input[name^="pkey["]').length == 0){                     
            oform.set('caller',obj);
            oform.main.save();
            return;
       }
       par = $(obj).closest('div.osy-taglist');
       cid = par.attr('id');               
       val = $(obj).val();
       $(obj).val('');           
       $.ajax({
            type    : 'post',
            context : obj,
            data : $('form').serialize() + '&ajax='+cid+'&ajax-cmd=add&tag='+val,
            success : function(rsp)
            {
                if (rsp.trim() == 'OK'){
                    tag = rsp.split(',');
                    OclTagList.add_item($(this).parent(),null,val);
                } else {
                    oform.command.alert(rsp,'Errore');
                }
            }
        });  
    },
    add_item : function(par,id,vl)
    {
        tid = id ? ' tid="'+id.trim()+'"' : '';
        span = $('<li'+tid+' class="osy-taglist-entry"><span class="osy-taglist-entry-text">' + vl + '</span><a href="#" class="osy-taglist-entry-remove">remove</a></li>');	
        par.before(span);
        val = $('input[type=hidden]',par.closest('.osy-taglist')).val() == '' ? vl : $('input[type=hidden]',par.closest('.osy-taglist')).val() + ',' + vl;
        $('input[type=hidden]',par.closest('.osy-taglist')).val(val) ;
    },
    remove_item : function(par)
    {
        span = par.children().first();
        tlist = $('input[type=hidden]',par.closest('.osy-taglist')).val().split(',');
        tlist.splice(tlist.indexOf(span.text()),1);
        $('input[type=hidden]',par.closest('.osy-taglist')).val(tlist.join(','));
        par.remove();
    }
}

if (window.FormController) {
    FormController.register('init','OclTagList',function(){
        OclTagList.init();
    });
}
