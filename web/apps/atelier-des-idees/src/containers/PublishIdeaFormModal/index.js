import React from 'react';
import PropTypes from 'prop-types';
import { connect } from 'react-redux';
import PublishIdeaFormModal from '../../components/Modal/PublishIdeaFormModal';
import { selectLoadingState } from '../../redux/selectors/loading';
import { selectStatic } from '../../redux/selectors/static';
import { fetchStaticData } from '../../redux/thunk/static';

class PublishFormModalContainer extends React.Component {
    componentDidMount() {
        this.props.initPublishForm();
    }

    render() {
        const { initPublishForm, ...otherProps } = this.props;
        return <PublishIdeaFormModal {...otherProps} />;
    }
}

PublishFormModalContainer.propTypes = {
    initPublishForm: PropTypes.func.isRequired,
};

function formatStaticData(data) {
    return data.map(({ id, name }) => ({ value: id, label: name }));
}

function mapStateToProps(state, { id }) {
    // get request status
    const { isFetching, isSuccess, isError } = selectLoadingState(state, 'PUBLISH_IDEA', id);
    // get static data
    const { themes, needs, categories, committees } = selectStatic(state);
    const formattedCommittees = committees.map(({ uuid, name }) => ({ value: uuid, label: name }));
    return {
        isSubmitting: isFetching,
        isSubmitSuccess: isSuccess,
        isSubmitError: isError,
        themeOptions: formatStaticData(themes),
        localityOptions: formatStaticData(categories),
        difficultiesOptions: formatStaticData(needs),
        committeeOptions: formattedCommittees,
        authorOptions: [{ value: 'alone', label: 'Seul' }, { value: 'committee', label: 'Mon comité' }],
    };
}

function mapDispatchToProps(dispatch) {
    return {
        initPublishForm: () => dispatch(fetchStaticData()),
    };
}

export default connect(
    mapStateToProps,
    mapDispatchToProps
)(PublishFormModalContainer);
