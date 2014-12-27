<?php

class lucid_html
{
    var $config = array();
    var $html_include_path = __DIR__;

    function __construct($config=[])
    {
        foreach($config as $key=>$value)
        {
            $this->config[$key] = $value;
        }
    }

    public function __call($object_type,$params)
    {
        if(!class_exists('lucid_html_'.$object_type))
        {
            $file_name = $this->html_include_path.'/lucid_html_'.$object_type.'.php';
            if(file_exists($file_name))
            {
                include($file_name);
            }
        }
        if(!class_exists('lucid_html_'.$object_type))
        {
            throw new Exception('Lucid_html: could find class '.$object_type.', searched '.$this->html_include_path);
        }
        $class = new ReflectionClass('lucid_html_'.$object_type);
        $instance = $class->newInstanceArgs($params);
        return $instance;
    }
}

$lucid->html = new lucid_html();
?>