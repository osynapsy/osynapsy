BclImageBox =
{
    init : function ()
    {        
        $(window).resize(function(){
            setTimeout(
                function(){
                    $('img.imagebox-main').each(function(){
                        $(this).cropper('reset');
                        BclImageBox.setCropDimension($(this));                        
                    });
                },
                300
            );
        });
        $('.osy-imagebox-bcl').on('change','input[type=file]',function(e){
            var filepath = this.value;
            var m = filepath.match(/([^\/\\]+)$/);
            var filename = m[1];
            $('.osy-imagebox-filename').text(filename);
            var uploadAction = $(this).closest('.osy-imagebox-bcl').data('action');            
            FormController.execute($(this).closest('.osy-imagebox-bcl'));
        });
        $('img.imagebox-main').each(function() {
            var cropWidth = $(this).closest('.crop').data('max-width');
            var cropHeight = $(this).closest('.crop').data('max-height');
            $(this).cropper({
                viewMode : 0,
                modal: true,
                dragMode: 'none',
                cropBoxResizable : false,
                data : true,
                zoomOnWheel: false,
                minCropBoxWidth:cropWidth,
                minCropBoxHeight:cropHeight,
                crop: function(e) {
                    // Output the result data for cropping image.
                    var imgData = $(this).cropper('getImageData');
                    var factor = imgData.width / imgData.naturalWidth;
                    var crpData = imgData.width + ',';
                        crpData += imgData.height + ',';
                        crpData += (e.x * factor) + ',';
                        crpData += (e.y * factor) + ','; 
                        crpData += (e.width * factor) + ',';
                        crpData += (e.height * factor);
                    //$(this).data('action','crop');
                    $(this).data('action-parameters', crpData);
                },
                built : function() {
                    $(this).cropper('setCropBoxData',{width: cropWidth, height: cropHeight, x:0, y:0});
                }
            });                          
        });
        $('.crop-command').click(function() {
            var par = $(this).closest('.crop');
            FormController.execute($('img.imagebox-main', par));            
        });
        $('.zoomin-command').click(function(){
            var par = $(this).closest('.crop');
            $('img.imagebox-main', par).cropper('zoom',0.1);            
        });
        $('.zoomout-command').click(function(){
            var par = $(this).closest('.crop');
            $('img.imagebox-main', par).cropper('zoom',-0.1);            
        });
    },
    setCropDimension : function(img){
        var cropWidth = $(img).closest('.crop').data('max-width');
        var cropHeight = $(img).closest('.crop').data('max-height');  
        img.cropper('setCropBoxData',{width: cropWidth, height: cropHeight, x:0, y:0});
    },
    crop : function(obj)
    {
        FormController.execute($('img', obj));
    }
}

if (window.FormController) {
    FormController.register('init','BclImageBox',function() {
        BclImageBox.init();
    });
}
