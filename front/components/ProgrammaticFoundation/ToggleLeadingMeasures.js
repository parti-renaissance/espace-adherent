import React, {PropTypes} from 'react';

export default class ToggleLeadingMeasures extends React.Component {
    render() {
        return (
            <div className="programmatic-foundation__leading em-form">
                <div className="form__checkbox">
                    <input
                        type="checkbox"
                        name="toggle-leading-measures"
                        id="toggle-leading-measures"
                        onChange={event => this.props.onToggleChange(event.target.checked)}
                        checked={this.props.value}
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

ToggleLeadingMeasures.propsType = {
    onToggleChange: PropTypes.func.isRequired,
    value: PropTypes.bool,
};
