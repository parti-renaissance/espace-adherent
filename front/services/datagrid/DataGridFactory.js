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

    createReferentCommitteesDataGrid(committees) {
        return (
            <DataGrid
                searchEngine={this.searchEngine}
                columns={this._referentCommitteesColumns()}
                orderBy={'slug'}
                rows={committees}
                perPage={50}
                pagerClassName={"referent__committees-list__pager"}
                tableClassName={"referent__committees-list__table"}
                searchClassName={"referent__committees-list__search"}
                selectable={false}
                searchable={true}
                onSelectedChange={() => {}}
            />
        );
    }

    _referentCommitteesColumns() {
        return [
            {
                key: 'id',
                name: 'ID',
                style: {
                    width: 25,
                },
            },
            {
                key: 'name',
                name: 'Nom',
                linkKey: 'url',
            },
            {
                key: 'postalCode',
                name: 'Code postal',
                style: {
                    width: 100,
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
                key: 'membersCounts',
                name: 'Nombre de suiveurs',
                className: 'datagrid__table__col--hide-mobile',
                style: {
                    width: 50,
                },
            },
        ];
    }
}
