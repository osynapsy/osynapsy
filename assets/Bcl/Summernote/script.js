BclSummernote =
{
    init : function()
    {
        $('.summernote').each(function(){
            var upath = $(this).attr('uploadpath');
            if (upath) {
                BclSummernote.uploadPath = upath;
            }
            var self = this;
            $(this).summernote({
                callbacks: {
                    onkeyup: function(e) {
                        //$(".summernote").val($(this).code());
                    },
                    onInit : function(e) {
                        var code = $(self).text().replace(/<\?/g,'&lt;?').replace(/\?>/g,'?&gt;');
                        $(self).summernote('reset');
                        $(self).summernote('code', code);                        
                    },
                    onImageUpload: function(files, editor, welEditable){
                        BclSummernote.upload(files[0], editor, welEditable);
                    }
                },
                height: 300,
                tabsize: 4,
            });
        });
    },
    upload : function(file, editor, welEditable)
    {
        var data = new FormData();
        data.append("file", file);
        $.ajax({
            data: data,
            type: "POST",
            url: this.uploadPath,
            cache: false,
            contentType: false,
            processData: false,
            success: function(url) {
                editor.insertImage(welEditable, url);
                setTimeout(
                    function() {
                        $(".summernote").val($('.summernote').summernote().code());
                    },
                    500
                );
                
            }
        });
    },
    uploadPath : ''
}

$(document).ready(function() {
    BclSummernote.init();
});


