<?php

class IFrameTab extends component
{
    private $iframe;
    
    public function __construct($name)
    {
       parent::__construct('div',$name);
       $this->att('class','osy-iframe-tab tabs');
       $this->requireJs('/__assets/osynapsy/Ocl/IFrame.js');
       //osy_form::$page->add_script('../lib/jquery/jquery.scrollabletab.js');
    }
    
    protected function __build_extra__()
    {
        $this->add(tag::create('ul'));
        //$this->iframe = $this->add(tag::create('iframe'));
        //$this->iframe->att('name',$this->id)->att("style",'width: 100%;');
        $src = $this->get_par('src');
        if (!key_exists($this->id,$_REQUEST) && !empty($src))
		{
            $_REQUEST[$this->id] = $src;
        }
        if(key_exists($this->id,$_REQUEST) && !empty($_REQUEST[$this->id]))
        {
            $this->att('src',$_REQUEST[$this->id]);
        }
    }
}