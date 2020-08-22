olistbox = {
    icoClose : 'fa-chevron-right',
    icoOpen  : 'fa-chevron-down',
    init : function(){
        $('.listbox').on('click','.listbox-box',function(){
            var par = $(this).closest('.listbox');
            var width = par.width();            
            $('.listbox-list',par).css('width', width+'px').toggle();
        }).on('click','.listbox-list .listbox-list-item',function(){
            $(this).closest('.listbox-list').toggle();
            var par = $(this).closest('.listbox');
            $('.listbox-list-item',par).removeClass('selected');
            $('input[type=hidden]',par).val($(this).attr('value'));
            $('.listbox-box',par).text($(this).text());
            $(this).addClass('selected');
        }).on('click','.'+this.icoClose,function(e){
            e.stopPropagation();
            $(this).closest('div').next().removeClass('hidden').removeClass('d-none');
            $(this).removeClass(olistbox.icoClose)
                   .addClass(olistbox.icoOpen);
        }).on('click','.'+this.icoOpen,function(e){
            e.stopPropagation();
            $(this).closest('div').next().addClass('hidden').addClass('d-none');
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
                    //console.log( mutation.type, mutation.target, mutation.attributeName );
                    var onchange = $(mutation.target).closest('.listbox').attr('onchange');
                    if (onchange) {
                        eval(onchange);
                    }
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

if (window.Osynapsy) {
    Osynapsy.plugin.register('olistbox',function(){
        olistbox.init(); 
    });
}