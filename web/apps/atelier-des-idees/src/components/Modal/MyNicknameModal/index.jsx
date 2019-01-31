import React from 'react';
import PropTypes from 'prop-types';
import Button from '../../Button';

class MyNicknameModal extends React.Component {
    constructor(props) {
        super(props);
        this.state = {
            nickname: '',
        };
        this.handleSubmit = this.handleSubmit.bind(this);
    }

    handleSubmit() {
        this.props.onSubmit(this.state.nickname);
    }

    render() {
        return (
            <form
                className="my-nickname-modal"
                onSubmit={(e) => {
                    e.preventDefault();
                    this.handleSubmit();
                }}
            >
                <h2 className="my-nickname-modal__title">Mon pseudo</h2>
                <p className="my-nickname-modal__description">
                    Vous pouvez choisir un pseudonyme si vous souhaitez rester anonyme lors de la publication d'idée ou
                    de commentaire.
                </p>
                <label htmlFor="nickname">Choix du pseudo</label>
                <input
                    id="nickname"
                    value={this.state.nickname}
                    onChange={e => this.setState({ nickname: e.target.value })}
                />
                <Button type="submit" label="Enregistrer" />
            </form>
        );
    }
}

MyNicknameModal.propTypes = {
    onSubmit: PropTypes.func.isRequired,
};

export default MyNicknameModal;
