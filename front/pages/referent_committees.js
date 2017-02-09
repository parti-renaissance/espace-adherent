import React from 'react';
import { render } from 'react-dom';
import SearchEngine from '../services/datagrid/SearchEngine';
import DataGridFactory from '../services/datagrid/DataGridFactory';

/*
 * Referent committees list
 */
export default (slugifier, committees) => {
    const dataGridFactory = new DataGridFactory(new SearchEngine(slugifier));

    render(
        dataGridFactory.createReferentCommitteesDataGrid(committees),
        dom('#users-list')
    );
};
