OclMultiselect = {
    init : function() {
        $('.osy-multiselect').multiselect();
    }
}

if (window.FormController) {
    FormController.register('init','multiselect-init',function(){
        OclMultiselect.init();
    });
}