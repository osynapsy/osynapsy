OclMapTomtomBox = {
    datagrid : [],
    maplist  : {},
    markerlist : {},
    layermarker : {},
    layerlist : {},
    polylinelist : {},
    datasets : {},
    autocenter : true,
    startMarker : [], 
    init : function()
    {
        var self = this;
        $('.osy-mapgrid-tomtom').each(function(){
            var mapId = $(this).attr('id');
            var zoom = 10;            
            tomtom.setProductInfo('OclTomtomBox', '0.1');
            var map = tomtom.map(mapId, {
                key: 'dXD9YwZw0BUAgA3VKh8YsOQKDEHcMbEO',
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
                self.datagrid.push($(this).attr('id'));
            });
            self.setVertex(map);
            map.on('moveend', function(e) {
                self.autocenter = false;
                self.setVertex(map);
                //OclMapTomtomBox.refreshDatagrid(map);
            });                                                                                                                                       						                       
            if (Osynapsy.isEmpty($(this).data('marker'))){
                return true;
            }
            self.startMarker = $(this).data('marker').split(',');				
            self.markersAdd(mapId, 'start-layer',[{
                lat : parseFloat(self.startMarker[0]),
                lng : parseFloat(self.startMarker[1]),
                oid : mapId + '-start',
                ico : {
                    class : self.startMarker[2],
                    color : 'red'
                },
                popup : 'MAIN'
            }]); 
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
                i = 100;
            }		
	}	  
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
    displayInstructions : function (routeJson, resultsContainer)
    {
        var guidance = routeJson.features[0].properties.guidance;
        guidance.instructionGroups.forEach(function(instructionGroup) {
            // Print name of the group
            var groupEl = tomtom.L.DomUtil.create('p', 'tt-results__groupname');
            groupEl.innerHTML = instructionGroup.groupMessage;
            resultsContainer.appendChild(groupEl);
            // Print steps of the group
            var stepsEl = tomtom.L.DomUtil.create('p', 'tt-results__steps');
            stepsEl.innerHTML = this.formatGroupSteps(guidance.instructions, instructionGroup);
            resultsContainer.appendChild(stepsEl);
        });
        return routeJson;
    },
    formatGroupSteps : function(instructions, instructionGroup)
    {
        var firstStep = instructionGroup.firstInstructionIndex,
            lastStep = instructionGroup.lastInstructionIndex + 1,
            steps = instructions.slice(firstStep, lastStep).map(function(step) {
                return step.message;
            });
        return '<ol start=\'' + (firstStep + 1) + '\'><li>' + steps.join('</li><li>') + '</li></ol>';
    },
    getMarkerId : function(lat, lng, txt)
    {
        var string = lat + ',' + lng + ','+ txt;        
        return Osynapsy.hashCode(string);
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
                    className: 'osy-mapgrid-marker ' + (Osynapsy.isEmpty(marker.ico.class) ? 'osy-mapgrid-marker-blue' : marker.ico.class), 
                    html : marker.ico.text, 
                    iconSize : null,
                    popupAnchor : [0, -35]
                });
            }
            var markerObject = L.marker(
                [marker.lat, marker.lng],
                {icon: ico}
            );
            if (!Osynapsy.isEmpty(marker.popup)){
                markerObject.bindPopup(marker.popup);
            }
            this.markerAppend(mapId, layerId, marker['id'], markerObject);
        }        
    },
    markerAppend : function(mapId, layerId, markerId, markerObject)
    {        
        if (!(layerId in this.layermarker)){
            this.layermarker[layerId] = {};
        }        
        this.layermarker[layerId][markerId] = markerObject; 
        this.getLayer(mapId, layerId).addLayer(markerObject);
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
    refreshMarkersFromDataset : function(mapId, layerId, dataset, autoCenter = false, colorRoute = false)
    {
        this.cleanLayer(layerId);
        if (Osynapsy.isEmpty(dataset)) {
           return;
        }       
        var markers = [];
        for (var i in dataset) {            
            var rawMarker = Array.isArray(dataset[i]) ? dataset[i] : dataset[i].split(',');
            var infoWindow = rawMarker.length === 7 ? rawMarker[6] : '';  
            infoWindow = '<div class="infoWindow" style="width: 250px;">'+ infoWindow +'</div>';            
            var marker = {
                id  : rawMarker[0],
                lat : Osynapsy.isEmpty(rawMarker[1]) ? null : parseFloat(rawMarker[1]),
                lng : Osynapsy.isEmpty(rawMarker[2]) ? null : parseFloat(rawMarker[2]),
                ico : {
                    class : Osynapsy.isEmpty(rawMarker[3]) ? 'fa-circle' : rawMarker[3],
                    text  : Osynapsy.isEmpty(rawMarker[4]) ? '' : rawMarker[4],
                    color : Osynapsy.isEmpty(rawMarker[5]) ? 'blue' : rawMarker[5]
                },                
                popup : infoWindow                
            };                       
            if (!Osynapsy.isEmpty(marker.lat) && !Osynapsy.isEmpty(marker.lng)){               
                markers.push(marker);
            }            
        }        
        this.markersAdd(mapId, layerId, markers);                        
        if (colorRoute) {
            this.routing(mapId, markers, layerId + '_route', colorRoute);
        } else if (autoCenter) {
            this.computeCenter(mapId, markers);
        }
    },
    refreshMarkers : function(mapId, dataGridId)
    {        
        if (this.datagrid.length === 0){ 
            return; 
	}        
	var dataGrid = $('#'+dataGridId);
        var infoFormat = dataGrid.data('mapgrid-infowindow');
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
            infoWindow = '<div class="infoWindow" style="width: 250px;">'+ infoWindow +'</div>';
            var rawMarker = $(this).data('marker').split(',');           
            var marker = {
                id  : rawMarker[0],
                lat : Osynapsy.isEmpty(rawMarker[0]) ? null : parseFloat(rawMarker[1]),
                lng : Osynapsy.isEmpty(rawMarker[1]) ? null : parseFloat(rawMarker[2]),
                ico : {
                    class : Osynapsy.isEmpty(rawMarker[3]) ? 'fa-circle' : rawMarker[3],
                    text  : Osynapsy.isEmpty(rawMarker[4]) ? '' : rawMarker[4],
                    color : Osynapsy.isEmpty(rawMarker[5]) ? 'blue' : rawMarker[5]
                },                
                popup : infoWindow
            };            
            if (!Osynapsy.isEmpty(marker.lat) && !Osynapsy.isEmpty(marker.lng)){               
                dataset.push(marker);
            }            
        });        
        this.markersAdd(mapId, dataGridId, dataset);
        if (this.autocenter) {
           this.computeCenter(mapId, dataset);
        }
        if (!Osynapsy.isEmpty($(dataGrid).data('mapgrid-routing'))) {
            this.routing(mapId, dataset);
        }
        this.dataset_add(dataGridId, dataset);
        this.autocenter = true;
    },
    routing : function(mapId, dataset, layerId, colorLayer)
    {
        //Init routing layer
        if (Osynapsy.isEmpty(layerId)) { 
            layerId = 'routing';
        }
        if (Osynapsy.isEmpty(colorLayer)) { 
            colorLayer = '#00d7ff';
        }        
        var map = this.maplist[mapId];
        var coordinates = [];
        if (!(layerId in this.layerlist)) {                   
            this.layerlist[layerId] = tomtom.L.geoJson(null,{
                style: {
                    color: colorLayer,
                    opacity: 0.8, 
                    zIndexOffset : 100
                }
            }).addTo(map);            
        } else {            
            this.layerlist[layerId].clearLayers();
        }        
        if (!Osynapsy.isEmpty(this.startMarker)) {         
            coordinates.push(this.startMarker[0] + ',' + this.startMarker[1]);
        }
        if (Osynapsy.isEmpty(dataset) || (dataset.length + coordinates.length) < 2) {
            console.log('No route tracing. Dataset contains ' + dataset.length + ' item');
            return;
        }
        for (var i in dataset) {
            coordinates.push(dataset[i].lat + ',' + dataset[i].lng);
        }        
        var self = this;
        tomtom.routing()
              .locations(coordinates.join(':'))
              .go()
              .then(function(routeJson) {                    
                    var layer = self.layerlist[layerId];
                    layer.addData(routeJson);                    
                    //map.fitBounds(layer.getBounds(), {padding: [5, 5]});
               });
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
    openId : function(markerId, layerId)
    {   	 		
   	if (Osynapsy.isEmpty(layerId)){
            this.markerlist[markerId].openPopup();
            return;
	}        
        if ((layerId in this.layermarker) && (markerId in this.layermarker[layerId])){            
            this.layermarker[layerId][markerId].openPopup();
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
   	this.maplist[mid].setView(cnt,zom);
    }
}

if (window.FormController){    
    FormController.register('init','OclMapTomtomBox',function(){
        OclMapTomtomBox.init();
    });
}


