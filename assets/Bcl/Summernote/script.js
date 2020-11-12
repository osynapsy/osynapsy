BclSummernote =
{
    init : function()
    {
        $('.summernote').each(function(){
            var self = this;
            var vheight = Osynapsy.isEmpty($(this).data('height')) ? 300 : $(this).data('height');
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
                    onImageUpload: function(files){
                        Osynapsy.action.execute(this);
                        //BclSummernote.upload(files[0], editor, welEditable);
                    }
                },
                height: vheight,
                tabsize: 4,
                emptyPara: '<div><br /></div>'
            });
        });
    }
};

$(document).ready(function() {
    BclSummernote.init();
});
