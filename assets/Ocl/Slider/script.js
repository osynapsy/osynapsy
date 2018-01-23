OclSlider = {
    repo : {onstop:{}},
    init : function(){				
        $('.osy-slider-bar').each(function(){
            var opt = {
                min : 0, 
                max : 100 , 
                range : false,
                values : [0,100],
                slide : function(){},
                step  : 1,
                stop  : function(event,ui){							
                    sid = $(this).parent().attr('id');
                    oslider.fire('onstop',sid);					
                }
            };
            for (key in opt){
                val = $(this).parent().data(key);
                if (val == '0' || val){					
                    switch(key) {
                        case 'values' :
                            val = val.split(',');	
                            break;
                        case 'min' :
                            opt.values[0] = val;
                            break;
                        case 'max' :
                            opt.values[1] = val;
                            break;			
                        case 'range':
                            val = true;
                            opt.values[0] = opt.min;
                            opt.values[1] = opt.max;
                            opt.slide = function(event,ui){
                                oid = $(this).parent().attr('id');										   
                                $('.lbl-min', $(this).parent() ).text(ui.values[ 0 ]);
                                $('.lbl-max', $(this).parent() ).text(ui.values[ 1 ]);
                                $('#'+oid+'_min', $(this).parent() ).val(ui.values[ 0 ]);
                                $('#'+oid+'_max', $(this).parent() ).val(ui.values[ 1 ]);
                            }
                        break;
                    }
                    opt[key] = val;		
                } 
            }

            if (opt.range){			    
                if ((ml = (opt.max+'').length) > 3){
                    opt.step = Math.pow(10,ml - 3);					
                }								
            }
            $(this).slider(opt);
        });
    },
    fire : function(evt, id, event, ui)
    {
        if (evt in this.repo) {
            this.repo[evt][id](event,ui);
        }
    },
    onevent : function(evt, id, fnc)
    {
        this.repo[evt][id] = fnc;
    }
}

if (window.FormController) {
    FormController.register('init','OclSlider',function(){
        OclSlider.init();
    });
}
