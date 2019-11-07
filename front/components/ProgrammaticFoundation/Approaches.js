import React from 'react';
import Approach from './Approach';

export default class Approaches extends React.Component {
    render() {
        const renderedApproaches = this.props.approaches.map(
            approach => <Approach key={approach.position} approach={approach} />
        );

        return (<div className="programmatic-foundation__approaches">{renderedApproaches}</div>);
    }
}
