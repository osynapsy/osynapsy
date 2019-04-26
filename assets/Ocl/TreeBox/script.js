OclTreeBox = 
{
    init : function()
    {
        var treeboxParent = $('.osy-treebox').parent();
        $(treeboxParent).on('click','span.osy-treebox-branch-command',function(){
            OclTreeBox.toggleBranch($(this));
        }).on('click','span.osy-treebox-label', function(){
            OclTreeBox.clickLabel($(this));
        });
    },
    toggleBranch : function(elm)
    {             
        var box = $(elm).closest('.osy-treebox');
        var nodeId = $(elm).closest('.osy-treebox-node').data('nodeId');        
        var hdnOpenNodes = $('input.openNodes', box);
        var strOpenNodes = hdnOpenNodes.val();
        if ($(elm).hasClass('minus')){
           hdnOpenNodes.val(strOpenNodes.replace('['+nodeId+']',''));
        } else {
           hdnOpenNodes.val(strOpenNodes + '['+nodeId+']');
        }        
        $(elm).toggleClass('minus');
        $(elm).parent().next().toggleClass('hidden');
    },
    clickLabel : function(elm)
    {        
        var box = $(elm).closest('.osy-treebox');
        $('span.osy-treebox-label', box).removeClass('osy-treebox-label-selected');                   
        var curNodeId = String($(elm).closest('.osy-treebox-node').data('nodeId'));
        var selNodeId = $('input.selectedNode', box).val();        
        if (curNodeId !== selNodeId) {
            $(elm).addClass('osy-treebox-label-selected');
            $('input.selectedNode', box).val(curNodeId);
        } else {
            $('input.selectedNode', box).val('');
        }
    }
};

if (window.FormController) {
    FormController.register('init','OclTreeBox',function(){
        OclTreeBox.init();
    });
}