OclListSortable = {
    init : function(){        
        $('.osy-listsortable ul,.osy-listsortable-leaf').sortable({
            items : 'li.row',
            containment: "parent",
            afterMove : function(e,o) {                 
                var ajax = $(o).closest('.osy-listsortable').attr('id');  
                console.log(o);
                if (ajax == 'undefined') {
                    return;
                }                         
                var ipar = $(o.item).closest('ul');
                 //sort = ipar.sortable('serialize',{attribute :'id',connected:false});
                var sort = '';
                $(ipar).children().each(function(){
                    sort += (sort == '' ? '' : '&') + ajax +'[]='+$(this).attr('oid');
                });
                console.log(sort);
                if (sort == '') {
                    return;
                }
                sort += $('form').serialize()+'&ajax='+ajax;
                $.ajax({ 
                    data : sort, 
                    type : 'post', 
                    success : function(rsp) {
                        if (rsp !== 'OK') {
                            alert(rsp);
                        }
                    }
                });
            }
        });
    }    
}

if (window.FormController) {    
    FormController.register('init','OclListSortable.init',function(){     
        OclListSortable.init();        
    });
}