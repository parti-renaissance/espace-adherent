import EventEmitter from 'events';
import GooglePlaceAutocomplete from './GooglePlaceAutocomplete';

export default class AutocompletedAddressForm extends EventEmitter {
    constructor(autocompleteWrapper, addressBlock, addressObject, helpMessageBlock = null) {
        super();
        this._autocompleteWrapper = autocompleteWrapper;
        this._addressBlock = addressBlock;
        this._helpMessageBlock = helpMessageBlock;
        this._address = addressObject;
    }

    buildWidget() {
        // Stop if google class is undefined
        if ('undefined' === typeof google) {
            return;
        }

        // Show the autocomplete when the address fields are not filled
        if (!this._address.isFilled()) {

            const autocomplete = new GooglePlaceAutocomplete(this._autocompleteWrapper, this._address, 'form form--full form__field em-form__field');

            autocomplete.build();

            this.hideAddress();

            autocomplete.once('changed', () => {
                this.showAddress();
                hide(this._autocompleteWrapper);

                if (this._helpMessageBlock) {
                    hide(this._helpMessageBlock);
                }

                this.emit('changed');
            });

            if (this._helpMessageBlock) {
                this.addHelpMessage(autocomplete);
            }
        }
    }

    addHelpMessage(autocomplete) {
        autocomplete.once('no_result', () => show(this._helpMessageBlock));

        const removeAutocompleteLink = this._helpMessageBlock.getElementsByTagName('a')[0];

        once(removeAutocompleteLink, 'click', (event) => {
            event.preventDefault();

            hide(this._helpMessageBlock);

            autocomplete.placeChangeHandle();
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
