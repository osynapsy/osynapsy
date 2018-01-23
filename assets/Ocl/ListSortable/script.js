OclListSortable = {
    init : function(){        
        $('.osy-listsortable ul,.osy-listsortable-leaf').sortable({
            handle : '.osy-listsortable-item'
        }).bind('sortupdate', function(e, ui) {
            var lst = $(e.target).closest('.osy-listsortable');           
            var sort = [];
            $('.osy-listsortable-item', lst).each(function(){
                sort.push($(this).data('id'));
            }); 
            $('input[name='+lst.attr('id')+']', lst).val(sort.join(','));
            FormController.execute(lst)       
        });
    }
};

if (window.FormController) {    
    FormController.register('init','OclListSortable.init',function(){     
        OclListSortable.init();        
    });
}