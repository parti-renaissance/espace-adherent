import React, { Component } from 'react';
import { connect } from 'react-redux';
import MediaQuery from 'react-responsive';
import Select from 'react-select';

import { getTurnkeyDetail, getTurnkeyProjects } from '../actions/turnkey-projects';
import TurnkeyProjectDetail from './../components/TurnkeyProjectDetail';
import TurnkeyProjectListItem from './../components/TurnkeyProjectListItem';

const TurnkeyProjectDropdown = ({ projects, active, dispatch }) =>
    <div className="turnkey__project__dropdown">
        <Select
            simpleValue
            searchable={false}
            clearable={false}
            onChange={slug => dispatch(getTurnkeyDetail(slug))}
            value={{ label: active.title, value: active.slug }}
            options={projects.map(p => ({
                label: p.title,
                value: p.slug,
            }))}
        />
    </div>;

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
                <h1>Les projets citoyens clé en main</h1>
                <p className="citizen__blurb">
                    Vous avez envie de vous engager mais vous ne savez pas
                    comment vous y prendre ? <br />
                    Avec les projets clé en main c'est facile : nous vous
                    donnons un mode d'emploi et des outils pour agir.
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
                        <MediaQuery maxWidth={650}>
                            {(project && projects.length) ?
                                <TurnkeyProjectDropdown
                                    projects={projects}
                                    active={project}
                                    dispatch={dispatch}
                                />
                                : null}
                        </MediaQuery>
                        <MediaQuery minWidth={651}>
                            {projects.map((p, i) => (
                                <TurnkeyProjectListItem
                                    onClick={() => dispatch(getTurnkeyDetail(p.slug))}
                                    key={i}
                                    isActive={project && p.slug === project.slug}
                                    {...p}
                                />
                            ))}
                        </MediaQuery>
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
