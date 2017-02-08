import React, { PropTypes } from 'react';

export default class DataGrid extends React.Component {
    constructor(props) {
        super(props);

        this._rows = props.rows;
        this._perPage = props.perPage || 2;

        this.state = {
            term: '',
            selected: {},
            headerCheckboxChecked: false,
            page: 1,
        };

        this.handleSearchInputChange = this.handleSearchInputChange.bind(this);
        this.handlePagerClick = this.handlePagerClick.bind(this);
        this.handleHeaderCheckboxChange = this.handleHeaderCheckboxChange.bind(this);
        this.handleCheckboxChange = this.handleCheckboxChange.bind(this);
    }

    handleSearchInputChange(event) {
        this.setState({
            term: event.target.value,
            selected: {},
            headerCheckboxChecked: false,
        });

        this.props.onSelectedChange([]);
    }

    handlePagerClick(newPage) {
        this.setState({
            page: newPage,
        });
    }

    handleHeaderCheckboxChange(event) {
        if (!event.target.checked) {
            this.setState({
                selected: {},
                headerCheckboxChecked: false,
            });
        }

        const results = this._buildResultsCollection();
        const selected = {};

        Object.keys(results).forEach((i) => {
            if (event.target.checked) {
                selected[i] = results[i];
            }
        });

        this.setState({
            selected,
            headerCheckboxChecked: event.target.checked,
        });

        this.props.onSelectedChange(selected);
    }

    handleCheckboxChange(i, event) {
        const selected = this.state.selected;

        if (event.target.checked) {
            selected[i] = this._buildResultsCollection()[i];
        } else {
            delete selected[i];
        }

        this.setState({
            selected,
            headerCheckboxChecked: false,
        });

        this.props.onSelectedChange(selected);
    }

    _buildResultsCollection() {
        if (0 === this.state.term.length) {
            return this._rows;
        }

        return this.props.searchEngine.search(this.props.columns, this._rows, this.props.orderBy, this.state.term);
    }

    render() {
        const results = this._buildResultsCollection();
        const totalCount = results.length;
        const pagesCount = Math.max(1, Math.ceil(totalCount / this._perPage));
        const currentPage = Math.min(this.state.page, pagesCount);

        return (
            <div className="datagrid">
                <div className={`datagrid__search ${this.props.searchClassName || ''}`}>
                    <input type="text"
                           placeholder="Recherche ..."
                           className="form form__field"
                           onChange={this.handleSearchInputChange} />
                </div>

                <div className={`datagrid__pager ${this.props.pagerClassName || ''}`}>
                    <ul>
                        {this._buildPagesList(pagesCount, currentPage, 'top')}
                    </ul>
                </div>

                <table className={`datagrid__table ${this.props.tableClassName || ''}`}>
                    <thead>
                    <tr>
                        {this._buildColumns(this.props.columns)}
                    </tr>
                    </thead>
                    <tbody>
                    {this._buildResultsList(this.props.columns, results, this.state.selected, currentPage)}
                    </tbody>
                </table>

                <div className={`datagrid__pager ${this.props.pagerClassName || ''}`}>
                    <ul>
                        {this._buildPagesList(pagesCount, currentPage, 'bottom')}
                    </ul>
                </div>
            </div>
        );
    }

    _buildPagesList(pagesCount, current, position) {
        const from = Math.max(1, current - 2);
        const to = Math.min(pagesCount, current + 2);

        const pagesList = [];

        if (1 !== from) {
            pagesList.push(
                <li key={`page-${position}-before`}>
                    <button type="button"
                            className={'btn btn--disabled'}
                            disabled={true}>
                        ...
                    </button>
                </li>
            );
        }

        for (let i = from; i <= to; i += 1) {
            pagesList.push(
                <li key={`page-${position}-${i}`}>
                    <button type="button"
                            className={`btn ${i === current ? 'btn--disabled' : ''}`}
                            disabled={i === current}
                            onClick={() => this.handlePagerClick(i)}>
                        {i}
                    </button>
                </li>
            );
        }

        if (to !== pagesCount) {
            pagesList.push(
                <li key={`page-${position}-after`}>
                    <button type="button"
                            className={'btn btn--disabled'}
                            disabled={true}>
                        ...
                    </button>
                </li>
            );
        }

        return pagesList;
    }

    _buildColumns(columns) {
        const columnsList = [];

        columnsList.push(
            <th key={'column-select'} style={{ width: 25 }}>
                <input type="checkbox"
                       onChange={this.handleHeaderCheckboxChange}
                       checked={this.state.headerCheckboxChecked} />
            </th>
        );

        Object.keys(columns).forEach((i) => {
            columnsList.push(
                <th key={`column${columns[i].key}`}
                    style={columns[i].style || null}
                    className={columns[i].className || ''}>
                    {columns[i].name}
                </th>
            );
        });

        return columnsList;
    }

    _buildResultsList(columns, results, selected, currentPage) {
        const offset = (currentPage - 1) * this._perPage;
        const limit = offset + this._perPage;

        const resultsList = [];

        for (let i = offset; i < limit; i += 1) {
            const result = results[i];

            if ('undefined' === typeof result) {
                break;
            }

            const resultColumns = [];

            resultColumns.push(
                <td key={`result${i}-column-select`}>
                    <input type="checkbox"
                           onChange={(event) => { this.handleCheckboxChange(i, event); }}
                           checked={'undefined' !== typeof selected[i] && selected[i]} />
                </td>
            );

            Object.keys(columns).forEach((j) => {
                resultColumns.push(
                    <td key={`result${i}-column${j}`}
                        style={columns[j].style || null}
                        className={columns[j].className || ''}>
                        {result[columns[j].key]}
                    </td>
                );
            });

            resultsList.push(
                <tr key={`result${i}`}>
                    {resultColumns}
                </tr>
            );
        }

        if (0 === resultsList.length) {
            resultsList.push(
                <tr>
                    <td colSpan={columns.length + 1}>
                        Aucun résultat
                    </td>
                </tr>
            );
        }

        return resultsList;
    }
}

DataGrid.propTypes = {
    columns: PropTypes.array.isRequired,
    rows: PropTypes.array.isRequired,
    searchEngine: PropTypes.object.isRequired,
    orderBy: PropTypes.string.isRequired,
    onSelectedChange: PropTypes.func.isRequired,
    perPage: PropTypes.number,
    tableClassName: PropTypes.string,
    pagerClassName: PropTypes.string,
    searchClassName: PropTypes.string,
};
