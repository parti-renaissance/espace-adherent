import React from 'react';
import PropTypes from 'prop-types';
import { connect } from 'react-redux';
import { ideaStatus } from '../../constants/api';
import { selectLoadingState } from '../../redux/selectors/loading';
import { selectIdeasWithStatus, selectIdeasMetadata } from '../../redux/selectors/ideas';
import { selectStatic } from '../../redux/selectors/static';
import { fetchIdeas, fetchNextIdeas, voteIdea } from '../../redux/thunk/ideas';
import Button from '../../components/Button';
import IdeaCardList from '../../components/IdeaCardList';
import IdeaFilters from '../../components/IdeaFilters';

class IdeaCardListContainer extends React.Component {
    constructor(props) {
        super(props);
        this.state = {
            params: {},
        };
        this.onFilterChange = this.onFilterChange.bind(this);
    }

    fetchIdeas() {
        this.props.fetchIdeas(this.state.params);
    }

    onFilterChange(filters) {
        this.setState({ params: filters }, () => this.fetchIdeas());
    }

    render() {
        return (
            <React.Fragment>
                <IdeaFilters
                    onFilterChange={this.onFilterChange}
                    status={this.props.status}
                    options={this.props.filters}
                    disabled={this.props.isLoading}
                />
                {this.props.isLoading || this.props.ideas.length ? (
                    <React.Fragment>
                        <IdeaCardList
                            ideas={this.props.ideas}
                            isLoading={this.props.isLoading}
                            mode={this.props.mode}
                            onVoteIdea={this.props.onVoteIdea}
                        />
                        {this.props.withPaging && (
                            <div className="idea-card-list__paging">
                                <Button label="Plus d'idées" mode="tertiary" onClick={this.props.onMoreClicked} />
                            </div>
                        )}
                    </React.Fragment>
                ) : (
                    <div className="idea-card-list__empty">
                        <img className="idea-card-list__empty__img" src="/assets/img/no-idea-result.svg" />
                        <p>Il n'y a pas d'idée correspondant à votre recherche</p>
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
};

IdeaCardListContainer.propTypes = {
    fetchIdeas: PropTypes.func.isRequired,
    onMoreClicked: PropTypes.func,
    status: PropTypes.oneOf(Object.keys(ideaStatus)).isRequired,
    withPaging: PropTypes.bool,
    filters: PropTypes.object,
};

function mapStateToProps(state, ownProps) {
    const { isFetching } = selectLoadingState(state, 'FETCH_IDEAS', ownProps.status);
    const ideas = selectIdeasWithStatus(state, ownProps.status);
    /* paging data */
    const { current_page, last_page } = selectIdeasMetadata(state);
    // show paging if props says so and is not loading and is not at the end of the list
    const withPaging = ownProps.withPaging && current_page < last_page && !isFetching;
    // filter options
    const { themes } = selectStatic(state);
    return {
        ideas,
        isLoading: isFetching && !ideas.length,
        withPaging,
        filters: {
            themes: themes.map(theme => ({ value: theme.name, label: theme.name })),
        },
    };
}

function mapDispatchToProps(dispatch, ownProps) {
    return {
        fetchIdeas: params => dispatch(fetchIdeas(ownProps.status, params, true)),
        onMoreClicked: params => dispatch(fetchNextIdeas(ownProps.status, params)),
        onVoteIdea: (id, vote) => dispatch(voteIdea(id, vote)),
    };
}

export default connect(
    mapStateToProps,
    mapDispatchToProps
)(IdeaCardListContainer);
