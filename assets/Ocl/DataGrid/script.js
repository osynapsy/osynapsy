var OTree = 
{
    init : function()
    {
        //OTree.parentOpen();
        $('div.osy-treegrid').each(function(){
            $('tr.branch-open', this).each(function(){
                OTree.initBranch($(this));
            });
        });
        $('.osy-datagrid-2').on('click', 'span.tree', function (event){
            event.stopPropagation();
            OTree.toggleBranch($(this));
        });
    },
    initBranch(row)
    {
        $(row).removeClass('hide');
        $('span[class*=tree-plus-]',row).addClass('minus');
        $('.parent-' + $(row).attr('treeNodeId')).removeClass('hide');
        var parentId = $(row).attr('treeParentNodeId');
        if (parentId) {
            OTree.toggleBranch($('tr[treeNodeId="'+parentId+'"]'),true);
        }
    },
    toggleBranch : function(span)
    {
        var row = $(span).toggleClass('minus').closest('tr');
        $('.parent-' + $(row).attr('treeNodeId')).toggleClass('hide');
        var grid = $(row).closest('.osy-treegrid');
        var nodeId = $(row).attr('treeNodeId');
        $('input.selected-folder').val(nodeId);
        var inputOpenFolders = $('input.open-folders', grid);
        var strOpenFolders = inputOpenFolders.val();
        if (!$(span).hasClass('minus')){
           inputOpenFolders.val(strOpenFolders.replace('['+nodeId+']',''));
        } else {
           inputOpenFolders.val(strOpenFolders + '['+nodeId+']');
        }                    
    }
};

var ODataGrid = 
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
            url :  window.location.href,
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