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
                    //$(".summernote").val($(this).code());
                },
                onblur: function(e) {
                    var phpkey1 = "&lt;"+"?php",
                        phpkeyend = "?&gt;",
                        stylekey1 = "&lt;style&gt;",
                        stylekey2 = "&lt;style type=\"text/css\"&gt;",
                        stylekeyend = "&lt;/style&gt;",
                        scriptkey1 = "&lt;script&gt;",
                        scriptkey2 = "&lt;script type=\"text/javascript\"&gt;",
                        scriptkeyend = "&lt;/script&gt;";

                    var code = $(this).code();

                    code = $.trim(code)
                      .replace(/<!--\?php/g, phpkey1)
                      .replace(/\?-->/g, phpkeyend)
                      .replace(/<style>/g, stylekey1)
                      .replace(/<style type="text\/css">/g, stylekey2)
                      .replace(/<\/style>/g, stylekeyend)
                      .replace(/<script>/g, scriptkey1)
                      .replace(/<script type="text\/javascript">/g, scriptkey2)
                      .replace(/<\/script>/g, scriptkeyend);

                    var content = $("textarea[name='"+$(this).attr('id')+"']").html(code);
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


