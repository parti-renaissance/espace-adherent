import React from 'react';
import PropTypes from 'prop-types';
import Button from '../../Button';
import Input from '../../Input';
import Switch from '../../Switch';

class MyNicknameModal extends React.Component {
    constructor(props) {
        super(props);
        this.state = {
            nickname: props.defaultValues.nickname || '',
            useNickname: props.defaultValues.useNickname || false,
        };
        this.handleSubmit = this.handleSubmit.bind(this);
    }

    handleSubmit() {
        this.props.onSubmit(this.state.nickname, this.state.useNickname);
    }

    render() {
        return (
            <div className="my-nickname-modal">
                <h2 className="my-nickname-modal__title">Mon pseudo</h2>
                <p className="my-nickname-modal__description">
                    Vous pouvez choisir un pseudonyme si vous souhaitez rester anonyme lors de la publication d'idée ou
                    de commentaire.
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
                        id="nickname"
                        className="my-nickname-modal__form__input"
                        inputClassName="my-nickname-modal__form__field"
                        value={this.state.nickname}
                        onChange={value => this.setState({ nickname: value })}
                        placeholder="Entrez votre pseudo"
                        maxLength={25}
                    />
                    <div className="my-nickname-modal__form__use-nickname">
                        <span className="my-nickname-modal__form__label">Utiliser le pseudo</span>
                        <Switch
                            defaultChecked={this.state.useNickname}
                            onChange={() => this.setState(prevState => ({ useNickname: !prevState.useNickname }))}
                        />
                    </div>
                    <Button
                        className="my-nickname-modal__form__button"
                        type="submit"
                        label="Enregistrer"
                        isLoading={this.props.isSubmitting}
                    />
                </form>
            </div>
        );
    }
}

MyNicknameModal.defaultProps = {
    defaultValues: {},
    isSubmitting: false,
};

MyNicknameModal.propTypes = {
    defaultValues: PropTypes.shape({
        nickname: PropTypes.string,
        useNickname: PropTypes.bool,
    }),
    isSubmitting: PropTypes.bool,
    onSubmit: PropTypes.func.isRequired,
};

export default MyNicknameModal;
