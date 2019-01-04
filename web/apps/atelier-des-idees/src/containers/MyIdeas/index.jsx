import React from 'react';
import PropTypes from 'prop-types';
import { connect } from 'react-redux';
import { DELETE_IDEA_MODAL } from '../../constants/modalTypes';
import { showModal } from '../../redux/actions/modal';
import { deleteIdea, fetchUserIdeas, fetchUserContributions } from '../../redux/thunk/ideas';
import { selectMyIdeas } from '../../redux/selectors/myIdeas';
import { selectMyContributions } from '../../redux/selectors/myContributions';
import MyIdeasModal from '../../components/Modal/MyIdeasModal';

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
    const myIdeasData = selectMyIdeas(state);
    const myContributionsData = selectMyContributions(state);
    return {
        data: { myIdeasData, myContributionsData },
    };
}

function mapDispatchToProps(dispatch) {
    return {
        onDeleteIdea: id =>
            dispatch(
                showModal(DELETE_IDEA_MODAL, {
                    onConfirmDelete: () => dispatch(deleteIdea(id)),
                })
            ),
        initMyIdeas: () => {
            // user ideas
            dispatch(fetchUserIdeas());
            // user contributions
            dispatch(fetchUserContributions());
        },
    };
}

export default connect(
    mapStateToProps,
    mapDispatchToProps
)(MyIdeasContainer);
