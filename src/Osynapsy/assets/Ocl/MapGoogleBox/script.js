function omapbutton (controlDiv,map){
	
	  // Set CSS for the control border
	  var controlUI = document.createElement('div');
	  controlUI.style.backgroundColor = '#fff';
	  controlUI.style.border = '2px solid #fff';	  
	  controlUI.style.boxShadow = '0 2px 6px rgba(0,0,0,.3)';
	  controlUI.style.cursor = 'pointer';
	  controlUI.style.marginTop = '6px';
	  controlUI.style.marginLeft = '-6px';
	  controlUI.style.textAlign = 'center';
	  controlUI.title = 'Delete active polygon on the map';
	  controlDiv.appendChild(controlUI);

	  // Set CSS for the control interior
	  var controlText = document.createElement('div');
	  controlText.style.color = 'rgb(25,25,25)';
	  controlText.style.fontFamily = 'Roboto,Arial,sans-serif';
	  controlText.style.fontSize = '12px';
	  controlText.style.lineHeight = '18px';
	  controlText.style.padding = '1px 6px';
	  controlText.style.color = '#888';	  
	  controlText.innerHTML = '<span class="fa fa-trash"></span>';
	  controlUI.appendChild(controlText);

	  // Setup the click event listeners: simply set the map to
	  // Chicago
	  google.maps.event.addDomListener(controlUI, 'click', function() {
		OclGmapBox.selection_delete();
	  });    
}

OclGmapBox = {
   cont : 0,
   selected_shape : null,
   datagrid : [],
   refresh_datagrid_blocked : false,
   calc_dist : function(x,y)
   {
        var RAGGIO_QUADRATICO_MEDIO = 6372.795477598; 
        var rad_x = {lat : 0, lng : 0};
        var rad_y = {lat : 0, lng : 0};
        var phi = 0;
        var P = 0;
        rad_x.lat = Math.PI * x.lat / 180;
        rad_x.lng = Math.PI * x.lng / 180;
        rad_y.lat = Math.PI * y.lat / 180;
        rad_y.lng = Math.PI * y.lng / 180;
        phi = Math.abs(rad_x.lng - rad_y.lng);
        P = Math.acos (  (Math.sin(rad_x.lat) * Math.sin(rad_y.lat)) +  (Math.cos(rad_x.lat) * Math.cos(rad_y.lat) * Math.cos(phi)));  
        return Math.round((P * RAGGIO_QUADRATICO_MEDIO *100)) / 100; 
   },
   calc_dist2 : function(x,y)
   {
        rad = function(x1) {
            return x1*Math.PI/180;
        }

        var R     = 6371.0090667;                          //Radio de la tierra en km
        var dLat  = rad( y.lat - x.lat );
        var dLong = rad( y.lng - y.lng );

        var a = Math.sin(dLat/2) * Math.sin(dLat/2) + Math.cos(rad(x.lat)) * Math.cos(rad(y.lat)) * Math.sin(dLong/2) * Math.sin(dLong/2);
        var c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
        var d = R * c;

        return d.toFixed(3);                      //Retorna tres decimales
   },
   calc_next : function(sta,dat)
   {
        //console.log(dat);
  	dst_min = 100000;
	coo_min = null;
        console.log('..........' + sta.lat + ' - ' + sta.lng + '...........');
	for (i in dat) {	  
            dst_cur = this.calc_dist(sta, dat[i]);
            dst_min = Math.min(dst_min, dst_cur);
            console.log(dat[i],dst_cur);
            if (dst_min == dst_cur){ 
                coo_min = dat[i];
            }
	}
        //console.log('..........' + coo_min.lat + ' - ' + coo_min.lng + '...........');
	return coo_min;
   },
   calc_perc : function(dat)
   {
   	prc = [];
        arr = [];
        nxt = dat.shift();
        arr.push([parseFloat(nxt.lat),parseFloat(nxt.lng)]);
	i = 0;
	while ((dat.length > 0) && (i < 100)) {
            nxt = this.calc_next(nxt,dat);
            arr.push([parseFloat(nxt.lat),parseFloat(nxt.lng)]);
            dat.splice( dat.indexOf(nxt),1);
	}
	  console.log(arr);
        $('#map_grd').gmap3({
            clear : { id : 'prova'}, 
            polyline : {
                options : {
                    strokeColor : '#FF0000' ,
                    path : arr
                },
                id : 'prova'
            }
        }); 
    },
    init : function()
    {   		
	$('.osy-mapgrid').each(function(){
            //if (!$(this).is(':visible')) return;
            var mid = $(this).attr('id');
            var cnt = $('#'+mid+'_center').val().split(','); 
            var zom = ($('#'+mid+'_zoom').val() ? parseInt($('#'+mid+'_zoom').val()) : 5);  			
            ref = this;
            map_par = {
		map : {
                    options: { 
                        center : cnt, 
                        zoom : zom
                    },
                    events : {
			bounds_changed : function(map) {
                            mid = $(ref).attr('id');
                            if ($('#' + mid + '_refresh_bounds_blocked').val() == '1') {
				return;
                            }
                            OclGmapBox.set_bounds(map,ref);  
                            if (!OclGmapBox.refresh_datagrid_blocked){
   	   			OclGmapBox.refresh_datagrid(map,ref);  
                            }
			},
                        dragstart : function(map){
                                OclGmapBox.refresh_datagrid_blocked = true;
                        },
                        dragend : function(map){
                                OclGmapBox.refresh_datagrid(map,ref);  
                                OclGmapBox.refresh_datagrid_blocked = false;						   
                        }
                    }							 
		},
		marker : {
                    values : [{latLng : cnt, data:'center', options:{icon: "http://maps.google.com/mapfiles/marker_green.png"}}],
                    events : {
			click : function(marker, event, context) {		
                            OclGmapBox.open_infowindow(this,marker,event,context);
                            return;
			}
                    }
		},
		trigger : 'resize'
	};	 		 			
        $(this).gmap3(map_par);
        OclGmapBox.init_tool(mid);
        OclGmapBox.init_polygon(mid);			
        $('div[data-mapgrid=' + $(this).attr('id') +']').each(function(){
                OclGmapBox.datagrid.push($(this).attr('id'));
        });
    });
   },
   init_polygon : function(mapid){
        if (!$('#'+mapid+'_polygon').val()) return;
        pol_par = {					 
                'map' : $('#'+mapid).gmap3('get'),
                /*strokeColor: "#FF0000",*/
                strokeOpacity: 0.8,
                strokeWeight: 2,
                fillColor: "#FFFF00",
                fillOpacity: 0.4,
                clickable : true,
                editable: true,
                paths: []					
        };
        vtx_raw = $('#'+$('#'+mapid).attr('id')+'_polygon').val().split(',');		
        for (i in vtx_raw) {	
                vtx = vtx_raw[i].trim().split(' ');
                pol_par.paths[i] = new google.maps.LatLng(vtx[0],vtx[1]); 
        }				
        this.selected_shape = new google.maps.Polygon(pol_par);
        this.selection_init(this.selected_shape);
   },
   init_tool : function(mapid){
	map = $('#'+mapid).gmap3('get');
        var drawingManager = new google.maps.drawing.DrawingManager({
            //drawingMode: google.maps.drawing.OverlayType.RECTANGLE,
            drawingControl: true,
            drawingControlOptions: {
                    position: google.maps.ControlPosition.TOP_CENTER,
                    drawingModes: [										
                            /*google.maps.drawing.OverlayType.CIRCLE,*/
                            google.maps.drawing.OverlayType.POLYGON,											
                            google.maps.drawing.OverlayType.RECTANGLE
                    ]
            },									
            circleOptions: {
                fillColor: '#ffff00',
                fillOpacity: 1, 
                strokeWeight: 2, 
                clickable: false, 
                zIndex: 1, 
                editable: true
            },
            polygonOptions: {
                fillColor: '#ffff00',
                fillOpacity: 0.4, 
                strokeWeight: 2, 
                clickable: true, 
                zIndex: 1, 
                editable: true
            },
            rectangleOptions: {
                fillColor: '#ffff00',
                fillOpacity: 0.4, 
                strokeWeight: 2, 
                clickable: true, 
                zIndex: 1, 
                editable: true
            }
        });
	drawingManager.setMap(map);
		
        google.maps.event.addListener(drawingManager, 'overlaycomplete', function(event) {
            if (event.type == google.maps.drawing.OverlayType.RECTANGLE) {				
                //var bounds = event.overlay.getBounds();
                //OclGmapBox.set_bounds2(mapid,bounds);		
                var vert = OclGmapBox.get_verticies(event.overlay);
                $('#'+mapid+'_polygon').val(vert);
                OclGmapBox.refresh_datagrid(mapid);				
            }	
            if (event.type == google.maps.drawing.OverlayType.POLYGON) {				
                var vert = OclGmapBox.get_verticies(event.overlay);
                $('#'+mapid+'_polygon').val(vert);
                OclGmapBox.refresh_datagrid(mapid);								
            }				
            OclGmapBox.selection_init(event.overlay);
            event.overlay.setEditable(false);
            drawingManager.setDrawingMode(null);
        });				
        google.maps.event.addListener(map, 'click', OclGmapBox.selection_clear);
        $(document).keyup(function(e){			
                if (e.keyCode == '46'){
                        OclGmapBox.selection_delete();
                }
        });

        //Creo pulsante elimina poligono
        var centerControlDiv = document.createElement('div');
        var centerControl = new omapbutton(centerControlDiv, map);
        centerControlDiv.index = 1;
        map.controls[google.maps.ControlPosition.TOP_CENTER].push(centerControlDiv);				
   },   
   get_verticies : function(polygon)
   {
        var vertices = {
            vertex : [],
            getLength : function(){
                    return this.vertex.length;
            },
            getAt : function(i){
                    return this.vertex[i];
            },
            setAt : function(lat,lng){
                    this.vertex[this.vertex.length] = new google.maps.LatLng(lat, lng);
            }
        };
        if (polygon.getPath){
                var vertices = polygon.getPath();
        } else if (polygon.getBounds) {
              var b = polygon.getBounds(); 
              vertices.setAt(b.getNorthEast().lat(),b.getNorthEast().lng());
              vertices.setAt(b.getNorthEast().lat(),b.getSouthWest().lng());
              vertices.setAt(b.getSouthWest().lat(),b.getSouthWest().lng());
              vertices.setAt(b.getSouthWest().lat(),b.getNorthEast().lng());		
        }
        var str_vertices = '';
        var frt_vertex = '';

        for (var i = 0; i < vertices.getLength(); i++) {
            var xy = vertices.getAt(i);
            str_vertices += xy.lat() + ' ' + xy.lng() + ', ';					
            if (i === 0){
                frt_vertex = xy.lat() + ' ' + xy.lng();	
            }					
        }
        str_vertices += frt_vertex; 
        return str_vertices;
   },
   selection_delete : function()
   {
        if (this.selected_shape){ 
            var vert = this.get_verticies(this.selected_shape);
            var mapid = this.selected_shape.getMap().getDiv().getAttribute('id');		   
            if ($('#'+mapid+'_polygon').val() == vert){
                    $('#'+mapid+'_polygon').val('')
            }
            this.selected_shape.setMap(null);
            this.selected_shape = null;
            OclGmapBox.refresh_datagrid(mapid);
        }
   },
   selection_clear : function()
   {	   
        if (OclGmapBox.selected_shape){
            self = OclGmapBox.selected_shape;		    		   
            if (self.getEditable()){			
                var vert = OclGmapBox.get_verticies(self);
                mapid = OclGmapBox.selected_shape.getMap().getDiv().getAttribute('id');
                if (vert != $('#'+mapid+'_polygon').val()){
                        $('#'+mapid+'_polygon').val(vert);	
                        OclGmapBox.refresh_datagrid('mapid');				   
                }
            }
            OclGmapBox.selected_shape.setEditable(false);
            OclGmapBox.selected_shape = null;
        }
   },
   selection_click : function(shape)
   {
	OclGmapBox.selection_clear();		
        OclGmapBox.selected_shape = shape;        
	OclGmapBox.selected_shape.setEditable(true);
   },
   selection_init : function(shape)
   {				
	this.selection_clear();		
        this.selected_shape = shape;        
	this.selected_shape.setEditable(true);
	google.maps.event.addListener(shape, 'click', function(e) {
            console.log(this.getMap().getDiv().getAttribute('id'));
            OclGmapBox.selection_click(this);				
        });	
	google.maps.event.addListener(shape, 'dblclick', function(e) {													
            OclGmapBox.selection_delete(shape);
            e.preventDefault();
        });
	google.maps.event.addListener(shape, 'bounds_changed', function() {				
            var mapid = this.getMap().getDiv().getAttribute('id');								
            OclGmapBox.set_bounds2(mapid,this.getBounds());		
            OclGmapBox.refresh_datagrid(mapid);
        });		
   },
   draw_rectangle : function(raw_map)
   {
	var map = $(raw_map).gmap3('get');
	var bounds = new google.maps.LatLngBounds(
		new google.maps.LatLng(44.490, -78.649),
		new google.maps.LatLng(44.599, -78.443)
	);

	// Define the rectangle and set its editable property to true.
	var rectangle = new google.maps.Rectangle({
            bounds: bounds,
            editable: true,
            draggable: true
	});
		
	rectangle.setMap(map);

	// Add an event listener on the rectangle.
	google.maps.event.addListener(rectangle, 'bounds_changed', showNewRect);
   },
   open_infowindow  : function(obj,marker,event,context)
   {
	var map = $(obj).gmap3('get');
	var infowindow = $(obj).gmap3({get:{name:"infowindow"}});
        this.refresh_datagrid_blocked = true;
        if (infowindow){
            infowindow.close();
            infowindow.open(map, marker);
            infowindow.setContent(context.data);   	  
	} else {
            $(obj).gmap3({
		infowindow:{ anchor:marker,  options:{content: context.data} }
            });
        }
	setTimeout(
            function() { 
                OclGmapBox.refresh_datagrid_blocked = false; 
            },
            1000
        );
   },
   open_id : function(map,oid){
        var map = $("#"+map);
        mrk = map.gmap3({ 
            get: {
                id : oid ,
                full : true
            } 
        });            
		
        if (mrk) {                    
            this.refresh_datagrid_blocked = true;
            var infowindow = map.gmap3({get:{name:"infowindow"}});
            //console.log(mrk);
            if (infowindow){
                infowindow.open(map.gmap3("get"), mrk.object);
                infowindow.setContent(mrk.data);
            } else {
                map.gmap3({	infowindow:	{ anchor: mrk.object, options:{content: mrk.data}}});
            }	
            setTimeout(
                function() { 
                    OclGmapBox.refresh_datagrid_blocked = false; 
                },
                1000
            );	    
        }                    
   },
   refresh_datagrid : function(map,div){		       
        /*if (gridid = $(div).data('datagrid-parent'))
        {			
                var mid = $(div).attr('id');
                odatagrid.refresh_ajax($('#'+gridid),null,function(){OclGmapBox.refresh_markers(mid)});
        }*/
        if (this.datagrid.length > 0){
            for(var i in this.datagrid ){
                var gid = this.datagrid[i]; //Datagrid id
                var mid = $(div).attr('id'); //Map id
                OclDataGrid.refresh_ajax(
                    $('#'+gid),
                    null,
                    function(){
                        OclGmapBox.refresh_markers(mid)
                    }
                );
            }
        }
   },
   refresh_markers : function(mapid)
   {
        if (this.datagrid.length == 0) { 
            return; 
        }
	for (i in this.datagrid){			
            var grd = this.datagrid[i];
            var gob = $('#'+this.datagrid[i]);
            //For other marker color view http://mabp.kiev.ua/2010/01/12/google-map-markers/
            var mrk_ico = gob.attr('marker-url') ? gob.attr('marker-url') : 'http://maps.google.com/mapfiles/marker.png';			
            var mapoption = {
                marker : {
                    values : [],
                    events : {  
                        click : function(marker,event,context){	
                            OclGmapBox.open_infowindow(this,marker,event,context);
                            return;
                        }
                    }
                    /*,				 					cluster:{
                      radius: 30,
                      events:{ // events trigged by clusters 
                            mouseover: function(cluster){  $(cluster.main.getDOMElement()).css("border", "1px solid red"); },
                            mouseout: function(cluster) {  $(cluster.main.getDOMElement()).css("border", "0px");	}
                      },
                      0 : {content: "<div class='cluster cluster-1'>CLUSTER_COUNT</div>", width: 53, height: 52},
                      20: {content: "<div class='cluster cluster-2'>CLUSTER_COUNT</div>", width: 56, height: 55},
                      50: {content: "<div class='cluster cluster-3'>CLUSTER_COUNT</div>", width: 66, height: 65}
                    }*/
                }
            }
            if (!(f = gob.data('mapgrid-infowindow-format'))){
                 f = null;
            }
            $('tr',gob).each(function() {
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
                //console.log(frm);
                //dat = '<div style="width: 250px; height: 100px;">'+ frm +'</div>';
		//dat = frm;
                var dat = '<div class="osy-mapgrid-infowindow" kid="'+$(this).attr('oid')+'">'+ frm +'</div>';
                if ($(this).attr('lat')) {
                   var lat = $(this).attr('lat');
                   var lng = $(this).attr('lng');
                   var oid = $(this).attr('oid');
                   mapoption.marker.values.push({
                       latLng : [lat,lng],
                       data:dat,
                       id : oid,
                       options:{
                           icon: mrk_ico
                       }
                   });       
               }
            });
            $('#'+mapid).gmap3(mapoption);
        }
    },
    clear_markers : function(mapid)
    {
	$('#'+mapid).gmap3({
	    clear: {
                name:["marker"]
	    }
	});
    },   
    set_bounds : function(map, div)
    {
        var mid = $(div).attr('id');
        var zom = map.getZoom();
        var bounds = map.getBounds();		
        var ne = bounds.getNorthEast();
        var sw = bounds.getSouthWest();		
        $('#'+mid+'_ne_lat').val(ne.lat());
        $('#'+mid+'_ne_lng').val(ne.lng());
        $('#'+mid+'_sw_lat').val(sw.lat());
        $('#'+mid+'_sw_lng').val(sw.lng()); 
        $('#'+mid+'_center').val(((ne.lat() + sw.lat()) / 2) + ',' + ( (ne.lng() + sw.lng()) / 2));
        $('#'+mid+'_zoom').val(zom); 
    },
    set_bounds2 : function(mapid, bounds)
    {	    
        var ne = bounds.getNorthEast();
        var sw = bounds.getSouthWest();
        $('#'+mapid+'_ne_lat').val(ne.lat());
        $('#'+mapid+'_ne_lng').val(ne.lng());
        $('#'+mapid+'_sw_lat').val(sw.lat());
        $('#'+mapid+'_sw_lng').val(sw.lng()); 
        $('#'+mapid+'_center').val(((ne.lat() + sw.lat()) / 2) + ',' + ( (ne.lng() + sw.lng()) / 2));	
    }
};

if (window.FormController) {    
    FormController.register('init','OclGmapBox',function(){
        OclGmapBox.init();
    });
}
