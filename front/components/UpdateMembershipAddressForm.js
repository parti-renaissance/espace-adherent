import React, { PropTypes } from 'react';
import reqwest from 'reqwest';

export default class AddressForm extends React.Component
{
    constructor() {
        super();

        this.state = {
            loading: false,
            cities: [],
            address: null,
            country: null,
            postalCode: null,
            city: null,
            cityName: null,
        };

        this.handleAddressChange = this.handleAddressChange.bind(this);
        this.handleCountryChange = this.handleCountryChange.bind(this);
        this.handlePostalCodeChange = this.handlePostalCodeChange.bind(this);
        this.handleCityChange = this.handleCityChange.bind(this);
        this.handleCityNameChange = this.handleCityNameChange.bind(this);
    }

    fetchCities(postalCode) {
        reqwest({
            url: Routing.generate('api_postal_code', { postalCode: postalCode }),
            success: (cities) => {
                let state = this.state;
                state.cities = cities;
                state.loading = false;
                state.city = postalCode+'-'+Object.keys(cities)[0];

                this.setState(state);
                this.dispatchAddressChange(state);
            },
            error: () => {
                let state = this.state;
                state.cities = [];
                state.loading = false;
                state.city = null;

                this.setState(state);
                this.dispatchAddressChange(state);
            },
        });
    }

    dispatchAddressChange(state) {
        if (!state.postalCode || state.postalCode.length !== 5) {
            return;
        }

        this.props.onAddressChange({
            address: state.address,
            country: state.country,
            postalCode: state.postalCode,
            city: state.city,
            cityName: state.cityName,
        });
    }

    componentDidMount() {
        if (this.props.defaultAddress.postalCode.length === 5) {
            this.fetchCities(this.props.defaultAddress.postalCode);
        }

        this.setState({
            loading: this.props.defaultAddress.postalCode.length === 5,
            address: this.props.defaultAddress.address,
            country: this.props.defaultAddress.country,
            postalCode: this.props.defaultAddress.postalCode,
            city: this.props.defaultAddress.city,
            cityName: this.props.defaultAddress.cityName,
        });
    }

    handleCountryChange(event) {
        let state = this.state;
        state.country = event.target.value;

        this.setState(state);
        this.dispatchAddressChange(state);
    }

    handlePostalCodeChange(event) {
        let state = this.state;
        state.postalCode = event.target.value;
        state.loading = event.target.value.length === 5;

        if (event.target.value.length === 5) {
            this.fetchCities(event.target.value);
        }

        this.setState(state);
        this.dispatchAddressChange(state);
    }

    handleCityChange(event) {
        let state = this.state;
        state.city = event.target.value;

        this.setState(state);
        this.dispatchAddressChange(state);
    }

    handleCityNameChange(event) {
        let state = this.state;
        state.cityName = event.target.value;

        this.setState(state);
        this.dispatchAddressChange(state);
    }

    handleAddressChange(event) {
        let state = this.state;
        state.address = event.target.value;

        this.setState(state);
        this.dispatchAddressChange(state);
    }

    render() {
        let countriesOptions = [], citiesOptions = [];

        for (let code in this.props.countries) {
            countriesOptions.push(<option key={code} value={code}>{this.props.countries[code]}</option>);
        }

        for (let code in this.state.cities) {
            citiesOptions.push(<option key={code} value={this.state.postalCode+'-'+code}>{this.state.cities[code]}</option>);
        }

        return (
            <div>
                <div className="form__row register__form--trunc">
                    {typeof this.props.defaultAddress.errors.address !== 'undefined'
                        ? <div dangerouslySetInnerHTML={{ __html: this.props.defaultAddress.errors.address }} />
                        : ''}

                    <input type="text"
                           id="update_membership_request_address_address"
                           required="required"
                           placeholder="Adresse postale"
                           defaultValue={this.props.defaultAddress.address}
                           onChange={this.handleAddressChange}
                           className="form form--full form__field" />
                </div>

                <div className="l__row">
                    <div className="form__row register__form__zip_code">
                        <div>
                            <input type="text"
                                   id="update_membership_request_address_postalCode"
                                   required="required"
                                   className="form form__field"
                                   maxLength="5"
                                   placeholder="Code postal"
                                   disabled={this.state.loading}
                                   defaultValue={this.state.postalCode ? this.state.postalCode : this.props.defaultAddress.postalCode}
                                   onChange={this.handlePostalCodeChange} />
                        </div>
                    </div>

                    <div className="form__row register__form__city">
                        {typeof this.props.defaultAddress.errors.city !== 'undefined'
                            ? <div dangerouslySetInnerHTML={{ __html: this.props.defaultAddress.errors.city }} />
                            : ''}

                        {this.state.country === 'FR' ?
                            <select id="update_membership_request_address_city"
                                    required="required"
                                    defaultValue={this.props.defaultAddress.city}
                                    onChange={this.handleCityChange}
                                    className="form form__field">
                                {citiesOptions}
                            </select>
                            : '' }

                        {this.state.country !== 'FR' ?
                            <div>
                                <input id="update_membership_request_address_city"
                                       required="required"
                                       defaultValue={this.props.defaultAddress.cityName}
                                       onChange={this.handleCityNameChange}
                                       className="form form__field"/>
                            </div>
                            : '' }
                    </div>

                    <div className="form__row register__form__country">
                        <label className="form form__label color--blue" htmlFor="update_membership_request_country">
                            Pays
                        </label>

                        {typeof this.props.defaultAddress.errors.country !== 'undefined'
                            ? <div dangerouslySetInnerHTML={{ __html: this.props.defaultAddress.errors.country }} />
                            : ''}

                        <select id="update_membership_request_country"
                                required="required"
                                className="form--mid form form__field"
                                defaultValue={this.state.country ? this.state.country : this.props.defaultAddress.country}
                                onChange={this.handleCountryChange}>
                            {countriesOptions}
                        </select>
                    </div>

                    {this.state.loading ?
                        <div className="loader">
                            <div className="loader__edge loader__edge--small"></div>
                            <div className="loader__edge loader__edge--big"></div>
                        </div>
                        : ''}
                </div>
            </div>
        );
    }
};

AddressForm.propTypes = {
    countries: PropTypes.object.isRequired,
    defaultAddress: PropTypes.object.isRequired,
    onAddressChange: PropTypes.func.isRequired
};
