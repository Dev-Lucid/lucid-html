<?php

class lucid_html_data_table
{
    var $identifier  = '';
    var $title       = '';
    var $url         = '';
    var $model       = null;

    # sorting and paging fields
    var $page           = 0;
    var $page_size      = 10;
    var $page_count     = 0;
    var $sort_column    = -1;
    var $sort_direction = 0;
    var $filters = [];
    var $columns = [];

    function __construct($title,$url,$model=null)
    {
        $this->title       = $title;
        $this->url         = $url;
        $this->model       = $model;

        # Generate a unique identifier for cookies/url parameters/data indexes based on the crc32 of the url
        $this->identifier  = 'l.h.dt-'.(crc32($this->url));
    }

    function add_column($new_column)
    {
        $new_column->idx    = count($this->columns);
        $new_column->parent = $this;
        $this->columns[]    = $new_column;
        return $this;
    }

    function add_filter($new_filter)
    {
        $new_filter->idx    = count($this->filters);
        $new_filter->parent = $this;
        $this->filters[]    = $new_filter;
        return $this;
    }

    function render($format='html')
    {
        $html = '';
        $js   = '';

        $data = $this->get_data($format);

        $html .= '
<div class="panel panel-primary" id="'.$this->identifier.'">
    <div class="panel-heading">
        <div class="row">
            <div class="pull-left col-xs-12 col-sm-12 col-md-6 col-lg-6">
                <h4>'.$this->title.'</h4>
            </div>
            ';
        


        $html .= '
        </div>
    </div>
    <div class="table-responsive">
        <table class="table table-striped table-condensed table-hover">
        ';

        foreach($this->columns as $column)
        {
            $html .= $column->render_width('html');
        }

        $html .= '
            <thead>
                <tr>
        ';

        foreach($this->columns as $column)
        {
            $html .= $column->render_header('html');
        }

        $html .= '
                </tr>
            </thead>
        ';

        $html .= '<tbody id="'.$this->identifier.'--tbody">'.$data['tbody'].'</tbody>';

        $html .= '
        </table>
    </div>

    <div class="panel-footer">
        <div class="row">
            <div class="col-xs-12 col-sm-6 col-md-6 col-lg-6">

        ';

        foreach($this->filters as $filter)
        {
            $html .= $filter->render();
        }


        $html .='
            </div>
            <div class="col-xs-12 col-sm-6 col-md-6 col-lg-6" >
        
            
                <div class="btn-group pull-right" role="group">
                    <button class="btn btn-info" type="button" onclick="lucid.html.data_tables[\''.$this->identifier.'\'].changePage(\'first\');"><i class="glyphicon glyphicon-step-backward"></i></button>
                    <button class="btn btn-info" type="button" onclick="lucid.html.data_tables[\''.$this->identifier.'\'].changePage(\'previous\');"><i class="glyphicon glyphicon-backward"></i></button>
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-info dropdown-toggle" data-toggle="dropdown" aria-expanded="false" onclick="" id="'.$this->identifier.'--page_indicator">
                            Page '.($data['page'] + 1).' of '.($data['page_count']).' <span class="caret"></span>
                        </button>
                        <ul class="dropdown-menu" role="menu">
        ';
        for($i = 0; $i < $data['page_count']; $i++)
        {
            $html .= '<li><a onclick="lucid.html.data_tables[\''.$this->identifier.'\'].changePage('.$i.');">Page '.($i + 1).' of '.$data['page_count'].'</a></li>';
        }
        $html .= '
                        </ul>
                    </div>
                    <button class="btn btn-info" type="button" onclick="lucid.html.data_tables[\''.$this->identifier.'\'].changePage(\'next\');"><i class="glyphicon glyphicon-forward"></i></button>
                    <button class="btn btn-info" type="button" onclick="lucid.html.data_tables[\''.$this->identifier.'\'].changePage(\'last\');"><i class="glyphicon glyphicon-step-forward"></i></button>
                </div>
        
            </div>
        </div>
    </div>
</div>
        ';

        lucid::javascript("new lucid.html.data_table('".$this->identifier."','".$this->url."',".$this->page.",".$this->page_size.",".$this->page_count.",".$this->sort_column.");");
        return $html;
    }

	public function get_data($format='html')
	{
        # parse parameters sent in request
        lucid::log('looking for parameters to table: ');
        lucid::log(print_r($_REQUEST,true));
        $this->page           = ( isset($_REQUEST['page']))?intval($_REQUEST['page']):$this->page;
        $this->page_size      = ( isset($_REQUEST['pageSize']))?intval($_REQUEST['pageSize']):$this->page_size;
        $this->sort_column    = ( isset($_REQUEST['sortColumn']) && is_numeric($_REQUEST['sortColumn']))?intval($_REQUEST['sortColumn']):$this->sort_column;
        $this->sort_direction = ( isset($_REQUEST['sortDirection']))?intval($_REQUEST['sortDirection']):$this->sort_direction;

        # determine the best sort column if they the value is still null.
        if ($this->sort_column < 0)
        {
            lucid::log('no sort column is set, find the first sortable one');
            $counter = 0;
            foreach($this->columns as $column)
            {
                if ($column->sortable == true)
                {   
                    $this->sort_column = $counter;
                    break;
                }
                $counter++;
            }
        }

        $filter_data = [];
        foreach($this->filters as $filter)
        {
            $filter->get_value_from_request();
            $filter->apply_to_query();
            $filter_data[$filter->name] = $filter->value;
        }

        # parse the new filter settings
        $data = array(
            'tbody'          => '',
            'sort_column'    => $this->sort_column,
            'sort_direction' => $this->sort_direction,
            'page'           => $this->page,
            'page_size'      => $this->page_size,
            'page_count'     => 0,
            'filters'        => $filter_data,
        );


        # calculate the number of pages. This requires running the full query 
        # with all where clauses, but no limit/offset, and a fake column (count(1) as row_count)
        $unpaged_count = $this->model->select_unpaged_count();
        $data['page_count'] = ceil($unpaged_count / $this->page_size);
        $this->page_count = $data['page_count'];
 
        # apply sorts, limits, offset; then run the final query
        $this->model->limit($this->page_size);
        $this->model->offset($this->page * $this->page_size);
        if($this->sort_column >= 0)
        {
            $this->model->order_by($this->columns[$this->sort_column]->column.' '.(($this->sort_direction == 0)?'':'desc'));
        }
        $results = $this->model->select();

        # render the data out.
        foreach($results as $row)
        {
            $data['tbody'] .= '<tr>';
            foreach($this->columns as $column)
            {
                $data['tbody'] .= $column->render_data($format,$row);
            }
            $data['tbody'] .= '</tr>';
        }

        # render the paging indicators? Hmmmmmm.

        return $data;
	}

    public function get_refresh_js()
    {
        return 'lucid.html.data_tables[\''.$this->identifier.'\'].refreshData();';
    }
}

?>