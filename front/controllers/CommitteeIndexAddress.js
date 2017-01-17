import React, { PropTypes } from 'react';
import AddressForm from '../components/CommitteeAddressForm';

export default class CommitteeIndexAddress extends React.Component
{
    constructor() {
        super();

        this.state = {
            country: null,
            postalCode: null,
            city: null,
        };

        this.handleAddressChange = this.handleAddressChange.bind(this);
    }

    componentDidMount() {
        this.setState({
            country: this.props.defaultAddress.country,
            postalCode: this.props.defaultAddress.postalCode,
            city: this.props.defaultAddress.city,
        });
    }

    handleAddressChange(address) {
        this.setState(address);
    }

    render() {
        return (
            <div>
                <AddressForm
                    countries={this.props.countries}
                    defaultAddress={this.props.defaultAddress}
                    onAddressChange={this.handleAddressChange}
                />

                {this.state.country ? <input type="hidden" name="committee[country]" value={this.state.country} /> : ''}
                {this.state.country === 'FR' && this.state.postalCode ? <input type="hidden" name="committee[postalCode]" value={this.state.postalCode} /> : ''}
                {this.state.country === 'FR' && this.state.city ? <input type="hidden" name="committee[city]" value={this.state.city} /> : ''}
            </div>
        );
    }
};

CommitteeIndexAddress.propTypes = {
    countries: PropTypes.object.isRequired,
    defaultAddress: PropTypes.object.isRequired
};
