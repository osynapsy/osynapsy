BclFileBox = 
{
    init : function()
    {
        $('.btn-file :file').on('fileselect', function(event, numFiles, label) {
            console.log(numFiles);
            console.log(label);
            $('input[type=text]',$(this).closest('.input-group')).val(label);
        });
        $(document).on('change', '.btn-file :file', function() {
            var input = $(this),
                numFiles = input.get(0).files ? input.get(0).files.length : 1,
                label = input.val().replace(/\\/g, '/').replace(/.*\//, '');
            input.trigger('fileselect', [numFiles, label]);
        });
    }
}

if (window.FormController){    
    FormController.register('init','BclFileBox_Init',function(){
        BclFileBox.init();
    });
}