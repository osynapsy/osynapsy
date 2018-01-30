OclMapLeafletBox = {
    datagrid : [],
    maplist  : {},
    markerlist : {},
    layermarker : {},
    layerlist : {},
    polylinelist : {},
    datasets : {},
    init : function()
    {
        self = this;
        $('.osy-mapgrid-leaflet').each(function(){
            var mapId = $(this).attr('id');
            cnt = $('#' + mapId + '_center').val().split(',');	
            zom = 10;
            if (document.getElementById(mapId + '_zoom').value>0){
                    zom = document.getElementById(mapId + '_zoom').value;			
            }
            cnt[0] = parseFloat(cnt[0]);
            cnt[1] = parseFloat(cnt[1]);
            var map = L.map(mapId).setView(cnt, zom);
            map.mapid = mapId;
            self.maplist[mapId] = map;
            L.tileLayer(
                'http://{s}.tile.osm.org/{z}/{x}/{y}.png', 
                { attribution: '&copy; <a href="http://osm.org/copyright">OpenStreetMap</a> contributors' }
            ).addTo(map);
            self.set_vertex(map);
            $('div[data-mapgrid=' + $(this).attr('id') +']').each(function(){
                OclMapLeafletBox.datagrid.push($(this).attr('id'));
            });
            map.on('moveend', function(e) {
                OclMapLeafletBox.set_vertex(map);
                OclMapLeafletBox.refresh_datagrid(map);
            });                
            var LeafIcon = L.Icon.extend({
                options: {
                    shadowUrl: 'http://leafletjs.com/docs/images/leaf-shadow.png',
                    iconSize:     [38, 95],
                    shadowSize:   [50, 64],
                    iconAnchor:   [22, 94],
                    shadowAnchor: [4, 62],
                    popupAnchor:  [-3, -76]
                }			
            });

            var greenIcon = new LeafIcon({
                iconUrl: 'http://leafletjs.com/docs/images/leaf-green.png'
            });

            var drawnItems = new L.FeatureGroup();
            map.addLayer(drawnItems);						

            var drawControl = new L.Control.Draw({
                position: 'topright',
                draw: {
                    polygon: {
                        shapeOptions: {
                                color: 'purple'
                        },
                        allowIntersection: false,
                        drawError: {
                                color: 'orange',
                                timeout: 1000
                        },
                        showArea: true,
                        metric: false,
                        repeatMode: true
                    },
                    polyline: {
                        shapeOptions: {
                                color: 'red'
                        }
                    },
                    rect: {
                        shapeOptions: {
                                color: 'green'
                        }
                    },
                    circle: {
                        shapeOptions: {
                                color: 'steelblue'
                        }
                    },
                    marker: {
                        icon: greenIcon
                    }
                },
                edit: {
                    featureGroup: drawnItems
                }
            });
            map.addControl(drawControl);

            map.on('draw:created', function (e) {
                var type = e.layerType,
                    layer = e.layer;
                if (type === 'marker') {
                        layer.bindPopup('A popup!');
                }
                drawnItems.addLayer(layer);
            }).on('draw:drawstop', function (e) {
                alert('finito');
            }).on('zoomend',function(e){
                $('#'+this.mapid+'_zoom').val(this.getZoom());
            });
            if ($(this).attr('coostart')){			
                var mrk = $(this).attr('coostart').split(',');				
                OclMapLeafletBox.markers_add(
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
	this.refresh_datagrid();
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
            if (marker.ico !== undefined && marker.ico) {
                if (marker.ico.text.indexOf('fa-') === 0){
                    ico = L.AwesomeMarkers.icon({icon: marker.ico.text, prefix: 'fa', markerColor: marker.ico.color, spin:false});  
                } else {
                    ico = L.divIcon({className: layerId+'-icon', html : marker.ico.text, iconSize:null});
                }
                var markerObject = L.marker(
                    [marker.lat, marker.lng],
                    {icon: ico}
                );
                if (marker.popup !== undefined){
                    markerObject.bindPopup(marker.popup);
                }
                this.markerAppend(layerId, mapId, markerObject);
            }
        }
   },
   markerAppend : function(layerId, mapId, marker)
   {
        if (!(layerId in this.layermarker)){
            this.layermarker[layerId] = {};
        }		 
        this.layermarker[layerId][mapId] = marker;
        this.layerlist[layerId].addLayer(marker);
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
   refresh_datagrid : function(map, div)
   {
        if (this.datagrid.length === 0) {
            return;
        }
        for(var i in this.datagrid ) {
            var gridId = this.datagrid[i]; //Datagrid id
            var mapId = $(div).attr('id'); //Map id
            ODataGrid.refreshAjax($('#'+gridId),null/*,function(){OclMapLeafletBox.refresh_markers(mid)}*/);
        }
   },
   refreshMarkers : function(mapId, dataGridId)
   {        
        if (this.datagrid.length === 0){ 
            return; 
	}
	var dataGrid = $('#'+dataGridId);
	if (!(f = dataGrid.data('mapgrid-infowindow-format'))) {
            f = null;
        }
	var dataset = [];
        //Creo un nuovo layer
        this.getLayer(mapId, dataGridId, true);
        $('tr',dataGrid).each(function(){
            var frm = f;               
            var i = 1;
            $(this).children().each(function(){
               if (f){
                   if (frm.indexOf('['+i+']') > -1) { 
                       frm = frm.replace('['+i+']',$(this).html());
                    }
                } else {
                    frm += $(this).text() + '<br>';
                }
                i++;
            });   		
            if ($(this).attr('lat')){
                dataset.push({
                    lat : parseFloat($(this).attr('lat')),
                    lng : parseFloat($(this).attr('lng')), 
                    oid : $(this).attr('oid'), 
                    ico : {text : 'fa-circle-o', color: 'blue'},
                    popup : '<div style="width: 250px; height: 120px; overflow: hidden;">'+ frm +'</div>'
                });
            }			   
        });
        this.computeCenter(mapId, dataset);
        this.markersAdd(mapId, dataGridId, dataset);
        this.dataset_add(dataGridId, dataset);
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
    set_vertex : function(map){
	var mapId = map.getContainer().getAttribute('id');
	var bounds = map.getBounds();		
	var ne = bounds.getNorthEast();
	var sw = bounds.getSouthWest();
	$('#'+mapId+'_ne_lat').val(ne.lat);
	$('#'+mapId+'_ne_lng').val(ne.lng);
	$('#'+mapId+'_sw_lat').val(sw.lat);
	$('#'+mapId+'_sw_lng').val(sw.lng); 
	$('#'+mapId+'_center').val(map.getCenter().toString().replace('LatLng(','').replace(')','')); 
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
    FormController.register('init','OclMapLeafletBox',function(){
        OclMapLeafletBox.init();
    });
}