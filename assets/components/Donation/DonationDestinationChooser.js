import React from 'react';

export default class DonationDestinationChooser extends React.Component {
    constructor(props) {
        super(props);

        this.state = {
            destination: props.value
        };

        this.handleDestinationChange = this.handleDestinationChange.bind(this);
    }

    handleDestinationChange(destination) {
        if (this.props.onChange) {
            this.props.onChange(destination);
        }

        this.setState({ destination });
    }

    render() {
        return (
            <div className={'mb-12'}>
                <div className="inline-flex justify-center space-x-5 text-green">
                    <div>
                        <input
                            type="checkbox"
                            className="form-checkbox"
                            name="localDestination"
                            id="donation-localDestination"
                            defaultChecked={this.state.destination}
                            onChange={() => this.handleDestinationChange(true)}
                        />
                        <label htmlFor="donation-localDestination" id="donation-localDestination_label" className={'ml-2'}>
                            Je souhaite que mon don participe au financement de mon assemblée départementale.
                        </label>
                    </div>
                </div>
            </div>
        );
    }
}
