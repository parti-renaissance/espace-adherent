import React from 'react';
import PropTypes from 'prop-types';
import Button from '../../Button';
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
                    <input
                        id="nickname"
                        className="my-nickname-modal__form__input"
                        value={this.state.nickname}
                        onChange={e => this.setState({ nickname: e.target.value })}
                        placeholder="Entrez votre pseudo"
                    />
                    <div className="my-nickname-modal__form__use-nickname">
                        <span className="my-nickname-modal__form__label">Utiliser le pseudo</span>
                        <Switch
                            defaultChecked={this.state.useNickname}
                            onChange={() => this.setState(prevState => ({ useNickname: !prevState.useNickname }))}
                        />
                    </div>
                    <Button className="my-nickname-modal__form__button" type="submit" label="Enregistrer" />
                </form>
            </div>
        );
    }
}

MyNicknameModal.defaultProps = {
    defaultValues: {},
};

MyNicknameModal.propTypes = {
    defaultValues: PropTypes.shape({
        nickname: PropTypes.string,
        useNickname: PropTypes.bool,
    }),
    onSubmit: PropTypes.func.isRequired,
};

export default MyNicknameModal;
