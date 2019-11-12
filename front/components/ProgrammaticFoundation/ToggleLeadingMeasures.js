import React from 'react';

export default class ToggleLeadingMeasures extends React.Component {
    handleChange(e) {
        this.props.onToggleChange(e.target.checked);
    }

    render() {
        return (
            <div className="programmatic-foundation__leading em-form">
                <div className="form__checkbox">
                  <input
                    type="checkbox"
                    name="toggle-leading-measures"
                    id="toggle-leading-measures"
                    onChange={this.handleChange.bind(this)}
                  />
                  <label
                  htmlFor="toggle-leading-measures"
                  className="form__label"
                  >Mesures phares uniquement</label>
                </div>
            </div>
        );
    }
}
