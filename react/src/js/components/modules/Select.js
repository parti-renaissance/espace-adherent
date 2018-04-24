import React, { Component } from 'react';
import PropTypes from 'prop-types';

import { connect } from 'react-redux';
import * as actionCreators from './../../actions/index.js';

class Select extends Component {
    constructor(props) {
        super(props);
        this.handleChange = this.handleChange.bind(this);
    }

	handleChange = (e) => {
	    this.props.committeeFilter(e.target.value);
	};
	render() {
	    const { committees, id, name } = this.props;

	    return (
	        <select id={id} name={name} className="select__cpt" onChange={this.handleChange}>
	            <option>Choisir un comité</option>
	            {committees.map((committee, i) => (
	                <option value={committee.countryName} key={i}>
	                    {committee.countryName}
	                </option>
	            ))}
	        </select>
	    );
	}
}

const mapStateToProps = state => ({
    committees: state.fetch.committees,
});

export default connect(mapStateToProps, actionCreators)(Select);

Select.propTypes = {
    id: PropTypes.string,
    name: PropTypes.string,
    value: PropTypes.string,
    key: PropTypes.number,
    onChange: PropTypes.func,
};
