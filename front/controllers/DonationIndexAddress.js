import React, { PropTypes } from 'react';
import AddressForm from '../components/AddressForm';

export default class DonationIndexAddress extends React.Component
{
    constructor() {
        super();

        this.state = {
            country: null,
            postalCode: null,
            city: null,
            address: null,
        };

        this.handleAddressChange = this.handleAddressChange.bind(this);
    }

    componentDidMount() {
        this.setState({
            country: this.props.defaultAddress.country,
            postalCode: this.props.defaultAddress.postalCode,
            city: this.props.defaultAddress.city,
            address: this.props.defaultAddress.address,
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

                {this.state.country ? <input type="hidden" name="app_donation[country]" value={this.state.country} /> : ''}
                {this.state.country === 'FR' && this.state.postalCode ? <input type="hidden" name="app_donation[postalCode]" value={this.state.postalCode} /> : ''}
                {this.state.country === 'FR' && this.state.city ? <input type="hidden" name="app_donation[city]" value={this.state.city} /> : ''}
                {this.state.country === 'FR' && this.state.address ? <input type="hidden" name="app_donation[address]" value={this.state.address} /> : ''}
            </div>
        );
    }
};

DonationIndexAddress.propTypes = {
    countries: PropTypes.object.isRequired,
    defaultAddress: PropTypes.object.isRequired
};
