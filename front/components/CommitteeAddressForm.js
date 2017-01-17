import React, { PropTypes } from 'react';
import reqwest from 'reqwest';

export default class AddressForm extends React.Component
{
    constructor() {
        super();

        this.state = {
            loading: false,
            cities: [],
            country: null,
            postalCode: null,
            city: null,
        };

        this.handleCountryChange = this.handleCountryChange.bind(this);
        this.handlePostalCodeChange = this.handlePostalCodeChange.bind(this);
        this.handleCityChange = this.handleCityChange.bind(this);
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
            country: state.country,
            postalCode: state.postalCode,
            city: state.city,
        });
    }

    componentDidMount() {
        if (this.props.defaultAddress.postalCode.length === 5) {
            this.fetchCities(this.props.defaultAddress.postalCode);
        }

        this.setState({
            country: this.props.defaultAddress.country,
            postalCode: this.props.defaultAddress.postalCode,
            city: this.props.defaultAddress.city,
            loading: this.props.defaultAddress.postalCode.length === 5,
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
                <div className="form__row">
                    <label className="form form__label required" htmlFor="committee_country">
                        Pays
                    </label>

                    {typeof this.props.defaultAddress.errors.country !== 'undefined'
                        ? <div dangerouslySetInnerHTML={{ __html: this.props.defaultAddress.errors.country }} />
                        : ''}

                    <select id="committee_country"
                            className="form--full form form__field"
                            defaultValue={this.state.country ? this.state.country : this.props.defaultAddress.country}
                            onChange={this.handleCountryChange}>
                        {countriesOptions}
                    </select>
                </div>

                {this.state.country === 'FR' ?
                    <div>
                        <div className="l__row">
                            <div className="form__row">
                                <label className="form form__label" htmlFor="committee_postalCode">
                                    Code postal
                                </label>

                                {typeof this.props.defaultAddress.errors.postalCode !== 'undefined'
                                    ? <div dangerouslySetInnerHTML={{ __html: this.props.defaultAddress.errors.postalCode }} />
                                    : ''}

                                <div>
                                    <input type="text"
                                           id="committee_postalCode"
                                           required="required"
                                           className="form form__field"
                                           maxLength="5"
                                           disabled={this.state.loading}
                                           defaultValue={this.state.postalCode ? this.state.postalCode : this.props.defaultAddress.postalCode}
                                           onChange={this.handlePostalCodeChange} />
                                </div>
                            </div>

                            <div className="form__row">
                                <label className="form form__label" htmlFor="committee_city">
                                    Ville
                                </label>

                                {typeof this.props.defaultAddress.errors.city !== 'undefined'
                                    ? <div dangerouslySetInnerHTML={{ __html: this.props.defaultAddress.errors.city }} />
                                    : ''}

                                <div>
                                    <select id="committee_city"
                                            defaultValue={this.props.defaultAddress.city}
                                            onChange={this.handleCityChange}
                                            className="form form__field committee__form__city">
                                        {citiesOptions}
                                    </select>
                                </div>
                            </div>

                            {this.state.loading ?
                                <div className="loader">
                                    <div className="loader__edge loader__edge--small"></div>
                                    <div className="loader__edge loader__edge--big"></div>
                                </div>
                                : ''}
                        </div>
                    </div>
                    : ''}
            </div>
        );
    }
};

AddressForm.propTypes = {
    countries: PropTypes.object.isRequired,
    defaultAddress: PropTypes.object.isRequired,
    onAddressChange: PropTypes.func.isRequired
};
