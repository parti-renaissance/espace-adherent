import React from 'react';
import { render } from 'react-dom';
import DataGrid from '../../components/DataGrid';

export default class DataGridFactory
{
    constructor(searchEngine) {
        this.searchEngine = searchEngine;
    }

    createDataGrid(columns, items, perPage, onSelectedChange, classPrefix, orderBy) {
        return (
            <DataGrid
                searchEngine={this.searchEngine}
                columns={columns}
                orderBy={orderBy}
                rows={items}
                perPage={perPage}
                pagerClassName={classPrefix+"__pager"}
                tableClassName={classPrefix+"__table"}
                searchClassName={classPrefix+"__search"}
                onSelectedChange={onSelectedChange}
            />
        );
    }
}
