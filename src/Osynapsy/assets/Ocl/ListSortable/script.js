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
    },
    initRow : function()
    {
        $('.osy-listview').on('click','.osy-listview-item div.cmd',function(e){
            if ($(this).hasClass('__f')) {
                return;
            }
            if ($(this).closest('.osy-listview').data('form') === 'undefined') {
                return;
            }
            if (window.osy) {
                osypage.open_detail_2($(this).closest('li'));
            } else if (window.osyview) {
                osyview.open_detail_2($(this).closest('li'));
            }
            e.preventDefault();
        });
    }	
}

if (window.FormController) {    
    FormController.register('init','OclListSortable.init',function(){
        OclListSortable.initRow();
        OclListSortable.init();        
    });
}