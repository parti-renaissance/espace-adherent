import React, { Component } from 'react';

class BulletRanking extends Component {
    render() {
        return (
            <div>
                <div className="bullet--ranking__cpt">
                    <div className="bullet">{this.props.nbTotalEvent}</div>
                    <div className="bullet__title">{this.props.eventTitle}</div>
                </div>
            </div>
        );
    }
}

export default BulletRanking;
