import React from 'react';
import { render } from 'react-dom';
import SearchEngine from '../services/datagrid/SearchEngine';
import DataGridFactory from '../services/datagrid/DataGridFactory';

/*
 * Referent list
 */
export default (slugifier, columns, items) => {
    const dataGridFactory = new DataGridFactory(new SearchEngine(slugifier));
    const dataGrid = dataGridFactory.createDataGrid(
        columns,
        items,
        50,
        null,
        'referent__list',
        'name'
    );

    render(dataGrid, dom('#datagrid'));
};
