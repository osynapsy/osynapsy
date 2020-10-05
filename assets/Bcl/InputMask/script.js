BclInputMaskVanilla =
{
    init : function()
    {
        let self = this;
        document.querySelectorAll('.input-mask').forEach(function(inputMaskElement) {
            IMask(inputMaskElement, self[inputMaskElement.dataset.imask]());
        });
    },
    Number : function()
    {
        return {
            mask: Number,
            radix: '.',
            thousandsSeparator: ''
        };
    },
    Currency : function()
    {
        return {
            mask: Number,
            radix: '.',
            thousandsSeparator: ''
        };
    },
    Date : function()
    {
        return {
            mask: Date,
            pattern: 'd{/}`m{/}`Y',
            format: function (date)
            {
                var day = date.getDate();
                var month = date.getMonth() + 1;
                var year = date.getFullYear();
                if (day < 10) day = "0" + day;
                if (month < 10) month = "0" + month;
                return [day, month, year].join('/');
            },
            parse: function (str)
            {
                var yearMonthDay = str.split('/');
                return new Date(yearMonthDay[2], yearMonthDay[1] - 1, yearMonthDay[0]);
            },
            autofix: true,
            lazy: false,
            overwrite: true
        };
    }
};

if (window.Osynapsy){
    Osynapsy.plugin.register('BclInputMaskVanilla',function(){
        BclInputMaskVanilla.init();
    });
}