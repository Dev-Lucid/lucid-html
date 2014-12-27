lucid.html={};

lucid.html.data_tables = {};

lucid.html.data_table=function(identifier,url,page,pageSize,pageCount,sortColumn,sortDirection,filters){
    console.log('initing data_table: '+identifier);
    console.log(arguments);
    this.identifier         = identifier;
    this.url                = url;
    this.data               = {};
    this.data.page          = page;
    this.data.pageSize      = pageSize;
    this.data.pageCount     = pageCount;
    this.data.sortColumn    = sortColumn;
    this.data.sortDirection = sortDirection;
    this.data.filters       = filters;
    this.refreshTimeout     = -1;
    this.jq = $(document.getElementById(this.identifier));

    if(typeof(this.data.filters) != 'object'){
        this.data.filters = {};
    }
    lucid.html.data_tables[identifier] = this;
};

lucid.html.data_table.prototype.changePage=function(newPage){
    switch(newPage){
        case 'first':
            newPage = 0;
            break;
        case 'previous':
            newPage = ((this.data.page - 1) < 0)?0:(this.data.page - 1);
            break;
        case 'next':
            newPage = ((this.data.page + 1) == this.data.pageCount)?(this.data.pageCount - 1):(this.data.page + 1);
            break;
        case 'last':
            newPage = (this.data.pageCount - 1);
            break;
        default:
            // a number was passed, just continue
            break;
    }
    if(newPage == this.data.page){
        console.log('same page selected, no need to do anything');
    }else{
        this.data.page = newPage;
        this.fetchNewData();
    }
};

lucid.html.data_table.prototype.changeSort=function(sortColumn,linkObj){
    var pfx = ' glyphicon-chevron-';

    this.data.sortDirection = (this.data.sortColumn == sortColumn && this.data.sortDirection == 0)?1:0;
    this.data.sortColumn = sortColumn;
    this.data.page = 0;
    this.jq.find('table > thead > tr > th > i').removeClass(pfx+'up'+pfx+'down'+pfx+'right').addClass(pfx+'right');
    
    //update the icon
    var icn = $(linkObj).find('i');
    
    var newClass = pfx+((this.data.sortDirection == 0)?'down':'up');
    icn.removeClass(pfx+'up'+pfx+'down'+pfx+'right').addClass(newClass);
    this.fetchNewData();
};

lucid.html.data_table.prototype.clearFilter=function(jqueryObj){
    jqueryObj.val('');
    this.updateFilterValue(jqueryObj.attr('name'),'');
    this.fetchNewData();
};

lucid.html.data_table.prototype.fetchNewData=function(){
    window.clearTimeout(this.refreshTimeout);
    console.log('refreshing data_table, parameters sent are:');
    console.log(this.data);
    $.ajax({
        'url':'app.php?todo='+this.url,
        'data':this.data,
        'success':function(response){
            console.log('got data back from success fetchNewData:');

            console.log(arguments);
            lucid.handlers.handleResponse(response);
        },
        'failure':function(){
            console.log('got data back from failure fetchNewData:');
            console.log(arguments);
        }
    });
};

lucid.html.data_table.prototype.updateFilterValue=function(filterName,filterValue,refreshImmediately){
    if(this.refreshTimeout !== (-1)){
        window.clearTimeout(this.refreshTimeout);
    }
    this.data.filters[filterName] = filterValue;
    var timeToRefresh = 1200;
    if( refreshImmediately == true){
        timeToRefresh = 1;
    }
    this.refreshTimeout = window.setTimeout(new Function('','lucid.html.data_tables[\''+this.identifier+'\'].fetchNewData();'),timeToRefresh);
}

lucid.html.data_table.prototype.refreshData=function(){
    var newTableConfig = lucid.lastResponse['special'][this.identifier];
    console.log('refreshData is called, new table config is:');
    console.log(newTableConfig);
    this.data.page          = newTableConfig.page;
    this.data.pageCount     = newTableConfig.page_count;
    this.data.sortColumn    = newTableConfig.sort_column;
    this.data.sortDirection = newTableConfig.sort_direction;
    //console.log('trying to set #'+this.identifier+'--tbody to: '+newTableConfig.tbody);
    $(document.getElementById(this.identifier+'--tbody')).html(newTableConfig.tbody);

    // update the page indicator!
    $(document.getElementById(this.identifier+'--page_indicator')).html('Page '+(this.data.page + 1)+' of '+this.data.pageCount);

}