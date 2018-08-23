import React, { Component } from 'react';
import { connect } from 'react-redux';

import { getTurnkeyDetail, getTurnkeyProjects } from '../actions/turnkey-projects';
import TurnkeyProjectDetail from './../components/TurnkeyProjectDetail';
import TurnkeyProjectListItem from './../components/TurnkeyProjectListItem';

class CitizenProjectTurnKey extends Component {
    componentDidMount() {
        this.props.dispatch(getTurnkeyProjects());
    }

    componentDidUpdate(prevProps) {
        const { projects: prevAll } = prevProps.all;
        const { projects: nowAll } = this.props.all;
        if (prevAll.length !== nowAll.length) {
            const [{ slug }] = this.props.all.projects;
            this.props.dispatch(getTurnkeyDetail(slug));
        }
    }

    render() {
        const {
            detail: { project /* , loading: detailLoading */ },
            all: { projects /* , loading: allLoading */ } } = this.props;
        return (
            <div className="citizen__wrapper">
                <h2>Les projets citoyens faciles à lancer</h2>
                <p>
                    Voici les projets faciles à lancer qui ont déjà été lancés avec succès dans de nombreuses villes de
                    France. <br />Choisissez-en un et lancez le facilement près de chez vous !
                </p>

                <div className="turnkey__project__content">
                    <div className="turnkey__project__detail">
                        {project ?
                            <TurnkeyProjectDetail
                                border="light"
                                video_id={project.video_id}
                                title={project.title}
                                subtitle={project.subtitle}
                                description={project.description}
                                cta_content="Je soumets une demande de création"
                                cta_border="green"
                            />
                            : null}
                    </div>
                    <div className="turnkey__project__list">
                        {projects.map((p, i) => <TurnkeyProjectListItem
                            key={i}
                            category={p.category}
                            title={p.title}
                            subtitle={p.subtitle}
                        />)}
                    </div>
                </div>
            </div>
        );
    }
}

const mapStateToProps = state => ({
    all: state.citizen_project.turnkey.all,
    detail: state.citizen_project.turnkey.detail,
});

export default connect(mapStateToProps)(CitizenProjectTurnKey);
