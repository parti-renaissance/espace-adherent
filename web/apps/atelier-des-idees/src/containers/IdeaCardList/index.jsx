import React from 'react';
import PropTypes from 'prop-types';
import { connect } from 'react-redux';
import { selectLoadingState } from '../../redux/selectors/loading';
import { selectIdeasWithStatus } from '../../redux/selectors/ideas';
import Button from '../../components/Button';
import IdeaCardList from '../../components/IdeaCardList';

function IdeaCardListContainer(props) {
    return (
        <React.Fragment>
            <IdeaCardList ideas={props.ideas} isLoading={props.isLoading} mode={props.mode} />
            {props.withPaging && (
                <div className="idea-card-list__paging">
                    <Button label="Plus d'idées" mode="tertiary" />
                </div>
            )}
        </React.Fragment>
    );
}

IdeaCardListContainer.defaultProps = {
    onMoreClicked: undefined,
    withPaging: false,
};

IdeaCardListContainer.propTypes = {
    onMoreClicked: PropTypes.func,
    withPaging: PropTypes.bool,
};

function mapStateToProps(state, ownProps) {
    const isLoading = selectLoadingState(state, `FETCH_IDEAS_${ownProps.status}`);
    const ideas = selectIdeasWithStatus(state, ownProps.status);
    return { ideas, isLoading };
}

export default connect(
    mapStateToProps,
    {}
)(IdeaCardListContainer);
