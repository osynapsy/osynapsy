SortableList = {
    repo : {
        dragElement : null
    },
    init : function() {
        $('.sortable-list').on('click','.sortable-list-item-plus',function(){
            var li = $(this).closest('li');
            var par = $('#' + $(this).closest('ul').data('connected'));
            SortableList.buildHidden(li);
            par.append(li);
        }).on('click','.sortable-list-item-minus',function(){
            var li = $(this).closest('li');
            $('input[type=hidden]',li).remove();
            var par = $('#'+li.data('source'));
            par.append(li);
        });
        $('.sortable-list').each(function(){
            $(this).on('drop',function(ev){
                var par = $(ev.target).closest('.sortable-list');
                var elm = SortableList.repo.dragElement;
                if (par.hasClass('sortable-list-destination')) {
                    SortableList.buildHidden(elm);
                } else {
                   $('input[type=hidden]',elm).remove();
                }
                if ($(ev.target)[0].nodeName == 'UL') {
                    $(ev.target).append(SortableList.repo.dragElement);
                } else {
                    $(ev.target).closest('li').after(SortableList.repo.dragElement);
                }
            }).on('dragover',function(ev){
                ev.preventDefault();
            });
            $('li',this).on('dragstart',function(){
                if (!$(this).data('parent')) {
                    $(this).data('parent',$(this).closest('ul'));
                    console.log($(this).closest('ul').attr('id'));
                }
                SortableList.repo.dragElement = this;
            }).attr('draggable',true);
        });
    },
    buildHidden : function(elm) {
        var nam = $('span.label',elm).text();
        if (nam) {
            $(elm).append('<input type="hidden" name="'+nam+'[]" value="'+$(elm).data('value')+'">');
        }
    }
}

if (window.FormController) {
    FormController.register('init','SortableList',function(){
        SortableList.init();
    });
}