OclSelect = {
    init : function() {
        $('.osy-select').selectpicker();
    }
}

if (window.FormController) {
    FormController.register('init','select-init',function(){
        OclSelect.init();
    });
}
