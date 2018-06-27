import React from 'react';
import { render } from 'react-dom';
import SearchEngine from '../services/datagrid/SearchEngine';
import DataGridFactory from '../services/datagrid/DataGridFactory';

/*
 * List of items (for example, committees or events) managed by an adherent
 * with a specific role (for example, referent or deputy).
 */
export default (slugifier, columns, items) => {
    const dataGridFactory = new DataGridFactory(new SearchEngine(slugifier));
    const dataGrid = dataGridFactory.createDataGrid(
        columns,
        items,
        50,
        null,
        'managed__list',
        'name'
    );

    render(dataGrid, dom('#datagrid'));
};
