/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
otextsearchinline = {
    init : function()
    {
        $('div.osy-textsearch-inline input[type=text]').keyup(function(event){
            switch (event.keyCode) {
                case 13 : //Enter
                    $('.row.selected','#search_content').trigger('click');
                    break;
                case 27 :
                    $('#search_content').remove();
                    break;
                case 38 : // up
                    if ($('#search_content').length > 0) {
                        $('#search_content').trigger('arrow-up');
                    } 
                    break;
                case 40 :
                    if ($('#search_content').length > 0) {
                        $('#search_content').trigger('arrow-down');
                    }
                    break;
                default:
                    if ($(this).val() == '') {
                        return;
                    }
                    otextsearchinline.openSearchContainer(this);
                    $.ajax({
                        type : 'post',
                        data : $('form').serialize()+'&ajax='+$(this).parent().attr('id'),
                        success : function(rsp) {
                            $('#search_content').html(rsp);
                        }
                    });
                    break;
            }
        }).attr('autocomplete','off');
        $(window).on('click',function(){
            $('#search_content').remove();
            //$('div.osy-textsearch-inline input[type=text]').val('');
        })
    },
    openSearchContainer : function(obj)
    {
        if ($('#search_content').length > 0) {
            return;
        }
        pos = this.calcSearchConteinerPosition(obj);
        div = $('<div id="search_content" class="osy-textsearch-inline-result" style="top:'+(pos.top)+'px; left : '+pos.left+'px; width: '+pos.width+'px; max-height: '+pos.height+'px;"></div>');
        div.on('arrow-up',function(e) {
           if (e) {
                e.preventDefault();
                e.stopPropagation();
            }
            if ($('.row.selected',this).length == 0) {                
                $('.row:last',this).addClass('selected');
            } else if($('.row.selected',this).is(':first-child')){                
                $('.row.selected').removeClass('selected');
                $('.row:last',this).addClass('selected');
            } else {                
                $('.row.selected').removeClass('selected').prev().addClass('selected');
            }
        }).on('arrow-down',function(e) {
            if (e) {
                e.preventDefault();
                e.stopPropagation();
            }
            if ($('.row.selected',this).length == 0) {                
                $('.row:first',this).addClass('selected');
            } else if($('.row.selected',this).is(':last-child')){                
                $('.row.selected').removeClass('selected');
                $('.row:first',this).addClass('selected');
            } else {                
                $('.row.selected').removeClass('selected').next().addClass('selected');
            }
        }).on('click','div.row',function(e){ 
            e.preventDefault();
            parentid = $(this).closest('#search_content').data('parent');            
            $('input[type=hidden]','#'+parentid).val($(this).data('oid'));
            $('input[type=text]','#'+parentid).val($(this).data('label'));
        }).data('parent',$(obj).closest('div').attr('id'));
        $(document.body).append(div);
    },
    calcSearchConteinerPosition : function(parent)
    {
        divPosition = {top: 0, left: 0, width: 0, height: 0}
        parentPosition = $(parent).offset();
        parentPosition.right = parentPosition.left + $(parent).width();
        parentPosition.bottom = parentPosition.top + $(parent).outerHeight();
        parentWidth = $(parent).width();
        windowWidth = $(window).width();
        windowHeight = $(window).height();  
        divPosition.top = parentPosition.bottom;                                
        divPosition.height = Math.max(100,windowHeight - (parentPosition.bottom + 50));
        if (parentPosition.left >= (windowWidth - parentPosition.left)) {
            //Posiziono il SearchContent partendo dall'angolo destro del componente
            divPosition.left = parentPosition.right;
            divPosition.width = parentPosition.right - 50;
        } else {
            //Posizione il SearchContent partendo dall'angolo sinistro del componente
            divPosition.left = parentPosition.left;
            divPosition.width = parentWidth > 500 ? parentWidth : windowWidth - (parentPosition.left + 50);
        }
        return divPosition;
    },
    closeSearchContainer : function(parent)
    {
        alert('chiudi');
    },
    open_child : function(oid,pkey){
        tsc = $('#'+oid);                        
        dbg = oform.get('window').get('sdk-url') ? '1' : ''; 
        form_to_load = '';                      
        $('input[name^="pkey["]').each(function(){
            form_to_load += '&' + $(this).attr('name').replace('pkey','fkey') + '=' + $(this).val();
        });
        if (pkey) {
            form_to_load += '&'+pkey;
        } 
        dim = tsc.data('form-dim').split(',');
        console.log(dim);
        form_arr = [tsc.data('form-pag'),tsc.data('form'),tsc.data('form-nam'),dim[0],dim[1]];
        form_to_load = form_arr[0]+'?fid='+form_arr[1] +'&sid='+$('#osy\\[sid\\]').val() + form_to_load;                        
        d = new Date();
        $('.post-child').each(function(){
            id = $(this).attr('id');
            vl = ($(this).is('[value]') || this.nodeName == 'SELECT') ? $(this).val() : $(this).children('input').val();
            form_to_load += '&par['+this.id+']='+vl;
        });
        form_to_load += '&par[component]='+oid;     
        oform.get('window').open_child(form_to_load,form_arr[2],form_arr[3],form_arr[4],dbg);
        oform.refresh = function(){                
            $('input[type=text]','#'+oid).keyup();
            setTimeout( function() { oform.refresh = function(){ document.forms[0].submit();} }, 1000);
        }
    }
};

if (window.FormController) {
    FormController.register('init','otextsearchinline',function(){ 
        otextsearchinline.init(); 
    });
}