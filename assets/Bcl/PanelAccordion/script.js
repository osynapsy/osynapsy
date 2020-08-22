/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
BclPanelAccordion = 
{
    init : function()
    {
        $('body').on('click','.osy-panel-accordion .panel-heading a',function(){
            var panelId = $(this).data('panel-id');
            var openId = $(this).closest('.panel-group').children('input').val();
            $(this).closest('.panel-group').children('.expanded').removeClass('expanded');
            if (panelId !== openId) {
                $(this).closest('.panel-group').children('input').val(panelId); 
                $(this).closest('.panel').addClass('expanded');
            }            
        });
    }
};

if (window.Osynapsy) {
    Osynapsy.plugin.register('BclPanelAccordion',function(){
        BclPanelAccordion.init();
    });
}

