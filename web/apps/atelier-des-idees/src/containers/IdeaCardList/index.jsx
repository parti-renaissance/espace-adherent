import React from 'react';
import PropTypes from 'prop-types';
import classNames from 'classnames';
import { connect } from 'react-redux';
import { withRouter } from 'react-router-dom';
import queryString from 'query-string';
import { ideaStatus } from '../../constants/api';
import { selectLoadingState } from '../../redux/selectors/loading';
import { selectIdeasWithStatus, selectIdeasMetadata } from '../../redux/selectors/ideas';
import { selectStatic } from '../../redux/selectors/static';
import { selectVisitedIdeas } from '../../redux/selectors/session';
import { fetchIdeas, fetchNextIdeas, voteIdea } from '../../redux/thunk/ideas';
import Button from '../../components/Button';
import IdeaCardList from '../../components/IdeaCardList';
import IdeaFilters from '../../components/IdeaFilters';
import noIdeaImg from '../../img/no-idea-result.svg';
import condensedIcn from '../../img/icn_20px_autosave.svg';
import defaultIcn from '../../img/icn_hourglass.svg';

class IdeaCardListContainer extends React.Component {
    constructor(props) {
        super(props);
        this.state = {
            params: {},
            condensed: false,
        };
        this.onFilterChange = this.onFilterChange.bind(this);
        this.onDisplayModeChange = this.onDisplayModeChange.bind(this);
        this.fetchIdeas = this.fetchIdeas.bind(this);
    }

    fetchIdeas(options) {
        this.props.fetchIdeas(this.state.params, options);
    }

    onFilterChange(filters, options) {
        this.setState({ params: filters }, () => this.fetchIdeas(options));
    }

    onDisplayModeChange(condensed) {
        if (condensed !== this.state.condensed) {
            this.setState({ condensed });
        }
    }

    render() {
        return (
            <React.Fragment>
                <IdeaFilters
                    onFilterChange={this.onFilterChange}
                    onFilterInit={filters => this.onFilterChange(filters, { cancel: false, updateUrl: false })}
                    status={this.props.status}
                    options={this.props.filters}
                    disabled={this.props.isLoading}
                    defaultValues={this.props.defaultFilterValues}
                />
                <div className="idea-card-list__actions">
                    <div className="idea-card-list__actions__display">
                        {[
                            { title: 'Affichage par défaut', condensed: false },
                            { title: 'Affichage condensé', condensed: true },
                        ].map(item => (
                            <button
                                className={classNames('idea-card-list__actions__display__btn', {
                                    'idea-card-list__actions__display__btn--selected':
                                        this.state.condensed === item.condensed,
                                })}
                                data-tip={item.title}
                                data-effect="solid"
                                data-type="light"
                                data-class="idea-card-list__actions__display__btn-tip"
                                onClick={() => this.onDisplayModeChange(item.condensed)}
                                title={item.title}
                            >
                                <img src={item.condensed ? condensedIcn : defaultIcn} />
                            </button>
                        ))}
                    </div>
                </div>
                {this.props.isLoading || this.props.ideas.length ? (
                    <React.Fragment>
                        <IdeaCardList
                            condensed={this.state.condensed}
                            ideas={this.props.ideas}
                            isLoading={this.props.isLoading}
                            mode={this.props.mode}
                            onVoteIdea={this.props.onVoteIdea}
                            readIdeas={this.props.visitedIdeas}
                        />
                        {this.props.withPaging && (
                            <div className="idea-card-list__paging">
                                <Button
                                    label="Plus de propositions"
                                    mode="tertiary"
                                    onClick={() => this.props.onMoreClicked(this.state.params)}
                                />
                            </div>
                        )}
                    </React.Fragment>
                ) : (
                    <div className="idea-card-list__empty">
                        <img className="idea-card-list__empty__img" src={noIdeaImg} />
                        <p>Il n'y a pas de propositions correspondant à votre recherche</p>
                    </div>
                )}
            </React.Fragment>
        );
    }
}

IdeaCardListContainer.defaultProps = {
    onMoreClicked: undefined,
    withPaging: false,
    filters: undefined,
    defaultFilterValues: undefined,
};

IdeaCardListContainer.propTypes = {
    fetchIdeas: PropTypes.func.isRequired,
    onMoreClicked: PropTypes.func,
    status: PropTypes.oneOf(Object.keys(ideaStatus)).isRequired,
    withPaging: PropTypes.bool,
    filters: PropTypes.object,
    defaultFilterValues: PropTypes.object,
};

function mapStateToProps(state, ownProps) {
    const { isFetching } = selectLoadingState(state, 'FETCH_IDEAS', ownProps.status);
    const ideas = selectIdeasWithStatus(state, ownProps.status);
    const visitedIdeas = selectVisitedIdeas(state);
    /* paging data */
    const { current_page, last_page } = selectIdeasMetadata(state);
    // show paging if props says so and is not loading and is not at the end of the list
    const withPaging = ownProps.withPaging && current_page < last_page && !isFetching;
    // filter options
    const { themes, categories, needs } = selectStatic(state);
    return {
        ideas,
        isLoading: isFetching && !ideas.length,
        withPaging,
        filters: {
            themes: themes.map(theme => ({ value: theme.name, label: theme.name })),
            needs: needs.map(need => ({ value: need.name, label: need.name })),
            categories: categories.map(category => ({ value: category.name, label: category.name })),
        },
        defaultFilterValues: queryString.parse(ownProps.location.search),
        visitedIdeas,
    };
}

function mapDispatchToProps(dispatch, ownProps) {
    return {
        fetchIdeas: (params, options = {}) => {
            dispatch(fetchIdeas(ownProps.status, params, { setMode: true, cancel: true, ...options }));
            // update url with query params
            if (false !== options.updateUrl) {
                const { match, history } = ownProps;
                const { path } = match;
                const paramsString = queryString.stringify(params);
                history.replace(`${path}?${paramsString}`);
            }
        },
        onMoreClicked: params => dispatch(fetchNextIdeas(ownProps.status, params)),
        onVoteIdea: (id, vote) => dispatch(voteIdea(id, vote)),
    };
}

export default withRouter(
    connect(
        mapStateToProps,
        mapDispatchToProps
    )(IdeaCardListContainer)
);
