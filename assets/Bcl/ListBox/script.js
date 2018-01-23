olistbox = {
    icoClose : 'glyphicon-chevron-right',
    icoOpen  : 'glyphicon-chevron-down',
    init : function(){
        console.log('ci sono');
        $('.listbox').on('click','.listbox-box',function(){
            var par = $(this).closest('.listbox');
            var width = par.width();            
            $('.listbox-list',par).toggle();
        }).on('click','.listbox-list .listbox-list-item',function(){
            $(this).closest('.listbox-list').toggle();
            var par = $(this).closest('.listbox');
            $('.listbox-list-item',par).removeClass('selected');
            $('input[type=hidden]',par).val($(this).attr('value'));
            $('.listbox-box',par).text($(this).text());
            $(this).addClass('selected');
        }).on('click','.'+this.icoClose,function(e){
            e.stopPropagation();
            $(this).closest('div').next().removeClass('hidden');
            $(this).removeClass(olistbox.icoClose)
                   .addClass(olistbox.icoOpen);
        }).on('click','.'+this.icoOpen,function(e){
            e.stopPropagation();
            $(this).closest('div').next().addClass('hidden');
            $(this).removeClass(olistbox.icoOpen)
                   .addClass(olistbox.icoClose);
        });
        $(window).on('click',function(){
            $('.listbox').each(function(){
               if (!$(this).is(':hover')){
                   $('.listbox-list',this).hide();
               }
            });
        });
        this.initObserve();
    },
    initObserve : function()
    {
        this.observer = new MutationObserver(
            function( mutations ) {
                mutations.forEach(function( mutation ) {
                    console.log( mutation.type, mutation.target, mutation.attributeName );
                });    
            }
        );
        $('.listbox-box').each(function(){
            var config = { attributes: false, childList: true, characterData: false };
            olistbox.observer.observe( this, config );
        });
    },
    observer : null
}

if (window.FormController) {
    FormController.register('init','olistbox.init',function(){
        olistbox.init(); 
    });
}