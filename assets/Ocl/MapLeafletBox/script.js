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
            mid = $(this).attr('id');
            cnt = $('#' + mid + '_center').val().split(',');	
            zom = 10;
            if (document.getElementById(mid + '_zoom').value>0){
                    zom = document.getElementById(mid + '_zoom').value;			
            }
            cnt[0] = parseFloat(cnt[0]);
            cnt[1] = parseFloat(cnt[1]);
            var map = L.map(mid).setView(cnt, zom);
            map.mapid = mid;
            self.maplist[mid] = map;
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
                /*map.addLayer(new L.FreeDraw({
                mode: L.FreeDraw.MODES.CREATE | L.FreeDraw.MODES.EDIT
                }));*/

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
            });
            map.on('draw:drawstop', function (e) {
                alert('finito');
            });
            map.on('zoomend',function(e){
                $('#'+this.mapid+'_zoom').val(this.getZoom());
            });
            if ($(this).attr('coostart')){			
                mrk = $(this).attr('coostart').split(',');				
                OclMapLeafletBox.markers_add(
                    mid,
                    'start-layer',
                    [
                        {
                            lat : parseFloat(mrk[0]),
                            lng : parseFloat(mrk[1]),
                            oid : mid+'-start',
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
   layer_get : function(mid, lid, clean)
   {
        if (!(lid in this.layerlist)){
            this.layerlist[lid] = new L.FeatureGroup();
            this.maplist[mid].addLayer(this.layerlist[lid]);
        } else if (clean){
            this.layer_clean(mid,lid);
        }
        return this.layerlist[lid];
   },   
   layer_clean : function(mid, lid)
   {
        if (lid in this.layerlist){
            this.layerlist[lid].clearLayers();
	}
   },
   marker_add : function(lid, mid, mrk)
   {
        if (!(lid in this.layermarker)){
            this.layermarker[lid] = {};
        }		 
        this.layermarker[lid][mid] = mrk;
        this.layerlist[lid].addLayer(mrk);
   },
   markersClean : function(mapid)
   {
   },
   markers_add : function(mid, lid, dat)
   {
        if (!(dat instanceof Array)){ 
            return; 
        } 
        layer = this.layer_get(mid, lid, false);
        for (i in dat){		
            if (dat[i].ico !== undefined && dat[i].ico) {							
                if (dat[i].ico.text.indexOf('fa-') == 0){
                    ico = L.AwesomeMarkers.icon({icon: dat[i].ico.text, prefix: 'fa', markerColor: dat[i].ico.color, spin:false});  
                } else {
                    ico = L.divIcon({className: lid+'-icon', html : dat[i].ico.text, iconSize:null});
                }
                marker = L.marker(
                    [dat[i].lat, dat[i].lng],
                    {icon: ico}
                );
                if (dat[i].popup !== undefined){
                    marker.bindPopup(dat[i].popup);
                }
                this.marker_add(lid, dat[i].oid, marker);
            }
        }
   },
   polyline : function(mapid,layerid,dat,pcolor)
   {
        if (pcolor === undefined || pcolor == null) {
            pcolor = 'red';
        }
        console.log(pcolor);
        if (mapid in this.maplist) {
            var layer = this.layer_get(mapid,layerid,false);
            var pol = new L.polyline(dat,{color : pcolor});
            pol.addTo(layer);	  	
        } 
   },   
   refresh_datagrid : function(map, div)
   {
        if (this.datagrid.length > 0) {
            for( i in this.datagrid ) {
                var gid = this.datagrid[i]; //Datagrid id
                var mid = $(div).attr('id'); //Map id
                ODataGrid.refreshAjax($('#'+gid),null/*,function(){OclMapLeafletBox.refresh_markers(mid)}*/);
            }
        }
   },
   refreshMarkers : function(mid, gid)
   {        
        if (this.datagrid.length == 0){ 
            return; 
	}
	var gob = $('#'+gid);
	if (!(f = gob.data('mapgrid-infowindow-format'))) {
            f = null;
       	}
	layer = this.layer_get(mid, gid, true);		
	var dataset = [];			
        $('tr',gob).each(
            function(){
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
            }
        );
        this.markers_add(mid, gid, dataset);
        this.dataset_add(gid, dataset);
    },
    set_vertex : function(map){
	var mid = map.getContainer().getAttribute('id');
	var bounds = map.getBounds();		
	var ne = bounds.getNorthEast();
	var sw = bounds.getSouthWest();
	$('#'+mid+'_ne_lat').val(ne.lat);
	$('#'+mid+'_ne_lng').val(ne.lng);
	$('#'+mid+'_sw_lat').val(sw.lat);
	$('#'+mid+'_sw_lng').val(sw.lng); 
	$('#'+mid+'_center').val(map.getCenter().toString().replace('LatLng(','').replace(')','')); 
	$('#'+mid+'_cnt_lat').val((sw.lat + ne.lat) / 2); 
	$('#'+mid+'_cnt_lng').val((sw.lng + ne.lng) / 2); 
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
    set_center: function(mid,cnt,zom)
    {
   	self.maplist[mid].setView(cnt,zom);
    }
}

if (window.FormController){    
    FormController.register('init','OclMapLeafletBox',function(){
        OclMapLeafletBox.init();
    });
}