import React, { Component } from 'react';
import { connect } from 'react-redux';

import { getCitizenProjects } from '../actions/citizen-projects';
import { getPinned } from '../actions/turnkey-projects';

class CitizenProject extends Component {
    componentDidMount() {
        window.scrollTo(0, 0);
        this.props.dispatch(getCitizenProjects());
        this.props.dispatch(getPinned());
    }

    render() {
        return (
            <div className="citizen__wrapper">
                <h1>Plus de 650 projets citoyens déjà lancés !</h1>
                <p className="citizen__blurb">
                    Parce que transformer la France demande l'engagement de tous, des citoyens partout dans le pays se
                    regroupent pour agir dans leurs territoires. Ils l'ont fait : pourquoi pas vous ?
                </p>
                <div className="citizen__help">
                    <div className="citizen__help__list">
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
                    <div className="citizen__help__video turnkey__video--yellow">
                        <iframe
                            title="Home_video"
                            width="560"
                            height="315"
                            src={`https://www.youtube.com/embed/XFOp8jqBTnE?rel=0&amp;controls=0&amp;showinfo=0`}
                            frameBorder="0"
                            allow="autoplay; encrypted-media"
                            allowFullScreen
                        />
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

                <div className="turnkey__home turnkey__project__pinned">
                    <div className="turnkey__home__header">
                        <h3>
                            Vous avez envie de vous engager ?<br/>
                            Avec <em>La République en Marche</em>, c'est facile !
                        </h3>
                    </div>
                    <div className="turnkey__project__footer turnkey__video">
                        <a
                            href="/projets-citoyens/cle-en-main"
                            className="turnkey__cta turnkey__cta__yellow"
                        >
                            Voir les projets clé en main
                        </a>

                        <span>ou</span>

                        <a href="/projets-citoyens/recherche"
                           className="link link--yellow">
                            Voir tous les projets citoyens
                        </a>
                    </div>
                </div>

            </div>
        );
    }
}

const mapStateToProps = state => ({
    count: state.citizen.count,
    pinned: state.turnkey.pinned,
});

export default connect(mapStateToProps)(CitizenProject);
