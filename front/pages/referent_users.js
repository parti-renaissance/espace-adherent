import React from 'react';
import { render } from 'react-dom';
import SearchEngine from '../services/datagrid/SearchEngine';
import DataGridFactory from '../services/datagrid/DataGridFactory';

/*
 * Referent users list
 */
export default (slugifier, users) => {
    const dataGridFactory = new DataGridFactory(new SearchEngine(slugifier));
    const selectedUsersCount = dom('#selected-users-count');
    const sendMailBtn = dom('#send-mail-btn');

    let selected = [];

    const onSelectedChange = (rows) => {
        selected = [];

        Object.keys(rows).forEach((key) => {
            selected.push(rows[key]);
        });

        selectedUsersCount.innerHTML = selected.length;
    };

    const columns = [
        {
            key: 'id',
            name: 'ID',
            style: {
                width: 25,
            },
        },
        {
            key: 'postalCode',
            name: 'Code postal',
            style: {
                width: 100,
            },
        },
        {
            key: 'name',
            name: 'Nom',
        },
        {
            key: 'age',
            name: 'Age',
            className: 'datagrid__table__col--hide-mobile',
            style: {
                width: 50,
            },
        },
        {
            key: 'city',
            name: 'Ville',
            className: 'datagrid__table__col--hide-mobile',
        },
        {
            key: 'country',
            name: 'Pays',
            className: 'datagrid__table__col--hide-mobile',
            style: {
                width: 60,
            },
        },
        {
            key: 'email',
            name: 'Addresse e-mail (si publique)',
            className: 'datagrid__table__col--hide-mobile',
        },
        {
            key: 'emailsSubscription',
            name: 'Abonné aux mails ?',
            className: 'datagrid__table__col--hide-mobile',
            style: {
                width: 100,
            },
        },
    ];

    const dataGrid = dataGridFactory.createDataGrid(
        columns,
        users,
        50,
        onSelectedChange,
        'referent__users-list',
        'postalCode'
    );

    render(dataGrid, dom('#users-list'));

    on(sendMailBtn, 'click', () => {
        // TODO redirect to send message page
    });
};
