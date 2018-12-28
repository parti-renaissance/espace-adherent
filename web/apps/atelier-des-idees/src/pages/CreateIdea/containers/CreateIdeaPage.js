import { connect } from 'react-redux';
import CreateIdea from '../index';

function mapStateToProps(state) {
    // const currentUser = getCurrentUser(state)
    // const metadata = { authorName: currentUser.name, createdAt: new Date().toLocaleDateString() };
    // TODO: uncomment above and remove below
    const metadata = { authorName: 'Killian Mbappé', createdAt: new Date().toLocaleDateString() };
    return { isAuthor: true, metadata };
}

function mapDispatchToProps() {
    // TODO: replace with actual action creators
    return {
        onBackClicked: () => alert('Retour'),
        onPublichClicked: () => alert('Publier'),
        onDeleteClicked: () => alert('Supprimer'),
        onSaveClicked: () => alert('Enregistrer'),
    };
}

export default connect(
    mapStateToProps,
    mapDispatchToProps
)(CreateIdea);
