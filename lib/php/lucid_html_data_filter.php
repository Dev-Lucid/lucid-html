<?php

interface interface__lucid_html_data_filter
{
    public function render();
    public function apply_to_query();
}

class lucid_html_data_filter
{

    var $name   = '';
    var $column = '';
    var $value  = null;
    var $idx    = null;
    var $parent = null;

    function __construct($name,$column='')
    {
        $this->name   = $name;
        $this->column = $column;
    }

    function get_value_from_request()
    {
        $this->value = ( isset($_REQUEST['filters'][$this->name]))?$_REQUEST['filters'][$this->name]:$this->value;
        return $this->value;
    }
}

?>