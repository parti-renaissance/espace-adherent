import React, { PropTypes } from 'react';

export default class Loader extends React.Component {
    constructor(props) {
        super(props);

        this.wrapperClassName = props.wrapperClassName || 'space--30-0';
        this.title = props.title || '';
    }

    render() {
        return <div className={this.wrapperClassName}>
            <div className="spin-loader" />
            {this.title}
        </div>;
    }
}

Loader.propsType = {
    wrapperClassName: PropTypes.string.optional,
    title: PropTypes.string.optional,
};
