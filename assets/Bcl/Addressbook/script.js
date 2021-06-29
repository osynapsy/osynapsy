BclAddressbook =
{
    init : function()
    {
        $('.osy-addressbook').parent().on('click','.osy-addressbook-item',function(){
            var selected = $(this).hasClass('osy-addressbook-item-selected');
            $('input[type=checkbox]', this).prop('checked',!selected);
            $(this).toggleClass('osy-addressbook-item-selected');
        });
        $('.osy-addressbook').parent().on('click','a.osy-addressbook-link',function(evt){
            //evt.stopPropagation();
        });
    }
}

if (window.Osynapsy){
    Osynapsy.plugin.register('BclAdressbook',function() {
        BclAddressbook.init();
    });
}
