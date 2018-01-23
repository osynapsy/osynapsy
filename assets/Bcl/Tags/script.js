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
            for (i in fld) {
                if ($(fld[i]).val() == '') {
                    alert('Non hai inserito nessun valore impossibile proseguire');
                    return;
                }
                lst.append($(fld[i]).val());
            }
            BclTags.addLabel(lst, $(this).data('parent'));
            $(this).closest('modal').modal('hide');
        }).on('click','.bclTags-delete',function(){
            BclTags.deleteLabel($(this));
        });
    },
    addLabel : function(lbl, par){
        if (lbl.val() === '') {
            alert('Tag field is empty');
            return;
        } else if (this.checkField(lbl.val(), par)) {
            alert('Tag <' + lbl.val() + '> is present');
            return;
        }
        var htmlLabel = ' <span class="label label-default">' + lbl.val();
        htmlLabel += ' <span class="fa fa-close bclTags-delete"></span>';
        htmlLabel += '</span> ';
        $('.bclTags-container', $('div'+par)).append(htmlLabel);
        lbl.val('');
        this.updateField(par);
    },
    checkField : function(val, par)
    {
        var exist = false;
        $('.label', $('div'+par)).each(function(){
            if ($(this).text().trim() == val.trim()) {
                exist = true;
            }
        });
        return exist;
    },
    deleteLabel : function(obj) {
        if (confirm('Are you sure to delete tag')) {
            var parentId = '#' + $(obj).closest('.bclTags').attr('id');
            $(obj).parent().remove();
            this.updateField(parentId);
        }
    },
    updateField : function(par) {
        var val = '';
        $('.label', $('div'+par)).each(function(){
            val += '[' + $(this).text().trim() + ']';
        });
        $('input'+par).val(val);
    }
}

if (window.FormController){    
    FormController.register('init','BclTags',function(){
        BclTags.init();
    });
}