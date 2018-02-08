OclMapTomtomBox = {
    datagrid : [],
    maplist  : {},
    markerlist : {},
    layermarker : {},
    layerlist : {},
    polylinelist : {},
    datasets : {},
    autocenter : true,
    init : function()
    {
        self = this;
        $('.osy-mapgrid-tomtom').each(function(){
            var mapId = $(this).attr('id');
            var zoom = 10;            
            tomtom.setProductInfo('OclTomtomBox', '0.1');
            var map = tomtom.map(mapId, {
                key: 'EXwWaZDiKa0BcXEsHT8NiJxm2Z8GosTj',
                source: 'vector',
                basePath: '/sdk'
            });
            map.mapId = mapId;
            self.maplist[mapId] = map;            
            if (document.getElementById(mapId + '_zoom').value > 0){
                zoom = document.getElementById(mapId + '_zoom').value;			
            }
            if (!Osynapsy.isEmpty( $('#' + mapId + '_center').val())) {                
                var center = $('#' + mapId + '_center').val().split(',');                        
                center[0] = parseFloat(center[0]);
                center[1] = parseFloat(center[1]);
                map.setView(center, zoom);
            }                        
            $('div[data-mapgrid=' + $(this).attr('id') +']').each(function(){
                OclMapTomtomBox.datagrid.push($(this).attr('id'));
            });
            self.setVertex(map);
            map.on('moveend', function(e) {
                OclMapTomtomBox.autocenter = false;
                OclMapTomtomBox.setVertex(map);
                //OclMapTomtomBox.refreshDatagrid(map);
            });  
            return;            
                                                                                                                        						                       
            if ($(this).attr('coostart')){                
                var mrk = $(this).attr('coostart').split(',');				
                OclMapLeafletBox.markersAdd(
                    mapId,
                    'start-layer',
                    [
                        {
                            lat : parseFloat(mrk[0]),
                            lng : parseFloat(mrk[1]),
                            oid : mapId + '-start',
                            ico : {text : mrk[2],color:'green'},
                            popup : 'MAIN'
                        }
                    ]
                );                
            }
        });		
	this.refreshDatagrid();
    },
    calc_dist : function(sta, end)
    {
	var a = L.latLng(sta);
	var b = L.latLng(end);
	return a.distanceTo(b);
    },
    calc_next : function(sta,dat)
    {
        //console.log(dat);
	//Alert impostando una distanza troppo bassa va in errore;
  	var dst_min = parseFloat(100000000);
	var coo_min = null;
	for (i in dat) {		     
            var dst_cur = this.calc_dist(sta, dat[i]);
            dst_min = Math.min(dst_min,dst_cur);
            if (dst_min == dst_cur){ 
		coo_min = dat[i]; 
            }
	}
	return coo_min;
   },
    calc_perc : function(mapid, dat)
    {
        var polid = 'prova';
   	var prc = [];
        var arr = [];
        var nxt = dat.shift();
        arr.push([parseFloat(nxt.lat),parseFloat(nxt.lng)]);
	var i = 0;
	while ((dat.length > 0) && (i < 1000)){
            nxt = this.calc_next(nxt,dat);
            try{
            arr.push([parseFloat(nxt.lat),parseFloat(nxt.lng)]);
                    dat.splice( dat.indexOf(nxt),1);
            } catch (err){
                    //console.log(err,nxt,arr);
                    i = 100;
            }		
	}
	  //console.log(arr);
	if (mapid in this.maplist){
	    if (polid in this.polylinelist){
                this.maplist[mapid].removeLayer(this.polylinelist[polid]);
            }
            this.polylinelist[polid] = new L.polyline(arr,{color : 'red'});
            this.polylinelist[polid].addTo(this.maplist[mapid]);
            //this.layerlist[map].addLayer(pol);
	}      
   },   
   dataset_add : function(datid,dats)
   {
   	this.datasets[datid] = dats;
   },
   dataset_calc_route : function(mapid, datid, sta)
   {
        if (datid in this.datasets) {
            var data = this.datasets[datid].slice();			
            if (sta){ 
                data.unshift(sta);
            }
            this.calc_perc(mapid,data);
        }
   },
   getLayer : function(mapId, layerId, clean)
   {
        if (!(layerId in this.layerlist)){
            this.layerlist[layerId] = new L.FeatureGroup();
            this.maplist[mapId].addLayer(this.layerlist[layerId]);
        } else if (clean){
            this.cleanLayer(layerId);
        }
        return this.layerlist[layerId];
   },   
   cleanLayer : function(layerId)
   {
        if (layerId in this.layerlist){
            this.layerlist[layerId].clearLayers();
	}
   },   
   markersClean : function(mapid)
   {
   },
   markersAdd : function(mapId, layerId, markers)
   {
        if (!(markers instanceof Array)){ 
            return; 
        }        
        for (var i in markers){
            var marker = markers[i];            
            if (Osynapsy.isEmpty(marker.ico)) {                
                continue;
            }
            if (!Osynapsy.isEmpty(marker.ico.class) && marker.ico.class.indexOf('fa-') === 0){
                var ico = L.AwesomeMarkers.icon({
                    icon : marker.ico.class,
                    prefix : 'fa', 
                    markerColor : marker.ico.color, 
                    spin : false
                });  
            } else {
                var ico = L.divIcon({
                    className: Osynapsy.isEmpty(marker.ico.class) ? 'osy-mapgrid-marker-blue' : marker.ico.class, 
                    html : marker.ico.text, 
                    iconSize : null
                });
            }
            var markerObject = L.marker(
                [marker.lat, marker.lng],
                {icon: ico}
            );
            if (!Osynapsy.isEmpty(marker.popup)){
                markerObject.bindPopup(marker.popup);
            }
            this.markerAppend(mapId, layerId, markerObject);
        }
   },
   markerAppend : function(mapId, layerId, marker)
   {        
        if (!(layerId in this.layermarker)){
            this.layermarker[layerId] = {};
        }
        this.layermarker[layerId][mapId] = marker; 
        this.getLayer(mapId, layerId).addLayer(marker);
   },
   polyline : function(mapId, layerId, dataset, polylineColor)
   {
        if (polylineColor === undefined || polylineColor === null) {
            polylineColor = 'red';
        }        
        if (mapId in this.maplist) {
            var layer = this.getLayer(mapId, layerId, false);
            var polyline = new L.polyline(dataset, {color : polylineColor});
            polyline.addTo(layer);	  	
        } 
   },   
   refreshDatagrid : function()
   {
        if (this.datagrid.length === 0) {
            return;
        }
        for(var i in this.datagrid) {                        
            ODataGrid.refreshAjax($('#'+this.datagrid[i]),null);
        }
   },
   refreshMarkers : function(mapId, dataGridId)
   {        
        if (this.datagrid.length === 0){ 
            return; 
	}
	var dataGrid = $('#'+dataGridId);
        var infoFormat = dataGrid.data('mapgrid-infowindow-format');
	var dataset = [];
        //Se esiste pulisco il layer corrente
        this.cleanLayer(dataGridId);
        $('tr', dataGrid).each(function(){
            if (Osynapsy.isEmpty($(this).data('marker'))) {
                return true;
            }
            var infoWindow = infoFormat, i = 1;
            $(this).children().each(function(){
               if (Osynapsy.isEmpty(infoFormat)){
                   infoWindow += $(this).text() + '<br>';
                } else if (infoWindow.indexOf('['+i+']') > -1) { 
                   infoWindow = infoWindow.replace('['+i+']',$(this).html());
                }
                i++;
            });
            infoWindow = '<div style="width: 250px; height: 120px; overflow: hidden;">'+ infoWindow +'</div>';
            var rawMarker = $(this).data('marker').split(',');           
            var marker = {
                lat : Osynapsy.isEmpty(rawMarker[0]) ? null : parseFloat(rawMarker[0]),
                lng : Osynapsy.isEmpty(rawMarker[1]) ? null : parseFloat(rawMarker[1]),
                ico : {
                    class : Osynapsy.isEmpty(rawMarker[2]) ? 'fa-circle' : rawMarker[2],
                    text  : Osynapsy.isEmpty(rawMarker[3]) ? '' : rawMarker[3],
                    color : Osynapsy.isEmpty(rawMarker[4]) ? 'blue' : rawMarker[4]
                },                
                popup : infoWindow
            };            
            if (!Osynapsy.isEmpty(marker.lat) && !Osynapsy.isEmpty(marker.lng)){               
                dataset.push(marker);
            }            
        });
        if (this.autocenter) {
           this.computeCenter(mapId, dataset);
        }
        this.markersAdd(mapId, dataGridId, dataset);
        this.dataset_add(dataGridId, dataset);
        this.autocenter = true;
    },
    computeCenter : function(mapId, dataset)
    {
        if (dataset.length === 0) {
            return;
        }
        var center = {'lat' : 0, 'lng' : 0};
        for (var i in dataset) {
            var rec = dataset[i];
            center.lat += rec['lat'];
            center.lng += rec['lng'];
        }
        center.lat = center.lat / (parseInt(i) + 1);
        center.lng = center.lng / (parseInt(i) + 1);        
        this.setCenter(mapId, center);
    },
    setVertex : function(map){
	var mapId = map.mapId;
	var bounds = map.getBounds();
        
	var ne = bounds.getNorthEast();
	var sw = bounds.getSouthWest();
        var center = map.getCenter();
        //console.log(ne,sw,center.toString(), map.getContainer().getAttribute('id'));
        
	$('#'+mapId+'_ne_lat').val(ne.lat);
	$('#'+mapId+'_ne_lng').val(ne.lng);
	$('#'+mapId+'_sw_lat').val(sw.lat);
	$('#'+mapId+'_sw_lng').val(sw.lng); 
        //return;
	//$('#'+mapId+'_center').val(map.getCenter().toString().replace('LatLng(','').replace(')','')); 
	$('#'+mapId+'_cnt_lat').val((sw.lat + ne.lat) / 2); 
	$('#'+mapId+'_cnt_lng').val((sw.lng + ne.lng) / 2); 
    },	  
    open_id : function(oid,lid){
   	console.log(oid,lid)   		
   	if (lid){
            if ((lid in this.layermarker) && (oid in this.layermarker[lid])){
		this.layermarker[lid][oid].openPopup();
            }
	} else {
            this.markerlist[oid].openPopup();          
	}
    },
    resize : function(mapid)
    {
   	if (mapid in this.maplist){
            this.maplist[mapid].invalidateSize();
	}
    },
    setCenter: function(mid,cnt,zom)
    {
   	self.maplist[mid].setView(cnt,zom);
    }
}

if (window.FormController){    
    FormController.register('init','OclMapTomtomBox',function(){
        OclMapTomtomBox.init();
    });
}


