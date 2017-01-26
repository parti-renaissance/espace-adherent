import React, { PropTypes } from 'react';
import AddressForm from '../components/MembershipAddressForm';

export default class UpdateMembershipIndexAddress extends React.Component
{
    constructor() {
        super();

        this.state = {
            address: null,
            country: null,
            postalCode: null,
            city: null,
            cityName: null,
        };

        this.handleAddressChange = this.handleAddressChange.bind(this);
    }

    componentDidMount() {
        this.setState({
            address: this.props.defaultAddress.address,
            country: this.props.defaultAddress.country,
            postalCode: this.props.defaultAddress.postalCode,
            city: this.props.defaultAddress.city,
            cityName: this.props.defaultAddress.cityName,
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

                {this.state.country === 'FR' && this.state.address ? <input type="hidden" name="update_membership_request[address][address]" value={this.state.address} /> : ''}
                {this.state.country ? <input type="hidden" name="update_membership_request[address][country]" value={this.state.country} /> : ''}
                {this.state.country === 'FR' && this.state.postalCode ? <input type="hidden" name="update_membership_request[address][postalCode]" value={this.state.postalCode} /> : ''}
                {this.state.country === 'FR' && this.state.city ? <input type="hidden" name="update_membership_request[address][city]" value={this.state.city} /> : ''}
                {this.state.country !== 'FR' && this.state.cityName ? <input type="hidden" name="update_membership_request[address][cityName]" value={this.state.cityName} /> : ''}
            </div>
        );
    }
};

UpdateMembershipIndexAddress.propTypes = {
    countries: PropTypes.object.isRequired,
    defaultAddress: PropTypes.object.isRequired
};
