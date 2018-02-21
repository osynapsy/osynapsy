BclTags = {
    init : function() {        
        $('.bclTags').on('click','.bclTags-add',function(){
            if ($(this).hasClass('cmd-execute')){
                return;
            }
            if (!$(this).data('fields')) {
                alert('Attributo data-fields non presente');
                return;
            }
            var fld = $(obj).data('fields').split(',');
            var lst = [];
            for (var i in fld) {
                if ($(fld[i]).val() == '') {
                    alert('Non hai inserito nessun valore impossibile proseguire');
                    return;
                }
                lst.append($(fld[i]).val());
            }
            BclTags.addTag($(this).data('parent'));
            $(this).closest('modal').modal('hide');
        }).on('click','.bclTags-delete',function(){
            BclTags.deleteTag($(this));
        });
    },
    addTag : function(tagContainerId)
    {
        var tagInput = $('input[type=text]', $(tagContainerId));
        var tagValue = tagInput.val();
        if (Osynapsy.isEmpty(tagValue)) {
            alert('Tag field is empty');
            return;
        } else if (this.checkTagExists(tagValue, tagContainerId)) {
            alert('Tag <' + tagValue + '> is present');
            return;
        }
        var tagItem = ' <span class="label label-default">' + tagValue + ' <span class="fa fa-close bclTags-delete"></span></span> ';
        $('.bclTags-container', $('div'+tagContainerId)).append(tagItem);        
        tagInput.val('');        
        this.updateHiddenField(tagContainerId);
    },
    checkTagExists : function(tagValue, tagContainerId)
    {
        var exists = false;
        $('.label', $('div'+tagContainerId)).each(function(){
            if ($(this).text().trim() == tagValue.trim()) {
                exists = true;
            }
        });
        return exists;
    },
    deleteTag : function(tagItem) {
        if (confirm('Are you sure to delete tag')) {
            var parentId = '#' + $(tagItem).closest('.bclTags').attr('id');
            $(tagItem).parent().remove();
            this.updateHiddenField(parentId);
        }
    },
    updateHiddenField : function(tagContainerId) {
        var values = '';
        $('.label', $('div'+tagContainerId)).each(function(){
            values += '[' + $(this).text().trim() + ']';
        });
        $('input'+tagContainerId).val(values);
    }
}

if (window.FormController){    
    FormController.register('init','BclTags',function(){
        BclTags.init();
    });
}