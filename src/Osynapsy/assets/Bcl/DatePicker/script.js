BclDatePicker = 
{
    init : function()
    {
        $('.date-picker').each(function(){
            console.log('ci sono');
            var parStartDate = $(this).data('start-date');
            var parEndDate = $(this).data('end-date');
            $(this).datetimepicker({                
                format: 'DD/MM/YYYY'                
            });
        });
    }
};

if (window.FormController){    
    FormController.register('init','BclDatePicker_Init',function(){
        BclDatePicker.init();
    });
}