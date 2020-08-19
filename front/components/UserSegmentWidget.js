import React, { PropTypes } from 'react';
import Modal from './Modal';
import Loader from './Loader';
import successImage from './../../public/images/icons/icn_success.svg';
import ReqwestApiClient from '../services/api/ReqwestApiClient';

const STORAGE_KEY = 'su';

export default class UserSegmentManager extends React.Component {

    constructor(props) {
        super(props);

        this.segmentType = props.segmentType;
        this.api = props.api;

        this.checkboxSelector = props.checkboxSelector;
        this.checkboxes = findAll(document, props.checkboxSelector);

        if (0 === this.checkboxes.length) {
            throw new Error('Checkbox list is empty');
        }

        this.table = this.checkboxes[0].closest('table');

        this.mainCheckbox = props.mainCheckbox;
        this.countMembers = props.countMembers || 0;

        let item = localStorage.getItem(this.getStorageKey());

        if (item) {
            item = item.split(',');
        } else {
            item = [];
        }

        this.state = {
            checked: item,
            displayModal: false,
            segmentName: '',
            error: null,
            processing: false,
            isSuccess: false,
        };

        this.getModalContentCallback = this.getModalContentCallback.bind(this);

        this.handleSaveSegmentClick = this.handleSaveSegmentClick.bind(this);
        this.handleSegmentNameChange = this.handleSegmentNameChange.bind(this);
        this.handleOpenModal = this.handleOpenModal.bind(this);
        this.handleCloseModal = this.handleCloseModal.bind(this);
        this.handleResetClick = this.handleResetClick.bind(this);
        this.handleTableUpdate = this.handleTableUpdate.bind(this);
        this.handleCheckboxChange = this.handleCheckboxChange.bind(this);
        this.handleMainCheckboxChange = this.handleMainCheckboxChange.bind(this);
        this.updateCheckboxState = this.updateCheckboxState.bind(this);
        this.bindCheckboxListener = this.bindCheckboxListener.bind(this);
    }

    componentDidMount() {
        if (this.mainCheckbox) {
            on(this.mainCheckbox, 'change', this.handleMainCheckboxChange);
        }

        if (this.table) {
            on(this.table, 'table_update', this.handleTableUpdate);
        }

        this.bindCheckboxListener();
    }

    componentDidUpdate() {
        localStorage.setItem(this.getStorageKey(), this.state.checked);
    }

    render() {
        const length = this.state.checked.length;

        if (this.state.displayModal) {
            return <Modal contentCallback={this.getModalContentCallback} closeCallback={this.handleCloseModal} />;
        }

        return (
            <div className="b__nudge--bottom-large">
                <a href="#"
                   className={`btn-secondary btn-secondary--blue ${1 > length ? 'btn-secondary--disabled' : ''}`}
                   onClick={this.handleOpenModal}
                >
                    Créer ma liste
                    ({0 < length ? <span>{this.getCreateButtonCounter(length)}</span> : '0'})
                </a>

                {0 < length ?
                    <a href="#" className="btn-secondary btn-secondary--black b__nudge--left-small"
                       onClick={this.handleResetClick}>Effacer la sélection</a> : ''}
            </div>
        );
    }

    getModalContentCallback() {
        if (this.state.processing) {
            return (
                <div style={{ width: '44px', margin: '0 auto' }}>
                    <Loader />
                </div>
            );
        }

        if (this.state.isSuccess) {
            return (
                <div className="text--center">
                    <img className="modal-content__success" src={successImage} alt={'success image'}/>
                    <div className="text--bold text--default-large text--center b__nudge--top">
                        Votre liste a bien été créée !
                    </div>
                    <p>Retrouvez-la après l’étape de rédaction de votre message.</p>
                </div>
            );
        }

        return (
            <div>
                <div className="text--bold text--default-large">Créer une liste de diffusion</div>
                <p className="b__nudge--top-15 b__nudge--bottom-large text--dark">
                Créez des listes de diffusions pour envoyer des messages à un groupe de contacts.<br />
                Exemple : Adhérents de moins de 35 ans qui suivent l'écologie
                    <br /><br />
                    <strong className="text--blue--dark">{this.state.checked.length}</strong> contacts
                </p>

                <div className="form__row">
                    <label className="form__label">Nom de la liste</label>
                    <input type="text"
                           name="list_name"
                           className="form__field"
                           value={this.state.segmentName}
                           required={true}
                           onChange={this.handleSegmentNameChange}
                           placeholder="Entrez un nom pour cette liste"
                    />
                    {this.state.error ? <p className={'text--error b__nudge--top-10 b__nudge--bottom-medium'}>
                        {this.state.error}</p> : ''}
                </div>
                <button className="btn btn--blue btn--large-and-full form btn"
                        onClick={this.handleSaveSegmentClick}>OK</button>
            </div>
        );
    }

    handleTableUpdate() {
        this.checkboxes = findAll(document, this.checkboxSelector);

        this.bindCheckboxListener();
    }

    handleSaveSegmentClick() {
        if (!this.state.segmentName) {
            this.setState({ error: 'Le nom est invalide' });
            return;
        }

        this.setState({
            processing: true,
            error: null,
        });

        this.api.createUserSegmentList({
            segmentType: this.segmentType,
            label: this.state.segmentName,
            member_ids: this.state.checked,
        }, (data) => {
            if (null === data) {
                this.setState({
                    error: 'Une erreur est survenue',
                    processing: false,
                });
            } else {
                this.setState({
                    processing: false,
                    isSuccess: true,
                });
            }
        });
    }

    handleSegmentNameChange(event) {
        this.setState({ segmentName: event.target.value });
    }

    handleCloseModal() {
        this.resetState(!this.state.isSuccess);
    }

    handleOpenModal(event) {
        event.preventDefault();

        if (this.state.checked.length) {
            this.setState({ displayModal: true });
        }
    }

    handleResetClick(event) {
        event.preventDefault();

        this.resetChecked();
    }

    handleCheckboxChange(event) {
        const target = event.currentTarget;

        this.updateCheckboxState([target.value], target.checked);
    }

    resetState(partial) {
        if (!partial) {
            this.resetChecked();
        }

        this.setState({
            displayModal: false,
            segmentName: '',
            error: null,
            processing: false,
            isSuccess: false,
        });
    }

    resetChecked() {
        this.setState({ checked: [] });

        this.checkboxes.forEach((element) => {
            element.checked = false;
        });

        if (this.mainCheckbox) {
            this.mainCheckbox.checked = false;
        }
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

    getCreateButtonCounter(count) {
        if (this.countMembers) {
            return `${count} adhérent${1 < count ? 's' : ''} sélectionné${1 < count ? 's' : ''} sur ${
                this.countMembers
            } au total`;
        }

        return count;
    }

    getStorageKey() {
        return `_${STORAGE_KEY}_${this.segmentType}`;
    }

    bindCheckboxListener() {
        this.checkboxes.forEach((element) => {
            on(element, 'change', this.handleCheckboxChange);

            if (-1 !== this.state.checked.indexOf(element.value)) {
                element.checked = true;
            }
        });
    }
}

UserSegmentManager.propTypes = {
    segmentType: PropTypes.string.isRequired,
    api: PropTypes.instanceOf(ReqwestApiClient).isRequired,
    checkboxSelector: PropTypes.string.isRequired,
    mainCheckbox: PropTypes.element,
    countMembers: PropTypes.number,
};
