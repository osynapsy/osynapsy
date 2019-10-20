/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
Bcl4NavBar = 
{
    init : function()
    {
        /*$('.osy-bcl4-navbar').on('click','.dropdown-item-submenu',function(){
            $(this).offset();
            $(this).next().toggleClass('.d-none').style;
        });*/
        $('.dropdown-menu a.dropdown-toggle').on('click', function(e) {
            if (!$(this).next().hasClass('show')) {
                $(this).parents('.dropdown-menu').first().find('.show').removeClass("show");
            }
            var $subMenu = $(this).next(".dropdown-menu");
            $subMenu.toggleClass('show');

            $(this).parents('li.nav-item.dropdown.show').on('hidden.bs.dropdown', function(e) {
                $('.dropdown-submenu .show').removeClass("show");
            });
            return false;
        });
    }
};

if (window.FormController) {
    FormController.register('init','osy-bcl4-navbar',function(){
        Bcl4NavBar.init();
    });
}
