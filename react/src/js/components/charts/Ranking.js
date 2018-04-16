import React, { Component } from 'react';
import BulletRanking from './symbols/BulletRanking';

class Ranking extends Component {
    render() {
        return (
            <div className="ranking__cpt">
                <div className="left--side">
                    <p>{this.props.rankingTitle}</p>
                </div>
                <div className="right--side">
                    <BulletRanking nbTotalEvent={1} eventTitle="Mon Event" />
                    <BulletRanking nbTotalEvent={3} eventTitle="Mon Event" />
                    <BulletRanking nbTotalEvent={4} eventTitle="Mon Event" />
                    <BulletRanking nbTotalEvent={8} eventTitle="Mon Event" />
                    <BulletRanking nbTotalEvent={100} eventTitle="Mon Event" />
                    <BulletRanking nbTotalEvent={13} eventTitle="Mon Event" />
                    <BulletRanking nbTotalEvent={100000} eventTitle="Mon Event" />
                </div>
            </div>
        );
    }
}

export default Ranking;
