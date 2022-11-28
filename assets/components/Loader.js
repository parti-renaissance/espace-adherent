import React from 'react';
import PropTypes from 'prop-types';

export default class Loader extends React.Component {
    constructor(props) {
        super(props);

        this.wrapperClassName = props.wrapperClassName || '';
        this.title = props.title || '';
    }

    render() {
        return <div className={this.wrapperClassName}>
            <div className={'spin-loader'}/>
            {this.title}
        </div>;
    }
}

Loader.propTypes = {
    wrapperClassName: PropTypes.string,
    title: PropTypes.string,
};
