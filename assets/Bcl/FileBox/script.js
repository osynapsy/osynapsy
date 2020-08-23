BclFileBox = 
{
    init : function()
    {
        $('.btn-file :file').on('fileselect', function(event, numFiles, label) {            
            $('input[type=text]',$(this).closest('.input-group')).val(label);
        });
        $(document).on('change', '.btn-file :file', function() {
            let input = $(this);
            let numFiles = input.get(0).files ? input.get(0).files.length : 1;
            let label = input.val().replace(/\\/g, '/').replace(/.*\//, '');
            input.trigger('fileselect', [numFiles, label]);
        });
    }
}

if (window.Osynapsy){    
    Osynapsy.plugin.register('BclFileBox',function(){
        BclFileBox.init();
    });
}