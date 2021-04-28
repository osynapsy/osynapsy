BclFileBox =
{
    init : function()
    {
        Osynapsy.element('body').on('change', ".btn-file input[type='file']", function(event) {
            self = event.target;
            self.closest('.input-group').querySelector("input[type='text']").value = self.files[0].name;
        });
    }
};

if (window.Osynapsy){
    Osynapsy.plugin.register('BclFileBox',function(){
        BclFileBox.init();
    });
}