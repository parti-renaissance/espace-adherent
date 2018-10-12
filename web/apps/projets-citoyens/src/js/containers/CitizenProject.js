import React, { Component } from 'react';
import { connect } from 'react-redux';
import { Link } from 'react-router-dom';

import { getCitizenProjects } from '../actions/citizen-projects';
import { getPinned } from '../actions/turnkey-projects';
import TurnkeyProjectDetail from './../components/TurnkeyProjectDetail';

const ViewTurnkeys = () => (
    <Link to="/projets-citoyens/decouvrir" className="turnkey__cta turnkey__cta__yellow">
        Voir tous les projets clés en main
    </Link>
);

class CitizenProject extends Component {
    componentDidMount() {
        window.scrollTo(0, 0);
        this.props.dispatch(getCitizenProjects());
        this.props.dispatch(getPinned());
    }

    render() {
        const {
            // count,
            pinned: { /* loading, */ project },
        } = this.props;
        return (
            <div className="citizen__wrapper">
                <h2 className="">Déjà 500 projets citoyens lancés !</h2>
                <p className="citizen__blurb">
                    Parce que transformer la France demande l'engagement de tous, des citoyens partout dans le pays se
                    regroupent pour agir dans leurs territoires. Ils l'ont fait : pourquoi pas vous ?
                </p>
                <div className="citizen__helplist">
                    <h3 className="citizen__helplist-header">Un projet citoyen c'est&nbsp;quoi&nbsp;?</h3>
                    <div>
                        <span className="number">1</span>
                        <p>Un projet local porté par des citoyens, en lien avec les acteurs du territoire</p>
                    </div>
                    <div>
                        <span className="number">2</span>
                        <p>Un projet destiné à changer concrètement la vie des habitants</p>
                    </div>
                    <div>
                        <span className="number">3</span>
                        <p>Un projet ouvert à tous, marcheurs ou non</p>
                    </div>
                </div>

                <div className="citizen__more-link">
                    <a
                        href="https://storage.googleapis.com/en-marche-prod/documents/projets-citoyens/La%20Charte%20des%20Projets%20Citoyens.pdf"
                        target="_blank"
                        rel="noopener noreferrer"
                        className="simple--link"
                    >
                        En savoir plus sur la Charte des Projets Citoyens
                    </a>
                </div>

                {project && (
                    <TurnkeyProjectDetail
                        video_border="yellow"
                        border="light"
                        {...project}
                        is_favorite={false}
                        renderCTA={ViewTurnkeys}
                    />
                )}
            </div>
        );
    }
}

const mapStateToProps = state => ({
    count: state.citizen.count,
    pinned: state.turnkey.pinned,
});

export default connect(mapStateToProps)(CitizenProject);
