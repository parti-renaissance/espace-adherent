import React, { Component } from "react";
import PropTypes from "prop-types";

import BulletRanking from "./symbols/BulletRanking";

class Ranking extends Component {
    render() {
        const { title, committees } = this.props;
        return (
            <div className="ranking__cpt">
                <div className="left--side">
                    <p>{title}</p>
                </div>
                <div className="right--side">
                    {committees.map((committee, i) => (
                        <BulletRanking
                            key={i}
                            nbTotalEvent={committee.events}
                            eventTitle={committee.name}
                        />
                    ))}
                </div>
            </div>
        );
    }
}

export default Ranking;

Ranking.propTypes = {
    key: PropTypes.number,
    nbTotalEvent: PropTypes.number,
    eventTitle: PropTypes.string
};
