import React, { PropTypes } from 'react';
import Loader from './Loader';

const STORAGE_KEY = 'suld';
const STATUS_ALL = 'all';
const STATUS_NONE = 'none';
const STATUS_PARTIAL = 'partial';
const STATUS_CLASS = {
    all: 'checkbox checkbox--checked',
    none: 'checkbox',
    partial: 'checkbox checkbox--indeterminate',
};

export default class UserListDefinitionWidget extends React.Component {

    constructor(props) {
        super(props);

        this.type = props.type;
        this.memberType = props.memberType;
        this.api = props.api;
        this.postApplyCallback = props.postApplyCallback || null;

        this.checkboxSelector = props.checkboxSelector;
        this.checkboxes = findAll(document, props.checkboxSelector);

        if (0 === this.checkboxes.length) {
            throw new Error('Checkbox list is empty');
        }

        this.table = this.checkboxes[0].closest('table');

        this.mainCheckboxSelector = props.mainCheckboxSelector;

        let item = sessionStorage.getItem(this.getStorageKey());

        if (item) {
            item = item.split(',');
        } else {
            item = [];
        }

        this.state = {
            checked: item,
            error: null,
            processing: false,
            userListDefinitions: [],
            members: [],
            membersDataLoaded: false,
            displayList: false,
            canApply: false,
            lastChecked: null,
        };

        this.handleResetClick = this.handleResetClick.bind(this);
        this.handleCheckboxChange = this.handleCheckboxChange.bind(this);
        this.handleTableUpdate = this.handleTableUpdate.bind(this);
        this.handleMainCheckboxChange = this.handleMainCheckboxChange.bind(this);
        this.updateCheckboxState = this.updateCheckboxState.bind(this);
        this.bindCheckboxListener = this.bindCheckboxListener.bind(this);
        this.changeListStatus = this.changeListStatus.bind(this);
        this.checkIfCanApply = this.checkIfCanApply.bind(this);
        this.updateMainCheckbox = this.updateMainCheckbox.bind(this);
        this.getUserListDefinitionsForType = this.getUserListDefinitionsForType.bind(this);
        this.updateUserListDefinitions = this.updateUserListDefinitions.bind(this);
        this.calculateListStatus = this.calculateListStatus.bind(this);
        this.prepareUserListDefinitionMembers = this.prepareUserListDefinitionMembers.bind(this);
        this.saveUserListDefinitionMembers = this.saveUserListDefinitionMembers.bind(this);
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

    componentDidUpdate() {
        sessionStorage.setItem(this.getStorageKey(), this.state.checked);
    }

    renderLoader() {
        return (
            <div style={{ width: '44px', margin: '0 auto' }}>
                <Loader />
            </div>
        );
    }

    render() {
        const length = this.state.checked.length;

        return (
            <div className="l__row">
                <div className="pst--relative">
                    {0 < length ?
                        <div className={
                            `btn-secondary btn-secondary--blue ${1 > length ? 'btn-secondary--disabled' 
                        : ''}`}
                        onClick={this.getUserListDefinitionsForType}>
                            ({length}) Ajouter un label
                        </div>
                    : ''}

                    { this.state.processing ? <div className="label-list--loader">{this.renderLoader()}</div> :
                        <div style={{ display: this.state.displayList && 0 < length ? 'block' : 'none' }}
                             className="label-list">
                            {this.state.membersDataLoaded
                                ? this.state.userListDefinitions.map((userListDefinition, index) => (
                                    <div key={index} id={userListDefinition.code}
                                         className="label"
                                         onClick={this.changeListStatus}>
                                        <i className={STATUS_CLASS[userListDefinition.newStatus]} />
                                        {userListDefinition.label}
                                    </div>))
                                : ''}
                            <a style={{ display: this.state.canApply ? 'block' : 'none' }}
                               href="#"
                               className="text--blue--dark link--no-decor apply-btn"
                               onClick={this.saveUserListDefinitionMembers}>Appliquer</a>
                        </div>
                    }
                </div>

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
        );
    }

    handleResetClick(event) {
        event.preventDefault();

        this.resetChecked();
    }

    handleCheckboxChange(event) {
        const target = event.currentTarget;

        if (!this.state.lastChecked) {
            this.setState({ lastChecked: target });
        }

        if (event.shiftKey) {
            const start = $(this.checkboxes).index(target);
            const end = $(this.checkboxes).index(this.state.lastChecked);

            $(this.checkboxes)
                .slice(Math.min(start, end), Math.max(start, end) + 1)
                .prop('checked', target.checked);
            $(this.checkboxes)
                .slice(Math.min(start, end), Math.max(start, end) + 1)
                .each((key, element) => {
                    this.updateCheckboxState([element.value], target.checked);
                });
        }

        this.updateMainCheckbox();

        this.setState({ lastChecked: target });

        this.updateCheckboxState([target.value], target.checked);
        this.resetAndHideUserListDefinitions();
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
        this.resetAndHideUserListDefinitions();
    }

    updateCheckboxState(changed, isChecked) {
        if (isChecked) {
            this.setState((state) => {
                const checked = [...new Set(state.checked.concat(changed))];

                return { checked };
            });
        } else {
            this.setState((state) => {
                const checked = state.checked.filter(value => -1 === changed.indexOf(value));

                return { checked };
            });
        }
    }

    bindCheckboxListener() {
        this.checkboxes.forEach((element) => {
            on(element, 'click', this.handleCheckboxChange);

            if (-1 !== this.state.checked.indexOf(element.value)) {
                element.checked = true;
            }
        });
        this.updateMainCheckbox();
    }

    changeListStatus(event) {
        const listDefinitions = this.state.userListDefinitions;

        const objIndex = listDefinitions.findIndex((uld => uld.code === event.currentTarget.id));
        const hasBeenPartial = listDefinitions[objIndex].wasPartial;

        if (STATUS_ALL === listDefinitions[objIndex].newStatus) {
            listDefinitions[objIndex].newStatus = STATUS_NONE;
        } else if (STATUS_NONE === listDefinitions[objIndex].newStatus) {
            if (hasBeenPartial) {
                listDefinitions[objIndex].newStatus = STATUS_PARTIAL;
            } else {
                listDefinitions[objIndex].newStatus = STATUS_ALL;
            }
        } else if (STATUS_PARTIAL === listDefinitions[objIndex].newStatus) {
            listDefinitions[objIndex].newStatus = STATUS_ALL;
            listDefinitions[objIndex].wasPartial = true;
        }

        this.setState({
            userListDefinitions: listDefinitions,
        });

        this.checkIfCanApply();
    }

    checkIfCanApply() {
        this.setState({
            canApply: this.state.userListDefinitions.some(uld => uld.newStatus !== uld.status),
        });
    }

    updateMainCheckbox() {
        const checkboxes = Array.from(this.checkboxes);
        this.mainCheckbox.indeterminate = checkboxes.some(chk => true === $(chk).prop('checked'))
            && checkboxes.some(chk => false === $(chk).prop('checked'));
    }

    getUserListDefinitionsForType() {
        if (1 > this.state.checked.length) {
            return;
        }

        if (!this.type) {
            this.setState({ error: 'Pas de UserListDefinition type' });
            return;
        }

        if (this.state.displayList) {
            this.setState({
                displayList: false,
            });

            return;
        }

        this.setState({
            processing: true,
            error: null,
        });

        this.api.getUserListDefinitionsForType(this.memberType, this.type, {
            ids: this.state.checked,
        }, (data) => {
            if (null === data || !Array.isArray(data)) {
                this.setState({
                    error: 'Une erreur est survenue',
                    processing: false,
                    userListDefinitions: [],
                    membersDataLoaded: false,
                    displayList: false,
                    canApply: false,
                });
            } else {
                this.calculateListStatus(data);
                this.setState({
                    error: null,
                    processing: false,
                    userListDefinitions: data,
                    membersDataLoaded: true,
                    displayList: true,
                    canApply: false,
                });
            }
        });
    }

    updateUserListDefinitions(event) {
        if (1 > this.state.checked.length) {
            this.setState({ display: false });
        } else {
            this.setState({ displayList: !this.state.displayList });
        }
    }

    calculateListStatus(userListDefinitions) {
        userListDefinitions.forEach((userListDefinition) => {
            if ('undefined' === typeof userListDefinition.ids || 1 > userListDefinition.ids) {
                userListDefinition.status = STATUS_NONE;
                userListDefinition.newStatus = STATUS_NONE;
            } else {
                const every = this.state.checked.every(r => userListDefinition.ids.includes(r));

                if (every) {
                    userListDefinition.status = STATUS_ALL;
                    userListDefinition.newStatus = STATUS_ALL;
                } else {
                    userListDefinition.status = STATUS_PARTIAL;
                    userListDefinition.newStatus = STATUS_PARTIAL;
                }
            }
        });
    }

    prepareUserListDefinitionMembers() {
        const members = {};

        this.state.checked.forEach((id) => {
            members[id] = {
                member_of: [],
                not_member_of: [],
            };

            this.state.userListDefinitions.forEach((userListDefinition) => {
                if (userListDefinition.status !== userListDefinition.newStatus) {
                    switch (userListDefinition.newStatus) {
                    case STATUS_ALL:
                        members[id].member_of.push(userListDefinition.id);
                        break;
                    case STATUS_NONE:
                        members[id].not_member_of.push(userListDefinition.id);
                        break;
                    default:
                        return;
                    }
                }
            });
        });

        return members;
    }

    saveUserListDefinitionMembers(event) {
        event.preventDefault();

        this.setState({
            processing: true,
            error: null,
        });

        const uldMembers = this.prepareUserListDefinitionMembers();
        this.api.saveUserListDefinitionMembers(this.memberType, this.type, {
            members: uldMembers,
        }, (data) => {
            if ('' !== data) {
                this.setState({
                    error: 'Une erreur est survenue',
                    processing: false,
                });
            } else {
                this.setState({
                    processing: false,
                });

                if (null !== this.postApplyCallback) {
                    Object.keys(uldMembers).forEach((membreId) => {
                        const tdLabels = $(this.table)
                            .find(`:input[value="${membreId}"]`).parents('tr').find('td.table-labels');
                        $(tdLabels).html(
                            this.postApplyCallback(
                                this.state.userListDefinitions.filter(
                                    uld => uld.newStatus === STATUS_ALL
                                            || (uld.status === STATUS_PARTIAL
                                                && uld.ids && uld.ids.includes(membreId))
                                )
                            )
                        );
                    });
                }
            }
            this.resetAndHideUserListDefinitions();
        });
    }

    resetAndHideUserListDefinitions() {
        this.setState({
            displayList: false,
            userListDefinitions: [],
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

    getStorageKey() {
        return `_${STORAGE_KEY}_${this.type}`;
    }
}

UserListDefinitionWidget.propsType = {
    memberType: PropTypes.string.isRequired,
    type: PropTypes.string.isRequired,
    api: PropTypes.object.isRequired,
    checkboxSelector: PropTypes.string.isRequired,
    mainCheckboxSelector: PropTypes.string.isRequired,
    mainCheckbox: PropTypes.element,
    postApplyCallback: PropTypes.func,
};
