import React, { Component } from 'react';
import WomanSign from './../../../images/pictos/woman_sign.svg';
import ManSign from './../../../images/pictos/man_sign.svg';

class Summary extends Component {
    render() {

        const {womanPercentage, manPercentage, summaryDescription } = this.props;

        return (
            <div className="summary__cpn">
                <h2>9768</h2>
                <p>{summaryDescription}</p>
                <div className="summary__parity">
                    {womanPercentage ?
                        <div>
                            <p>{womanPercentage}</p>
                            <img src={WomanSign} alt="Woman Sign"/>
                        </div>
                    : null }

                    {manPercentage ?
                        <div>
                            <p>{manPercentage}</p>
                            <img src={ManSign} alt="Man Sign"/>
                        </div>
                    : null}

                </div>
            </div>
        )
    }
};

export default Summary;
