import React from 'react';
import { render } from 'react-dom';
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

    const onSelectedChange = (rows) => {
        let count = 0;
        const json = [];

        Object.keys(rows).forEach((key) => {
            json.push({
                type: 'adherent' === rows[key].type ? 'a' : 'n',
                id: rows[key].id,
            });

            count += 1;
        });

        selectedUsersInput.value = JSON.stringify(json);
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
