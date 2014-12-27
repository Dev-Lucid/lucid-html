<?php
if (!class_exists('lucid_html_data_filter'))
{
    include(__DIR__.'/lucid_html_data_filter.php');
}

class lucid_html_data_filter_select extends lucid_html_data_filter implements interface__lucid_html_data_filter
{
    function __construct($name,$column='',$prefix,$data)
    {
        $this->name   = $name;
        $this->column = $column;
        $this->prefix = $prefix;
        $this->data   = $data;
    }

    public function render()
    {
        $html = '';

        $html .='<select class="form-control" name="'.$this->name.'" onchange="lucid.html.data_tables[\''.$this->parent->identifier.'\'].updateFilterValue(this.name,this.options[this.selectedIndex].value,true);">';
        foreach($this->data as $option)
        {
            $html .= '<option value="'.$option[0].'"';
            if(isset($option[2]) and $option[2] === true)
            {
                $html .= ' selected="selected"';
            }
            $html .= '>'.$this->prefix.': '.$option[1];
            $html .= '</option>';
        }
        $html .= '</select>';

        return $html;
    }

    public function apply_to_query()
    {
        if (! is_null($this->value) and $this->value != (-1))
        {
            $this->parent->model->where($this->column,'=',$this->value); 
        }
    }
}


?>