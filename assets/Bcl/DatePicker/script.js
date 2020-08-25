BclDatePicker =
{
    init : function()
    {
        $('.date-picker').each(function(){
            var self = this;
            var opt = {
                format: $(this).data('format'),
                //Serve ad evitare l'autocompilazione con la data odierna se il campo è vuoto.
                useCurrent: false
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
        $('body').on('dp.change', '.datepicker-change', function(){
            if ($(this).hasClass('change-execute')) {
                Osynapsy.action.execute(this);
            }
            $(this).trigger('change');
        });
    }
};

if (window.Osynapsy){
    Osynapsy.plugin.register('BclDatePicker',function(){
        BclDatePicker.init();
    });
}