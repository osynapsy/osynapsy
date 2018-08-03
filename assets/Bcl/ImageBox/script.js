BclImageBox =
{
    init : function ()
    {        
        $(window).resize(function(){
            setTimeout(
                function(){
                    $('img.imagebox-main').each(function(){
                        BclImageBox.initCropBox(this);
                    });
                },
                1000
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
        $('.imagebox-command.crop').click(function() {
            BclImageBox.crop(this);
        });
        $('.imagebox-command.zoomin, .imagebox-command.zoomout').click(function(){
            var factor = $(this).hasClass('zoomout') ? -0.025 : 0.025;
            var parent = $(this).closest('.crop');
            $('img.imagebox-main', parent).cropper('zoom', factor);
            var zoom = $(parent).data('zoom') + factor;
            $(parent).data('zoom',zoom);
        });
        $(window).resize();
    },
    initCropBox : function(img)
    {
        var options = {
            viewMode : 1,
            modal: true,
            dragMode: 'none',
            responsive : true,
            cropBoxResizable : false,
            data : true,
            zoomOnWheel: false,
            built : function(){
                BclImageBox.setCropBoxDimension(this, 1);
            }
        };
        $(img).cropper('destroy');
        $(img).cropper(options);
    },
    setCropBoxDimension : function(img)
    {
        var cropWidth = $(img).closest('.crop').data('max-width');
        var cropHeight = $(img).closest('.crop').data('max-height');
        var imageProperties = $(img).cropper('getImageData');
        if (cropWidth > imageProperties.width) {
            var factor = imageProperties.width / imageProperties.naturalWidth;
            cropWidth = cropWidth * factor;
            cropHeight = cropHeight * factor;
        }
        $(img).cropper('setCropBoxData',{width: cropWidth, height: cropHeight, x:0, y:0});
    },
    crop : function(button)
    {
        var parent =  $(button).closest('div.crop');
        var image = $('img.imagebox-main', parent);
        var cropWidth = $(parent).data('max-width') + 0;
        var cropHeight = $(parent).data('max-height') + 0;
        var imageData = $(image).cropper('getImageData');
        // Output the result data for cropping image.
        var crop = $(image).cropper('getData');        
        var factor = imageData.width / imageData.naturalWidth;
        var factor2 = factor;
        imageData.zoomFactor = imageData.width / imageData.naturalWidth;
        imageData.zoomFactor2 = (imageData.naturalWidth - imageData.width) / imageData.width;
        console.log(crop);
        if (cropWidth > imageData.width) {
            factor = $(parent).data('zoom');
            factor2 = factor < 1 ? 1 : factor;
            crop.width = cropWidth;
            crop.height = cropHeight;
        }
        var data = [
            Math.ceil(imageData.naturalWidth * factor),
            Math.ceil(imageData.naturalHeight * factor),
            Math.ceil(crop.x * factor),
            Math.ceil(crop.y * factor), 
            Math.ceil(crop.width * factor2) - 1,
            Math.ceil(crop.height * factor2) - 1
        ];
        
        var data2 = [
            imageData.zoomFactor2,
            Math.ceil(imageData.width * imageData.zoomFactor2),
            Math.ceil(imageData.height * imageData.zoomFactor2),
            Math.ceil(crop.x),
            Math.ceil(crop.y), 
            Math.ceil(crop.width),
            Math.ceil(crop.height)
        ];
        if (parent.hasClass('debug')) {
            data.push(factor);
            data.push(factor2);
            data.push(crop);
            $('input[type=text]', parent).val(data.join(',')+'['+data2.join(',')+']');
            return;
        }
        $(image).data('action-parameters', data.join(','));
        FormController.execute(image);
    }
}

if (window.FormController) {
    FormController.register('init','BclImageBox',function() {
        BclImageBox.init();
    });
}
