BclDataGrid = 
{
    init : function()
    {
        $('.bcl-datagrid').on('click tap','.row',function(){
            if (!$(this).data('url-detail')) {
                return;
            }
            FormController.saveHistory();
            window.location = $(this).data('url-detail');            
        });
    }
};

if (window.FormController){    
    FormController.register('init','BclDataGrid_Init',function(){
        BclDataGrid.init();
    });
}


