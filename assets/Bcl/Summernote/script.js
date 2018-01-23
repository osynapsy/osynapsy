BclSummernote =
{
    init : function()
    {
        $('.summernote').each(function(){
            if (upath = $(this).attr('uploadpath')) {
                BclSummernote.uploadPath = upath;
            }
            $(this).summernote({
                onkeyup: function(e) {
                    $(".summernote").val($(this).code());
                },
                onImageUpload: function(files, editor, welEditable) {
                    BclSummernote.upload(files[0], editor, welEditable);
                },
                height: 300,
            })
        });
    },
    upload : function(file, editor, welEditable)
    {
        data = new FormData();
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


