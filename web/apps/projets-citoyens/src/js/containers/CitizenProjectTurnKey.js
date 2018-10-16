import React, { Component } from 'react';
import { connect } from 'react-redux';

import { getTurnkeyDetail, getTurnkeyProjects } from '../actions/turnkey-projects';
import TurnkeyProjectDetail from './../components/TurnkeyProjectDetail';
import TurnkeyProjectListItem from './../components/TurnkeyProjectListItem';

const formDetail = (slug, project) => (
    <div className="turnkey__project__footer">
        <a
            href={`/espace-adherent/creer-mon-projet-citoyen/${slug}`}
            className="turnkey__cta turnkey__cta__green"
            target="_blank"
            rel="noopener noreferrer"
        >
            Je lance ce projet
        </a>

        <span>ou</span>

        <a href="/espace-adherent/creer-mon-projet-citoyen"
           target="_blank"
           className="link link--green"
           rel="noopener noreferrer">
            Je propose un autre projet
        </a>
    </div>
);

class CitizenProjectTurnKey extends Component {
    componentDidMount() {
        window.scrollTo(0, 0);
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
            dispatch,
            detail: { project /* , loading: detailLoading */ },
            all: { projects /* , loading: allLoading */ },
        } = this.props;
        return (
            <div className="citizen__wrapper">
                <h2>Les projets citoyens clés en main</h2>
                <p className="citizen__blurb">
                    Vous avez envie de vous engager mais vous ne savez pas comment vous y prendre ? <br />Avec les
                    projets clés en mains c'est facile : nous vous donnons un mode d'emploi et des outils pour agir.
                </p>

                <div className="turnkey__project__content">
                    <div className="turnkey__project__detail">
                        {project ? (
                            <TurnkeyProjectDetail
                                video_border="green"
                                textSize="100%"
                                border="light"
                                swap={true}
                                {...project}
                                renderCTA={() => formDetail(project.slug)}
                            />
                        ) : null}
                    </div>
                    <div className="turnkey__project__list">
                        {projects.map((p, i) => (
                            <TurnkeyProjectListItem
                                onClick={() => dispatch(getTurnkeyDetail(p.slug))}
                                key={i}
                                isActive={project && p.slug === project.slug}
                                {...p}
                            />
                        ))}
                    </div>
                </div>
            </div>
        );
    }
}

const mapStateToProps = state => ({
    all: state.turnkey.all,
    detail: state.turnkey.detail,
});

export default connect(mapStateToProps)(CitizenProjectTurnKey);
