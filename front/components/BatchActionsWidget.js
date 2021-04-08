import React from 'react';
import PropTypes from 'prop-types';
import ReqwestApiClient from '../services/api/ReqwestApiClient';
import Loader from './Loader';

export default class BatchActionsWidget extends React.Component {
    constructor(props) {
        super(props);

        this.api = props.api;
        this.checkboxSelector = props.checkboxSelector;
        this.actions = props.actions;
        this.checkboxes = findAll(document, props.checkboxSelector);

        if (0 === this.checkboxes.length) {
            throw new Error('Checkbox list is empty');
        }

        this.table = this.checkboxes[0].closest('table');

        this.mainCheckboxSelector = props.mainCheckboxSelector;

        this.state = {
            checked: [],
            error: null,
            processing: false,
        };

        this.handleResetClick = this.handleResetClick.bind(this);
        this.handleCheckboxChange = this.handleCheckboxChange.bind(this);
        this.handleTableUpdate = this.handleTableUpdate.bind(this);
        this.handleMainCheckboxChange = this.handleMainCheckboxChange.bind(this);
        this.updateCheckboxState = this.updateCheckboxState.bind(this);
        this.bindCheckboxListener = this.bindCheckboxListener.bind(this);
        this.sendAction = this.sendAction.bind(this);
    }

    componentDidMount() {
        this.mainCheckbox = dom(this.mainCheckboxSelector);
        if (this.mainCheckbox) {
            on(this.mainCheckbox, 'change', this.handleMainCheckboxChange);
        }

        if (this.table) {
            on(this.table, 'table_update', this.handleTableUpdate);
        }

        this.bindCheckboxListener();
    }

    render() {
        const length = this.state.checked.length;

        return (
            <div>
                { this.state.processing ? <Loader /> :
                    <div className="l__row">
                        {0 < length ?
                            <div className="pst--relative">
                                {this.actions.map((action, key) => (
                                    <div key={key}
                                        className="btn-secondary btn-secondary--blue"
                                        data-path={action.path}
                                        data-method={action.method}
                                        onClick={this.sendAction}>
                                    ({length}) {action.name}
                                    </div>
                                ))}
                            </div>
                            : ''}

                        {0 < length ?
                            <a href="#"
                                className="btn-secondary btn-secondary--black b__nudge--left-small"
                                onClick={this.handleResetClick}>Effacer la sélection
                            </a>
                            : ''}

                        {this.state.error ? <p className={'text--error b__nudge--top-10 b__nudge--bottom-medium'}>
                            {this.state.error}</p>
                            : ''}
                    </div>
                }

            </div>
        );
    }

    handleResetClick(event) {
        event.preventDefault();

        this.resetChecked();
    }

    handleCheckboxChange(event) {
        const target = event.currentTarget;

        this.updateCheckboxState([target.value], target.checked);
    }

    handleTableUpdate() {
        this.checkboxes = findAll(document, this.checkboxSelector);

        this.bindCheckboxListener();
    }

    handleMainCheckboxChange(event) {
        const changed = [];

        this.checkboxes.forEach((element) => {
            element.checked = event.target.checked;
            changed.push(element.value);
        });

        this.updateCheckboxState(changed, event.target.checked);
    }

    updateCheckboxState(changed, isChecked) {
        if (isChecked) {
            this.setState(state => ({ checked: state.checked.concat(changed) }));
        } else {
            this.setState(state => ({ checked: state.checked.filter(value => -1 === changed.indexOf(value)) }));
        }
    }

    bindCheckboxListener() {
        this.checkboxes.forEach((element) => {
            on(element, 'click', this.handleCheckboxChange);

            if (-1 !== this.state.checked.indexOf(element.value)) {
                element.checked = true;
            }
        });
    }

    resetChecked() {
        this.setState({ checked: [] });

        this.checkboxes.forEach((element) => {
            element.checked = false;
        });

        if (this.mainCheckbox) {
            this.mainCheckbox.checked = false;
            this.mainCheckbox.indeterminate = false;
        }
    }

    sendAction(event) {
        this.setState({
            processing: true,
            error: null,
        });

        const data = {
            ids: this.state.checked,
        };
        this.api._createRequest((responseData) => {
            if ('' !== responseData) {
                this.setState({
                    processing: false,
                    error: 'Une erreur est survenue',
                });
            } else {
                this.setState({
                    error: null,
                    checked: [],
                });
                document.location.reload();
            }
        }, {
            method: event.currentTarget.dataset.method,
            type: 'json',
            url: event.currentTarget.dataset.path,
            data,
        });
    }
}

BatchActionsWidget.propTypes = {
    checkboxSelector: PropTypes.string.isRequired,
    mainCheckboxSelector: PropTypes.string.isRequired,
    mainCheckbox: PropTypes.element,
    api: PropTypes.instanceOf(ReqwestApiClient).isRequired,
};
