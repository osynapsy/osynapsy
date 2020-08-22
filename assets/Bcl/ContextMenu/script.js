BclContextMenu = 
{
    init : function()
    {
  
        $("body").on("contextmenu", ".BclContextMenuOrigin", function(e) {
           
            var $contextMenu = $('#' +  $(this).data('bclcontextmenuid'));
            
            $contextMenu.css({
                display: "block",
                left: e.pageX,
                top: e.pageY
            });
            var param = $(this).data('action-param') ? $(this).data('action-param') : '';
            $contextMenu.data('action-param',param);
            return false;
        });
        
        $('.BclContextMenu').each(function(){
            $(this).on("click", "a", function() {
                Osynapsy.action.exec(
                    $(this).data('action'), 
                    'actionParameter=' + $(this).closest('.BclContextMenu').data('action-param')
                );
                $(this).closest('.BclContextMenu').hide();
            });
        });
    }
}

if (window.Osynapsy){    
    Osynapsy.plugin.register('init','BclContextMenu_Init',function(){
        BclContextMenu.init();
    });
}