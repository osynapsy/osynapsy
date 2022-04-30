Bcl4DatePicker =
{
    init : function()
    {
        $('.date-picker').each(function(){
            var self = this;
            var opt = {
                format: $(this).data('date-format'),
                toolbarPlacement : 'top',
                buttons : {
                    showToday: true,
                    showClear: true,
                    showClose: true
                },
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
        $('body').on('change.datetimepicker', function(e) {
            let self = e.target;
            if ($(self).attr('onchangedate')) {
                eval($(self).attr('onchangedate'));
            }
            if ($(self).hasClass('change-execute')) {
                Osynapsy.action.execute(self);
            }
        });
    }
};

if (window.Osynapsy){
    Osynapsy.plugin.register('Bcl4DatePicker',function(){
        Bcl4DatePicker.init();
    });
}


