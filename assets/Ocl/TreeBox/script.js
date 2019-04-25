OclTreeBox = {
    init : function()
    {
        $('.osy-treebox').on('click','span.osy-treebox-branch-command',function(){
            $(this).toggleClass('minus').parent().next().toggleClass('hidden');
        });
    }
};

if (window.FormController) {
    FormController.register('init','OclTreeBox',function(){
        OclTreeBox.init();
    });
}