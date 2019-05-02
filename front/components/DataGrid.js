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
            loading: false,
            results: [],
        };

        this.handleSearchInputChange = this.handleSearchInputChange.bind(this);
        this.handlePagerClick = this.handlePagerClick.bind(this);
        this.handleHeaderCheckboxChange = this.handleHeaderCheckboxChange.bind(this);
        this.handleCheckboxChange = this.handleCheckboxChange.bind(this);
    }

    componentWillMount() {
        this.setState({ results: this._buildResultsCollection() });
    }
    handleSearchInputChange(event) {
        const term = event.target.value;

        this.setState({
            loading: true,
        });

        setTimeout(() => {
            this.setState({
                term,
                selected: {},
                headerCheckboxChecked: false,
                loading: false,
            });

            this.props.onSelectedChange([]);
        }, 100);
    }

    handlePagerClick(newPage) {
        this.setState({
            page: newPage,
        });
    }

    handleHeaderCheckboxChange(event) {
        const checked = event.target.checked;

        if (!checked) {
            this.setState({
                selected: {},
                headerCheckboxChecked: false,
            });

            this.props.onSelectedChange([]);
            return true;
        }

        const results = this._buildResultsCollection();
        const selected = {};

        Object.keys(results).forEach((i) => {
            selected[i] = results[i];
        });

        this.setState({
            selected,
            headerCheckboxChecked: checked,
            loading: false,
        });

        this.props.onSelectedChange(selected);
        return true;
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
        const totalCount = this.state.results.length;
        const pagesCount = Math.max(1, Math.ceil(totalCount / this._perPage));
        const currentPage = Math.min(this.state.page, pagesCount);

        return (
            <div className="datagrid">
                {this.state.loading ? <div className="datagrid__loader">Chargement ...</div> : '' }

                <table className={
                    `datagrid__table-manager
                    ${this.props.tableClassName || ''}
                    ${this.state.loading ? 'datagrid__table--loading' : ''}`
                }>
                    <thead>
                    <tr>
                        {this._buildColumns(this.props.columns)}
                    </tr>
                    </thead>
                    <tbody>
                    {this._buildResultsList(this.props.columns, this.state.results, this.state.selected, currentPage)}
                    </tbody>
                </table>

                {1 < pagesCount &&
                    <div className={`datagrid__pager ${this.props.pagerClassName || ''}`}>
                        <ul>
                            <li>
                                <div className="pager__go-to-page">
                                    <span>Aller à la page</span>
                                    <input type="number" placeholder="5" className="pager__action" />
                                    <span>{currentPage} sur {pagesCount}</span>
                                </div>

                            </li>
                            {this._buildPagesList(pagesCount, currentPage, 'bottom')}
                        </ul>
                    </div>
                }
            </div>
        );
    }
    _buildPagesList(pagesCount, current, position) {
        const from = Math.max(1, current - 2);
        const to = Math.min(pagesCount, current + 2);

        const pagesList = [];

        pagesList.push(
            <li key={`page-${position}-prec`}>
                <button type="button"
                        className="pager__action switch"
                        disabled={1 === current}
                        onClick={() => this.handlePagerClick(Math.max(1, current - 1))}>
                    <svg xmlns="http://www.w3.org/2000/svg" width="9" height="14" viewBox="0 0 9 14">
                      <polygon
                        fill="#444"
                        points="27.45 22.571 27.45 24.571 18.45 24.571 18.45 15.571 20.45 15.571 20.45 22.571"
                        transform="rotate(45 30.642 -5.743)"/>
                    </svg>
                </button>
            </li>
        );

        pagesList.push(
            <li key={`page-${position}-suiv`}>
                <button type="button"
                        className="pager__action switch"
                        disabled={pagesCount === current}
                        onClick={() => this.handlePagerClick(Math.min(pagesCount, current + 1))}>
                    <svg xmlns="http://www.w3.org/2000/svg" width="9" height="14" viewBox="0 0 9 14">
                      <polygon
                        fill="#444"
                        points="27.45 22.571 27.45 24.571 18.45 24.571 18.45 15.571 20.45 15.571 20.45 22.571"
                        transform="scale(-1 1) rotate(45 26.142 -16.607)"/>
                    </svg>
                </button>
            </li>
        );

        return pagesList;
    }

    _buildColumns(columns) {
        const columnsList = [];

        if (!this.props.disableSelect) {
            columnsList.push(
                <th key={'column-select'} style={{ width: 25 }}>
                    <input type="checkbox"
                           onChange={this.handleHeaderCheckboxChange}
                           checked={this.state.headerCheckboxChecked} />
                </th>
            );
        }

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

            if (!this.props.disableSelect) {
                resultColumns.push(
                    <td key={`result${i}-column-select`}>
                        <input type="checkbox"
                               onChange={(event) => {
                                   this.handleCheckboxChange(i, event);
                               }}
                               checked={'undefined' !== typeof selected[i] && selected[i]} />
                    </td>
                );
            }

            Object.keys(columns).forEach((j) => {
                if ('undefined' !== typeof columns[j].link && columns[j].link) {
                    const targetBlank = 'undefined' !== typeof columns[j].targetBlank && columns[j].targetBlank;

                    resultColumns.push(
                        <td key={`result${i}-column${j}`}
                            style={columns[j].style || null}
                            className={columns[j].className || ''}>
                            <a target={targetBlank ? '_blank' : '_self'}
                               href={result[columns[j].key].url}
                               dangerouslySetInnerHTML={{ __html: result[columns[j].key].label }}>
                            </a>
                        </td>
                    );
                } else {
                    resultColumns.push(
                        <td key={`result${i}-column${j}`}
                            style={columns[j].style || null}
                            className={columns[j].className || ''}
                            dangerouslySetInnerHTML={{ __html: result[columns[j].key] }}>
                        </td>
                    );
                }
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
    disableSelect: PropTypes.bool,
};
