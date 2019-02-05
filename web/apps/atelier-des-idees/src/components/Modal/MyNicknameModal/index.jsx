import React from 'react';
import PropTypes from 'prop-types';
import Button from '../../Button';
import Input from '../../Input';
import Switch from '../../Switch';
import SuccessModal from '../SuccessModal';
import ErrorModal from '../ErrorModal';

class MyNicknameModal extends React.Component {
    constructor(props) {
        super(props);
        this.state = {
            nickname: props.defaultValues.nickname || '',
            useNickname: props.defaultValues.useNickname || false,
            error: '',
        };
        this.emptyMsg = 'Veuillez renseigner un pseudonyme';
        // bindings
        this.handleSubmit = this.handleSubmit.bind(this);
    }

    handleSubmit() {
        if (this.state.nickname.length) {
            this.props.onSubmit(this.state.nickname, this.state.useNickname);
        } else {
            this.setState({ error: this.emptyMsg });
        }
    }

    componentDidUpdate(prevProps) {
        if (prevProps.error !== this.props.error) {
            this.setState({ error: this.props.error });
        }
    }

    render() {
        const hasSubmit = this.props.isSubmitError || this.props.isSubmitSuccess;
        return (
            <div className="my-nickname-modal">
                {!hasSubmit && (
                    <React.Fragment>
                        <h2 className="my-nickname-modal__title">Mon pseudo</h2>
                        <p className="my-nickname-modal__description">
                            Vous pouvez choisir un pseudonyme si vous souhaitez rester anonyme lors de la publication
                            de propositions ou de commentaires sur l'Atelier des idées.
                        </p>
                        <form
                            className="my-nickname-modal__form"
                            onSubmit={(e) => {
                                e.preventDefault();
                                this.handleSubmit();
                            }}
                        >
                            <label className="my-nickname-modal__form__label" htmlFor="nickname">
                                Choix du pseudo
                            </label>
                            <Input
                                className="my-nickname-modal__form__input"
                                error={this.state.error}
                                id="nickname"
                                inputClassName="my-nickname-modal__form__field"
                                maxLength={25}
                                onChange={value =>
                                    this.setState({
                                        nickname: value,
                                        error: !value.length ? this.emptyMsg : '',
                                    })
                                }
                                placeholder="Entrez votre pseudo"
                                subtitle="N’utilisez que des lettres, chiffres et les caractères _ ou -"
                                value={this.state.nickname}
                            />
                            <div className="my-nickname-modal__form__use-nickname">
                                <span className="my-nickname-modal__form__label">Utiliser le pseudo</span>
                                <Switch
                                    defaultChecked={this.state.useNickname}
                                    onChange={() =>
                                        this.setState(prevState => ({ useNickname: !prevState.useNickname }))
                                    }
                                />
                            </div>
                            <Button
                                className="my-nickname-modal__form__button"
                                type="submit"
                                label="Enregistrer"
                                isLoading={this.props.isSubmitting}
                            />
                        </form>
                    </React.Fragment>
                )}
                {hasSubmit &&
                    (this.props.isSubmitSuccess ? (
                        <SuccessModal text="Vos informations ont bien été enregistrées" />
                    ) : (
                        <ErrorModal submitAgain={this.handleSubmit} />
                    ))}
            </div>
        );
    }
}

MyNicknameModal.defaultProps = {
    defaultValues: {},
    error: '',
    isSubmitError: false,
    isSubmitSuccess: false,
    isSubmitting: false,
};

MyNicknameModal.propTypes = {
    defaultValues: PropTypes.shape({
        nickname: PropTypes.string,
        useNickname: PropTypes.bool,
    }),
    error: PropTypes.string,
    isSubmitError: PropTypes.bool,
    isSubmitSuccess: PropTypes.bool,
    isSubmitting: PropTypes.bool,
    onSubmit: PropTypes.func.isRequired,
};

export default MyNicknameModal;
