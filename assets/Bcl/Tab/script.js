BclTab = {
    init : function()
    {
        $('.nav-tabs').each(function(){                      
           $('a',this).click(function(){
               var tabid = $(this).attr('href');
               var hdnid = $(this).closest('ul').attr('id').replace('_nav','');
               $('#'+hdnid).val(tabid);
               //$('input[type=hidden]',$(this).closest('div')).val(tabid);
           });
        });
    }
}

if (window.FormController){    
    FormController.register('init','BclTab_Init',function(){
        BclTab.init();
    });
}