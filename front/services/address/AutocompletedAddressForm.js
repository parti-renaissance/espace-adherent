import EventEmitter from 'events';
import GooglePlaceAutocomplete from './GooglePlaceAutocomplete';

export default class AutocompletedAddressForm extends EventEmitter {
    constructor(autocompleteWrapper, addressBlock, addressObject, helpMessageBlock = null, showWhenFilled = false) {
        super();

        this._autocompleteWrapper = autocompleteWrapper;
        this._addressBlock = addressBlock;
        this._helpMessageBlock = helpMessageBlock;
        this._address = addressObject;
        this._showWhenFilled = showWhenFilled;
    }

    buildWidget() {
        // Show the autocomplete when the address fields are not filled
        if (true === this._showWhenFilled || !this._address.isFilled()) {
            // Stop if google class is undefined
            if ('undefined' === typeof google) {
                return;
            }

            const autocomplete = new GooglePlaceAutocomplete(
                this._autocompleteWrapper,
                this._address,
                'form form--full form__field em-form__field'
            );

            autocomplete.build();

            if (false === this._showWhenFilled) {
                this.hideAddress();
            }

            show(this._autocompleteWrapper);

            autocomplete.on('changed', () => {
                if (false === this._showWhenFilled) {
                    this.showAddress();
                    hide(this._autocompleteWrapper);
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
