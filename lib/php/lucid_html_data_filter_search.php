<?php
if (!class_exists('lucid_html_data_filter'))
{
    include(__DIR__.'/lucid_html_data_filter.php');
}

class lucid_html_data_filter_search extends lucid_html_data_filter implements interface__lucid_html_data_filter
{    
    public function render()
    {
        return '
        <div class="input-group">
            <span class="input-group-addon"><i class="glyphicon glyphicon-search"></i></span>
            <input type="text" name="search" class="form-control" onkeyup="lucid.html.data_tables[\''.$this->parent->identifier.'\'].updateFilterValue(this.name,this.value,false);">
            <span class="input-group-addon" onclick="lucid.html.data_tables[\''.$this->parent->identifier.'\'].clearFilter($(this).parent().find(\'input\'));"><i class="glyphicon glyphicon-remove"></i></span>
        </div>';
    }

    public function apply_to_query()
    {
        if (! is_null($this->value))
        {
            $this->parent->model->where($this->column,'%',$this->value); 
        }
    }
}


?>