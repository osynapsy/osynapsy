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
            Osynapsy.history.save();
            evt.stopPropagation();
        });
    }
}

if (window.FormController){    
    FormController.register('init','BclAdressbook_Init',function(){
        BclAddressbook.init();
    });
}


