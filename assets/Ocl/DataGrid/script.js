OTree = {
    branchClose : function(parentNodeId)
    {
        $('tr[treeParentNodeId="'+parentNodeId+'"]').each(function(){
            OTree.branchClose($(this).attr('treeNodeId'));
            $(this).addClass('hide');
        });
    },
    branchOpen : function(parentNodeId)
    {
        if ($('tr[treeNodeId="'+parentNodeId+'"]').is(':visible')){ //Serve a bloccare le aperture su refresh
            $('tr[treeParentNodeId="'+parentNodeId+'"]').each(function(){
                $(this).removeClass('hide');
                if ($(this).attr('__state') === 'open') {
                    OTree.branchOpen($(this).attr('treeNodeId'));
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
            var dataGridId = $(this).attr('id');
            var sel = $('#'+dataGridId+'_sel',this).val().split('][')[0];
            if (sel){
                sel = sel.replace('[','').replace(']','');
                $('tr[oid="'+sel+'"]',this).addClass('sel');
            }
            var nodeOpenObj = $('input[name='+ $(this).attr('id') + '_open]');
            var nodeOpenVal = nodeOpenObj.val().split('][');
            for (var i in nodeOpenVal) {
                var parentNodeId = nodeOpenVal[i].replace('[','').replace(']','');
                $('tr[treeNodeId="'+parentNodeId+'"]').attr('__state','open');
                $('span[class*=tree-plus]','tr[treeNodeId="'+parentNodeId+'"]').addClass('minus');
                OTree.branchOpen(parentNodeId);
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
                var dataGrid = $(this).closest('div.osy-datagrid-2');
                var nodeOpenObj = $('input[name='+ dataGrid.attr('id') + '_open]');
                var nodeOpenVal = nodeOpenObj.val();
                var targetNodeId = tr.attr('treeNodeId');
                if ($(this).hasClass('minus')){
                    nodeOpenObj.val(nodeOpenVal.replace('['+targetNodeId+']',''));
                    OTree.branchClose(targetNodeId);
                    tr.attr('__state','close');
                } else {
                    nodeOpenObj.val(nodeOpenVal+'['+targetNodeId+']');
                    OTree.branchOpen(targetNodeId);
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
    refreshAjax : function(grid, afterRefresh)
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
            success : function(response){
                Osynapsy.waitMask.remove();
                if (response) {
                    var id = '#'+$(this).attr('id');
                    var grid = $(response).find(id);
                    var body = $('.osy-datagrid-2-body', grid).html();
                    var foot = $('.osy-datagrid-2-foot', grid).html();
                    $('.osy-datagrid-2-body',this).html(body);
                    $('.osy-datagrid-2-foot',this).html(foot);
                    ODataGrid.refreshAjaxAfter(this);
                    if ($(this).hasClass('osy-treegrid')){
                        OTree.parentOpen();
                    }
                }
                if (!Osynapsy.isEmpty(afterRefresh)) {
                    afterRefresh(response);
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
        } else if((map = $(obj).data('mapgrid')) && window.OclMapTomtomBox){            
            OclMapTomtomBox.refreshMarkers(map, $(obj).attr('id'));
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