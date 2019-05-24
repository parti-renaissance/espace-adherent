import EventEmitter from 'events';
import GooglePlaceAutocomplete from './GooglePlaceAutocomplete';

export default class AutocompletedAddressForm extends EventEmitter {
    constructor(autocompleteWrapper, addressBlock, helpMessageBlock, addressObject, withHelpMessage = true) {
        super();
        this._autocompleteWrapper = autocompleteWrapper;
        this._addressBlock = addressBlock;
        this._helpMessageBlock = helpMessageBlock
        this._address = addressObject;
        this._withHelpMessage = withHelpMessage;
    }

    buildWidget() {
        // Stop if google class is undefined
        if ('undefined' === typeof google) {
            return;
        }

        // Show the autocomplete when the address fields are not filled
        if (!this._address.isFilled()) {

            const autocomplete = new GooglePlaceAutocomplete(this._autocompleteWrapper, this._address, 'form form--full form__field');

            autocomplete.build();

            this.hideAddress();

            autocomplete.once('changed', () => {
                this.showAddress();
                hide(this._autocompleteWrapper);

                this.emit('changed');
            });

            if (this._withHelpMessage) {
                this.addHelpMessage(autocomplete);
            }
        }
    }

    addHelpMessage(autocomplete) {
        const autocompleteHelpMessage = this._helpMessageBlock;

        autocomplete.once('no_result', () => show(autocompleteHelpMessage));

        const removeAutocompleteLink = autocompleteHelpMessage.getElementsByTagName('a')[0];

        on(removeAutocompleteLink, 'click', (event) => {
            event.preventDefault();

            hide(autocompleteHelpMessage);
            off(removeAutocompleteLink, 'click');

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
