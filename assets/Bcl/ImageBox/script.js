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
        var cropBoxWidth = $(img).closest('.crop').data('maxWidth');
        var cropBoxHeight = $(img).closest('.crop').data('maxHeight');
        //console.log(cropBoxWidth, cropBoxHeight);
        var preserveAspect = $(img).closest('.crop').data('preserveAspectRatio') ? true : false;
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
        var fieldId = $('input[type=file]', parent).attr('id');
        var cropObj = $('img.imagebox-main', parent).rcrop('getValues');
        var resizeWidth = $(parent).data('maxWidth');
        var resizeHeight = $(parent).data('maxHeight');
        var data = [
            cropObj.width,
            cropObj.height,
            cropObj.x,
            cropObj.y,
            fieldId,
            resizeWidth,
            resizeHeight
        ];
        $('img.imagebox-main', parent).data('action-parameters', Array.from(data).join(','));
        Osynapsy.action.execute($('img.imagebox-main', parent)[0]);
    },
    upload : function (input)
    {
        var filepath = input.value;
        var m = filepath.match(/([^\/\\]+)$/);
        var filename = m[1];
        $('.osy-imagebox-filename').text(filename);
        Osynapsy.action.execute($(input).closest('.osy-imagebox-bcl')[0]);
    }
};

if (window.Osynapsy) {
    Osynapsy.plugin.register('BclImageBox2',function() {
        BclImageBox2.init();
    });
}
