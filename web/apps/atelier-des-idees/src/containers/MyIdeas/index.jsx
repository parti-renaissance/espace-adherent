import React from 'react';
import PropTypes from 'prop-types';
import { connect } from 'react-redux';
import { DELETE_IDEA_MODAL } from '../../constants/modalTypes';
import { ideaStatus } from '../../constants/api';
import { showModal } from '../../redux/actions/modal';
import {
    deleteMyIdea,
    fetchUserIdeas,
    fetchUserContributions,
} from '../../redux/thunk/ideas';
import { selectMyContributions } from '../../redux/selectors/myContributions';
import MyIdeasModal from '../../components/Modal/MyIdeasModal';

const {
    FINALIZED,
    PENDING,
    DRAFT,
} = ideaStatus;

class MyIdeasContainer extends React.Component {
    componentDidMount() {
        this.props.initMyIdeas();
    }

    render() {
        const { data, ...otherProps } = this.props;
        const { myIdeasData, myContributionsData } = data;
        const { tabActive } = this.props;
        return (
            <MyIdeasModal
                my_ideas={myIdeasData}
                my_contribs={myContributionsData}
                tabActive={tabActive}
                {...otherProps}
            />
        );
    }
}

MyIdeasContainer.propTypes = {
    data: PropTypes.object.isRequired,
};

function mapStateToProps(state) {
    const myIdeasData = state.myIdeas;
    const myContributionsData = state.myContributions;
    return {
        data: {
            myIdeasData,
            myContributionsData,
        },
    };
}

function mapDispatchToProps(dispatch) {
    return {
        onDeleteIdea: id =>
            dispatch(
                showModal(DELETE_IDEA_MODAL, {
                    onConfirmDelete: () => dispatch(deleteMyIdea(id)),
                })
            ),
        initMyIdeas: () => {
            // draft user ideas
            dispatch(fetchUserIdeas({ status: DRAFT }));
            // pending user ideas
            dispatch(fetchUserIdeas({ status: PENDING }));
            // finalized user ideas
            dispatch(fetchUserIdeas({ status: FINALIZED }));
            // user contributions
            dispatch(fetchUserContributions());
        },
        getMyIdeas: params => dispatch(fetchUserIdeas(params)),
        getMyContribs: params => dispatch(fetchUserContributions(params)),
    };
}

export default connect(
    mapStateToProps,
    mapDispatchToProps
)(MyIdeasContainer);
