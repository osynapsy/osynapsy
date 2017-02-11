ODataGrid = 
{
    init : function()
    {
        this.initOrderBy();
        this.initPagination();
        this.initAdd();
        $('.osy-datagrid-2').each(function(){
            this.refresh = function() {ODataGrid.refreshAjax(this);}
        });
    },
    initAdd : function()
    {
        $('.osy-datagrid-2 .cmd-add').click(function(){
            FormController.saveHistory();
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
            FormController.waitMask('open','wait',grid);
        }
        var data  = $('form').serialize();
            data += '&ajax=' + $(grid).attr('id');
            data += (arguments.length > 1 && arguments[1]) ? '&'+arguments[1] : '';
        $.ajax({
            type : 'post',
            context : grid,
            data : data,
            success : function(rsp){
                FormController.waitMask('remove');
                if (rsp) {
                    var id = '#'+$(this).attr('id');
                    var grid = $(rsp).find(id);
                    var body = $('.osy-datagrid-2-body', grid).html();
                    var foot = $('.osy-datagrid-2-foot', grid).html();
                    $('.osy-datagrid-2-body',this).html(body);
                    $('.osy-datagrid-2-foot',this).html(foot);
                    ODataGrid.refreshAjaxAfter(this);
                    if ($(this).hasClass('osy-treegrid')){
                        ODataGrid.parentOpen();
                    }
                }
            }
        });
    },
    refreshAjaxAfter : function(obj)
    {
        if ((map = $(obj).data('mapgrid')) && window.OclMapLeafletBox){
            OclMapLeafletBox.markersClean(map);
            OclMapLeafletBox.refreshMarkers(map,$(obj).attr('id'));
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