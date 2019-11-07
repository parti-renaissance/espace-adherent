import React from 'react';

export default class ToggleLeadingMeasures extends React.Component {
    handleChange(e) {
        this.props.onToggleChange(e.target.checked);
    }

    render() {
        return (
            <div>
              <input
                type="checkbox"
                name="toggle-leading-measures"
                onChange={this.handleChange.bind(this)}
              />
              <label htmlFor="toggle-leading-measures">Mesures phares uniquement</label>
            </div>
        );
    }
}
