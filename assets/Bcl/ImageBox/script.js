ImageBox2 =
{
    init : function ()
    {        
        $(window).resize(function(){
            setTimeout(
                function(){
                    $('img.imagebox-main').each(function(){
                        ImageBox2.initCropBox(this);
                    });
                },
                500
            );
        });
        $('.osy-imagebox-bcl').on('change','input[type=file]',function(e){
            var filepath = this.value;
            var m = filepath.match(/([^\/\\]+)$/);
            var filename = m[1];
            $('.osy-imagebox-filename').text(filename);
            //var uploadAction = $(this).closest('.osy-imagebox-bcl').data('action');            
            FormController.execute($(this).closest('.osy-imagebox-bcl'));
        });
        $('.crop-command').click(function() {
            ImageBox2.crop(this);
        });
        $('.zoomin-command').click(function(){
            var parent = $(this).closest('.crop');
            $('img.imagebox-main', parent).cropper('zoom',0.05);
            var zoom = $(parent).data('zoom') + 0.05;
            $(parent).data('zoom',zoom);
            //ImageBox2.setCropBoxDimension($('img.imagebox-main', parent));
        });
        $('.zoomout-command').click(function(){
            var parent = $(this).closest('.crop');
            $('img.imagebox-main', parent).cropper('zoom',-0.05);
            var zoom = $(parent).data('zoom') - 0.05;
            $(parent).data('zoom',zoom);
            //ImageBox2.setCropBoxDimension($('img.imagebox-main', parent));
        });
        $(window).resize();
    },
    initCropBox : function(img)
    {
        var options = {
            viewMode : 0,
            modal: true,
            dragMode: 'none',
            responsive: true,
            cropBoxResizable : false,
            data : true,
            zoomOnWheel: false,
            crop: function(e) {
                // Output the result data for cropping image.
                var zoom = $(this).closest('.crop').data('zoom') + 0;
                
                var imgData = $(this).cropper('getImageData');
                //var factor = imgData.width / imgData.naturalWidth;
                var crpData = imgData.naturalWidth * zoom + ',';
                    crpData += imgData.naturalHeight * zoom + ',';
                    crpData += (e.x * zoom) + ',';
                    crpData += (e.y * zoom) + ','; 
                    crpData += (e.width * zoom) + ',';
                    crpData += (e.height * zoom);
                $(this).data('action-parameters', crpData);
            }
        };
        $(img).cropper(options).cropper('reset');
        this.setCropBoxDimension(img);
    },
    setCropBoxDimension : function(img)
    {
        var cropWidth = $(img).closest('.crop').data('max-width');
        var cropHeight = $(img).closest('.crop').data('max-height');
        var imgid = $(img).closest('.crop').attr('id');
        var imageProperty = $(img).cropper('getImageData');
        if (imageProperty.naturalWidth > imageProperty.width) {
            var factor = imageProperty.naturalWidth / imageProperty.width;
            cropWidth = cropWidth / factor;
            cropHeight = cropHeight / factor;
        }
        $(img).cropper('setCropBoxData',{width: cropWidth, height: cropHeight, x:0, y:0, resizable : false});
    },
    crop : function(button)
    {
        var image = $('img.imagebox-main', $(button).closest('.crop'));
        FormController.execute(image);
    }
}

if (window.FormController) {
    FormController.register('init','ImageBox2',function() {
        ImageBox2.init();
    });
}
