import EventEmitter from 'events';
import GooglePlaceAutocomplete from './GooglePlaceAutocomplete';

export default class AutocompletedAddressForm extends EventEmitter {
    constructor(autocompleteWrapper, addressBlock, addressObject, helpMessageBlock = null, showWhenFilled = false, showOnlyAutocomplete = false) {
        super();

        this._autocompleteWrapper = autocompleteWrapper;
        this._addressBlock = addressBlock;
        this._helpMessageBlock = helpMessageBlock;
        this._address = addressObject;
        this._showWhenFilled = showWhenFilled;
        this._showOnlyAutocomplete = showOnlyAutocomplete;
    }

    buildWidget() {
        // Show the autocomplete when the address fields are not filled
        if (true === this._showWhenFilled || !this._address.isFilled() || this._showOnlyAutocomplete) {
            // Stop if google class is undefined
            if ('undefined' === typeof google) {
                return;
            }

            const autocomplete = new GooglePlaceAutocomplete(
                this._autocompleteWrapper,
                this._address,
                'form form--full form__field em-form__field',
                'undefined' !== typeof this._address._address.attributes.disabled && 'disabled' === this._address._address.attributes.disabled.value
            );

            const postCodeField = this._address._postalCode;
            const addressField = this._address._address;
            const addressFieldAutoComplete = this._autocompleteWrapper.children;

            const onValueUpdate = (e) => {
                if ('' === e.target.value) {
                    this.hideAddress();
                    addressFieldAutoComplete[0].value = '';
                    show(this._autocompleteWrapper);
                }
            };

            addressField.addEventListener('input', onValueUpdate);

            autocomplete.build();

            if (this._showOnlyAutocomplete) {
                autocomplete.setInputElementValue();
            }

            if (false === this._showWhenFilled) {
                this.hideAddress();
            }

            show(this._autocompleteWrapper);

            autocomplete.on('changed', () => {
                if (false === this._showWhenFilled && !this._showOnlyAutocomplete) {
                    this.showAddress();
                    hide(this._autocompleteWrapper);

                    const inputEvent = new Event('input');
                    postCodeField.dispatchEvent(inputEvent);
                }

                if (this._helpMessageBlock) {
                    hide(this._helpMessageBlock);
                }

                this.emit('changed');
            });

            if (this._helpMessageBlock) {
                this.addHelpMessage(autocomplete);
            }
        } else {
            hide(this._autocompleteWrapper);
        }
    }

    addHelpMessage(autocomplete) {
        autocomplete.on('no_result', () => show(this._helpMessageBlock));

        const removeAutocompleteLink = this._helpMessageBlock.getElementsByTagName('a')[0];

        if (!removeAutocompleteLink) {
            return;
        }

        once(removeAutocompleteLink, 'click', (event) => {
            event.preventDefault();

            hide(this._helpMessageBlock);

            autocomplete.placeChangeHandle();

            this.emit('no_result');
        });
    }

    showAddress() {
        this._address.resetRequired();
        show(this._addressBlock);
    }

    hideAddress() {
        this._address.setRequired(false);
        hide(this._addressBlock);
    }
}
