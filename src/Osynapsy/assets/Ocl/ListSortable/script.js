OclListSortable = {
    init : function(){
        $('.osy-listview').on('click','.command-add',function(event){
            osyview.open_detail_2($(this));     
        });
        $('.osy-listview ul,.osy-listview-leaf').sortable({
            items : 'li.row',
            containment: "parent",
            update : function(e,o) {                     
                var ajax = $(o.item).closest('.osy-listview').attr('id');  
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