<?php

class lucid_html_data_column
{

    var $label           = '';
    var $column          = '';
    var $sortable        = false;
    var $width           = '';
    var $data_renderer   = null;
    var $header_renderer = null;
    var $parent          = null;

    function __construct($label,$column='',$sortable=false,$width='1%',$data_renderer=null,$header_renderer=null)
    {
        $this->label    = $label;
        $this->column   = $column;
        $this->sortable = $sortable;
        $this->width    = $width;
        $this->data_renderer   = $data_renderer;
        $this->header_renderer = $header_renderer;
    }

    function render_width($format = 'html')
    {
        # format can be: html, csv, pdf.
        if ($format === 'html')
        {
            return '<col width="'.$this->width.'" />';
        }
        else if ($format === 'csv')
        {
            # TODO: make this shit work :(
            return '';
        }
        else if ($format === 'pdf')
        {
            # TODO: make this shit work :(
            return '';
        }
    }

    function render_header($format = 'html')
    {
        if(is_callable($this->header_renderer))
        {
            return $this->header_renderer($format);   
        }

        # format can be: html, csv, pdf.
        if ($format === 'html')
        {
            $html  = '<th onclick="lucid.html.data_tables[\''.$this->parent->identifier.'\'].changeSort('.$this->idx.',this);">';
            $html .= '<i class="glyphicon glyphicon-chevron-right"></i> ';
            $html .= $this->label.'</th>';
            return $html;
        }
        else if ($format === 'csv')
        {
            # TODO: make this shit work :(
            return '';
        }
        else if ($format === 'pdf')
        {
            # TODO: make this shit work :(
            return '';
        }
    }

    function render_data($format = 'html', $data = array())
    {
        if(is_callable($this->header_renderer))
        {
            return $this->data_renderer($format,$data);   
        }

        # format can be: html, csv, pdf.
        if ($format === 'html')
        {
            return '<td>'.$data[$this->column].'</td>';
        }
        else if ($format === 'csv')
        {
            return $data[$this->column];
        }
        else if ($format === 'pdf')
        {
            return $data[$this->column];
        }
    }
}

?>