BclSummernote =
{
    init : function()
    {
        $('.summernote').each(function(){
            var self = this;
            var vheight = Osynapsy.isEmpty($(this).data('height')) ? 300 : $(this).data('height');
            let toolbarButtons = BclSummernote.buildToolbarButtonsParameters($(this).data('toolbarButtons'));
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
                emptyPara: '<div><br /></div>',
                toolbar : toolbarButtons
            });
        });
    },
    buildToolbarButtonsParameters : function(toolbarButtonsString)
    {
        let result = [
            ['style', ['style']],
            ['font', ['bold', 'underline', 'clear']],
            ['fontname', ['fontname']],
            ['color', ['color']],
            ['para', ['ul', 'ol', 'paragraph']],
            ['table', ['table']],
            ['insert', ['link', 'picture', 'video']],
            ['view', ['fullscreen', 'codeview', 'help']],
        ];
        if (Osynapsy.isEmpty(toolbarButtonsString)) {
            return result;
        }
        let toolbarButtonsRaw = toolbarButtonsString.split(',');
        if (Osynapsy.isEmpty(toolbarButtonsRaw)) {
            return result;
        }
        for (let i in toolbarButtonsRaw) {
            let buttonRaw = toolbarButtonsRaw[i].split('-', 2);
            let groupIdx = result.findIndex((element) => element === buttonRaw[0]);
            if (Osynapsy.isEmpty(groupIdx)) {
                result[groupIdx][1].push(buttonRaw[1]);
            } else {
                result.push([buttonRaw[0], [buttonRaw[1]]]);
            }
        }
        return result;
    }
};

$(document).ready(function() {
    BclSummernote.init();
});
