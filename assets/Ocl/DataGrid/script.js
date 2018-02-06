OTree = {
    branchClose : function(gid)
    {
        $('tr[gid="'+gid+'"]').each(function(){
            OTree.branchClose($(this).attr('oid'));
            $(this).addClass('hide');
        });
    },
    branchOpen : function(gid)
    {
        if ($('tr[oid="'+gid+'"]').is(':visible')){ //Serve a bloccare le aperture su refresh
            $('tr[gid="'+gid+'"]').each(function(){
                $(this).removeClass('hide');
                if ($(this).attr('__state') === 'open') {
                    OTree.branchOpen($(this).attr('oid'));
                }
            });
        }
    },
    branchOpen2 : function(obj)
    {
        var gid = obj.attr('gid');
        $('tr[gid="'+gid+'"]').each(function(){
             $(this).addClass('hide');
        });
        $('tr[oid="'+gid+'"]').each(function(){
            $(this).attr('__state','open');
            $('span[class*=tree-plus]',this).addClass('minus');
            OTree.branchOpen2($(this));
        });
    },
    checkOpen : function()
    {
        $('div.osy-treegrid').each(function(){
            $('input[type=checkbox]:checked',this).each(function(){
                OTree.branchOpen2($(this).closest('tr'));
            });
        });
    },
    parentOpen : function()
    {
        $('div.osy-treegrid').each(function(){
            var did = $(this).attr('id');
            var sel = $('#'+did+'_sel',this).val().split('][')[0];
            if (sel){
                sel = sel.replace('[','').replace(']','');
                $('tr[oid="'+sel+'"]',this).addClass('sel');
            }
            var obj_opn = $('input[name='+ $(this).attr('id') + '_open]');
            var val_opn = obj_opn.val().split('][');
            for (var i in val_opn) {
                var gid = val_opn[i].replace('[','').replace(']','');
                $('tr[oid="'+gid+'"]').attr('__state','open');
                $('span[class*=tree-plus]','tr[oid="'+gid+'"]').addClass('minus');
                OTree.branchOpen(gid);
            }
        });
    },    
    init : function()
    {
        OTree.parentOpen();
        OTree.checkOpen();
        $('.osy-datagrid-2').on(
            'click',
            'span.tree',
            function (event){
                event.stopPropagation();
                var tr = $(this).closest('tr');
                var dt = $(this).closest('div.osy-datagrid-2');
                var obj_opn = $('input[name='+ dt.attr('id') + '_open]');
                var val_opn = obj_opn.val();
                var gid = tr.attr('oid');
                if ($(this).hasClass('minus')){
                    obj_opn.val(val_opn.replace('['+gid+']',''));
                    OTree.branchClose(gid);
                    tr.attr('__state','close');
                } else {
                    obj_opn.val(val_opn+'['+gid+']');
                    OTree.branchOpen(gid);
                    tr.attr('__state','open');
                }
                $(this).toggleClass('minus');
            }
        );
    }
};

ODataGrid = 
{
    init : function()
    {
        this.initOrderBy();
        this.initPagination();
        OTree.init();
        this.initAdd();
        $('.osy-datagrid-2').each(function(){
            this.refresh = function() {ODataGrid.refreshAjax(this);}
        });
    },    
    initAdd : function()
    {
        $('.osy-datagrid-2 .cmd-add').click(function(){
            Osynapsy.history.save();
            window.location = $(this).data('view');
        });
    },
    initOrderBy : function(){
        $('.osy-datagrid-2').on('click','th:not(.no-ord)',function(){
            if (!$(this).data('ord')) {
                return;
            }
            var grid = $(this).closest('.datagrid');
            var gridId = grid.attr('id');
            var orderFld = $('#'+gridId+'_order');
            var orderVal = orderFld.val();
            var orderIdx = $(this).data('ord');
            if (orderVal.indexOf('[' + orderIdx +']') > -1){
                orderVal = orderVal.replace('[' + orderIdx + ']','[' + orderIdx + ' DESC]');               
                $(this).addClass('.osy-datagrid-desc').removeClass('.osy-datagrid-asc');
            } else if (orderVal.indexOf('[' + orderIdx +' DESC]') > -1) {
                orderVal = orderVal.replace('[' + orderIdx + ' DESC]','');               
                $(this).removeClass('.osy-datagrid-desc').removeClass('.osy-datagrid-asc');
            } else {
                orderVal += '[' + orderIdx + ']';
                //$('<span class="orderIcon glyphicon glyphicon-sort-by-alphabet"></span>').appendTo(this);
            }
            $('#'+gridId+'_pag').val(1);
            orderFld.val(orderVal);
            //console.log($('#'+grd.attr('id')+'_pag').val());
            ODataGrid.refreshAjax(grid);
        });
    },
    initPagination : function()
    {
        $('.osy-datagrid-2').on('click','.osy-datagrid-2-paging',function(){
            ODataGrid.refreshAjax(
                $(this).closest('div.osy-datagrid-2'),
                'btn_pag=' + $(this).val()
            );
            return;
            var pag = parseInt($('.osy-datagrid-2-pagval',$(this).closest('.osy-datagrid-2-foot')).val());
            var tot = $('.osy-datagrid-2-pagval',$(this).closest('.osy-datagrid-2-foot')).data('pagtot');
            switch($(this).data('mov')){
                case 'start': pag = 1;
                              break;
                case 'end'  : pag = tot;
                              break;
                default     : pag += parseInt($(this).data('mov'));
                              break;
            }            
            $('.osy-datagrid-2-pagval',$(this).closest('.osy-datagrid-2-foot')).val(pag);
            $('form').submit();
        });
    },
    refreshAjax : function(grid)
    {
        if ($(grid).is(':visible')) {
            Osynapsy.waitMask.show(grid);
        }
        var data  = $('form').serialize();
            data += '&ajax=' + $(grid).attr('id');
            data += (arguments.length > 1 && arguments[1]) ? '&'+arguments[1] : '';
        $.ajax({
            type : 'post',
            context : grid,
            data : data,
            success : function(rsp){
                Osynapsy.waitMask.remove();
                if (rsp) {
                    var id = '#'+$(this).attr('id');
                    var grid = $(rsp).find(id);
                    var body = $('.osy-datagrid-2-body', grid).html();
                    var foot = $('.osy-datagrid-2-foot', grid).html();
                    $('.osy-datagrid-2-body',this).html(body);
                    $('.osy-datagrid-2-foot',this).html(foot);
                    ODataGrid.refreshAjaxAfter(this);
                    if ($(this).hasClass('osy-treegrid')){
                        OTree.parentOpen();
                    }
                }
            }
        });
    },
    refreshAjaxAfter : function(obj)
    {
        if ((map = $(obj).data('mapgrid')) && window.OclMapLeafletBox){
            //OclMapLeafletBox.markersClean(map);
            OclMapLeafletBox.refreshMarkers(map, $(obj).attr('id'));
            return;
        }
        if ((map = $(obj).data('mapgrid')) && window.OclMapGridGoogle){
            omapgrid.clear_markers(map);
            omapgrid.refresh_markers(map);
        }
        
    }
}

if (window.FormController){    
    FormController.register('init','ODataGrid',function(){
        ODataGrid.init();
    });
}