import React from 'react';
import { render } from 'react-dom';
import { CSVLink } from 'react-csv';
import SearchEngine from '../services/datagrid/SearchEngine';
import DataGridFactory from '../services/datagrid/DataGridFactory';

/*
 * Referent users list
 */
export default (slugifier, columns, users) => {
    const dataGridFactory = new DataGridFactory(new SearchEngine(slugifier));
    const selectedUsersCount = dom('#selected-users-count');
    const sendMailBtn = dom('#send-mail-btn');
    const selectedUsersInput = dom('#selected-users-json');
    const headerNames = columns.map(object => object.name);
    const headerKeys = columns.map(object => object.key);
    const filename = `${slugifier.slugify(dom('li.active .csv-export-title').innerHTML)}.csv`;

    const onSelectedChange = (rows) => {
        let count = 0;
        const serialized = [];
        const data = [];

        Object.keys(rows).forEach((key) => {
            serialized.push(`${'adherent' === rows[key].type ? 'a' : 'n'}|${rows[key].id}`);

            // Construct the csv data ordered by column names
            const dataRow = [];
            headerKeys.map(i => dataRow.push(rows[key][i]));
            data.push(dataRow);

            count += 1;
        });

        render(
            <CSVLink
                data={[headerNames, ...data]}
                filename={filename}
                className={`btn btn--blue referent__btn-export-csv${0 === count ? ' disabled' : ''}`}
                onClick={(0 === count) ? e => e.preventDefault() : ''}
            >
                Export csv
            </CSVLink>,
            dom('#csv-export')
        );

        selectedUsersInput.value = serialized.join(',');
        selectedUsersCount.innerHTML = count;
        sendMailBtn.disabled = 0 === count;
    };

    const dataGrid = dataGridFactory.createDataGrid(
        columns,
        users,
        50,
        onSelectedChange,
        'referent__users-list',
        'postalCode'
    );

    render(dataGrid, dom('#users-list'));
};
