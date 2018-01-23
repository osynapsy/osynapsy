BclDatePicker = 
{
    init : function()
    {
        $('.date-picker').each(function(){
            var self = this;
            var opt = {
                format: $(this).data('format')
            };
            var minDate = $(this).data('min');
            if (typeof minDate !== 'undefined') {
                if (minDate.charAt(0) === '#') {
                    $(minDate).on("dp.change", function (e) {
                         $(self).data("DateTimePicker").minDate(e.date);
                    });
                } else {
                    opt['minDate'] = new Date(minDate);
                }
            }            
            var maxDate = $(this).data('max');
            if (typeof maxDate !== 'undefined') {
                if (maxDate.charAt(0) === '#') {
                    $(maxDate).on("dp.change", function (e) {
                        $(self).data("DateTimePicker").maxDate(e.date);
                    });
                } else {
                    opt['maxDate'] = new Date(maxDate);
                }
            }
            $(this).datetimepicker(opt);
        });
    }
};

if (window.FormController){    
    FormController.register('init','BclDatePicker_init',function(){
        BclDatePicker.init();
    });
}