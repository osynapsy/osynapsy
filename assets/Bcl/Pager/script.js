BclPager = {
    init : function() {
        $('.BclPager').each(function() {
            
            if ($(this).hasClass('infinitescroll')) {
                console.log('init');
                $(document).imagesLoaded(function() {
                    BclPager.infiniteScrollInit($('.infinitescroll'));
                });
            }                      
            $('body').on('click','.BclPager a',function(e){
                e.preventDefault();
                var par = $(this).closest('.BclPager');
                var hdn = $('input[type=hidden]:first-child', par);
                hdn.val($(this).data('value'));
                $(this).closest('form').submit();                
            });            
        });
    },
    infiniteScrollInit : function(obj) {
        console.log('infiniteScrollInit');
        var oid = $(obj).attr('id');
        var cnt = $(obj).data('container');
        $(cnt).wookmark({offset : 0, 
            resizeDelay: 250,
            outerOffset : -10,
            align : 'center', 
            itemWidth : 0, 
            autoResize: true
        });
        $(obj).hide();
        $(window).scroll($.debounce( 50, function(){ 
            BclPager.infiniteScroll('#'+oid); 
        }));
    },
    infiniteScroll : function(oid) {
        var hdn = $('input[type=hidden]',$(oid));
        var cnt = $($(oid).data('container'));
        if (cnt.hasClass('infinite-loading-pending')) {
            return;
        }
        var scrollPercent = 100 * $(window).scrollTop() / ($(document).height() - $(window).height());
        if (scrollPercent < 99) {
            return;
        }
        var curPage = hdn.val() ? parseInt(hdn.val()) : 1;
        var nxtPage = curPage + 1;
        if (nxtPage > parseInt($(oid).data('page-max'))) {
            console.log('max page arrived');
            return;
        }
        hdn.val(nxtPage);
        var dat = $(oid).closest('form').serialize();
        $.ajax({
            type : 'post',
            data : dat,
            dataType : 'html',
            context : cnt,
            success : function(resp) {
                var containerId = '#' + $(this).attr('id');
                $items = $(containerId, resp).html();
                $(this).append($items);
                if (!$(this).hasClass('index-isotope')) {
                    return;
                }
                $('.infinite-loading-pending').imagesLoaded(function(){
                    $('.infinite-loading-pending').wookmark( {offset : 0, resizeDelay: 0, outerOffset : -10, align : 'center', itemWidth : 0, autoResize: true});
                    $('.infinite-loading-pending').removeClass('infinite-loading-pending');
                });
                setTimeout(function(){
                    
                }, 1000);
            },
            beforeSend: function(){
                $(this).addClass('infinite-loading-pending');
            },
            error : function(){
                $(this).removeClass('infinite-loading-pending');
            }
        });
    }
}

if (window.FormController) {
    FormController.register('init','BclPager.init',function(){
        BclPager.init();        
    });
}


