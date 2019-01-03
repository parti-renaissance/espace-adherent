import React from 'react';
import PropTypes from 'prop-types';
import { connect } from 'react-redux';
import { selectMyIdeas } from '../../redux/selectors/myIdeas';
import { selectMyContributions } from '../../redux/selectors/myContributions';
import MyIdeasModal from '../../components/Modal/MyIdeasModal';

function MyIdeasContainer(props) {
    const { myIdeasData, myContributionsData } = props.data;
    const { tabActive } = props;
    return (
        <MyIdeasModal
            my_ideas={myIdeasData}
            my_contribs={myContributionsData}
            tabActive={tabActive}
        />
    );
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

export default connect(
    mapStateToProps,
    {}
)(MyIdeasContainer);
