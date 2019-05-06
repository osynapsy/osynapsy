BclImageBox2 =
{
    init : function ()
    {        
        $(window).resize(function(){
            setTimeout(
                function(){
                    $('img.imagebox-main').each(function(){
                        BclImageBox2.initCropBox(this);
                    });
                },
                1000
            );
        });
        $('.osy-imagebox-bcl').on('change','input[type=file]',function(e){
            BclImageBox2.upload(this);
        }).on('click','.crop-command', function(){            
            BclImageBox2.crop(this);
        }).on('click','.zoomin-command, .zoomout-command', function(){
            BclImageBox2.zoom(this);
        });
        $(window).resize();
    },
    initCropBox : function(img)
    {
        var cropBoxWidth = $(img).closest('.crop').data('max-width');
        var cropBoxHeight = $(img).closest('.crop').data('max-height');
        $(img).rcrop({
            minSize : [cropBoxWidth, cropBoxHeight],
            //maxSize : [cropBoxWidth, cropBoxHeight],
            preserveAspectRatio : true,
            grid : true    
        });        
    },
    zoom : function(button)
    {
        var parent = $(button).closest('.osy-imagebox-bcl');
        var factor = $(button).hasClass('zoomout-command') ? -0.05 : 0.05;
        var data = $('img.imagebox-main', parent).rcrop('getValues');        
        var params = [
            data.width * (1 + factor),
            data.height * (1 + factor),
            data.x,
            data.y
        ];        
        $('img.imagebox-main', parent).rcrop('resize', params[0], params[1], params[2], params[3]);        
    },
    crop : function(button)
    {
        var parent = $(button).closest('.osy-imagebox-bcl');
        var cropObj = $('img.imagebox-main', parent).rcrop('getValues');
        var data = [
            cropObj.width,
            cropObj.height,
            cropObj.x,
            cropObj.y
        ];
        $('img.imagebox-main', parent).data('action-parameters', Array.from(data).join(','));
        FormController.execute($('img.imagebox-main', parent));
    },
    upload : function (input)
    {
        var filepath = input.value;
        var m = filepath.match(/([^\/\\]+)$/);
        var filename = m[1];
        $('.osy-imagebox-filename').text(filename);        
        FormController.execute($(input).closest('.osy-imagebox-bcl'));
    }
};

if (window.FormController) {
    FormController.register('init','BclImageBox2',function() {
        BclImageBox2.init();
    });
}
