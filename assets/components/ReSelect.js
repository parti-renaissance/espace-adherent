// type xReIcon with jsdoc as Alpine.data second argument

/** @typedef {{label:string, value:string}} Option */

/**
 * Replace Caps, Diacritics for a string
 * @param {string} string
 */
const normaliseString = (string) => string.toLowerCase()
    .normalize('NFD')
    .replace(/\p{Diacritic}/gu, '');

/**
 * Search for option in the label of the options
 * @param {string} query
 * @param {Option[]} options
 */
const filterOptions = (query, options) => options
    .filter((option) => normaliseString(option.label)
        .includes(normaliseString(query)));

/**
 * Alpine Component for ReSelect
 * @param {{
 *  id: string,
 *  preferredOptions?:Option[],
 *  options: Option[],
 *  placeholder?:string
 *  onQuery: (query:string)=>Promise<Option[]> | null,
 * }} props
 */
const xReSelect = (props) => {
    const options = [
        ...props.preferredOptions,
        ...props.options.filter((o) => !props.preferredOptions.find((p) => p.value === o.value)),
    ];
    const firstOption = options[0];
    const defaultOption = !props.placeholder && firstOption ? firstOption : {
        value: '',
        label: props.placeholder,
    };
    const placeholder = props.placeholder || defaultOption.label;

    return {
        filteredOptions: options,
        selected: defaultOption.value,
        selectedIndex: 0,
        query: '',
        placeholder,
        toggle: false,
        isValueSet: !props.placeholder,
        handleChangeSetValue(event) {
            this.isValueSet = false;
            this.filteredOptions = options;
            this.query = '';
            this.$nextTick(() => {
                this.$refs.input.focus();
            });
        },
        handleClickAway(event) {
            if (!this.isValueSet && this.filteredOptions[0]) {
                this.setEndValues(this.filteredOptions[0]);
            } else if (!this.isValueSet && defaultOption.value) {
                this.setEndValues(defaultOption);
            } else {
                this.toggle = false;
                // this.$dispatch(`autocomplete_change:${props.id}`, '');
            }
        },
        async handleInput(text) {
            const newOpts = await this.onQuery(text);
            this.filteredOptions = newOpts;
            this.activeFirst();
        },
        activeFirst() {
            if (0 === this.filteredOptions.length) {
                return;
            }
            this.selected = this.filteredOptions[0].value;
            this.selectedIndex = 0;
        },
        /**
         * @param {KeyboardEvent} event
         */
        handleKeyDown(event) {
            if ('ArrowDown' === event.key) {
                this.selectedIndex = Math.min(this.selectedIndex + 1, this.filteredOptions.length - 1);
                this.selected = this.filteredOptions[this.selectedIndex].value;
            } else if ('ArrowUp' === event.key) {
                this.selectedIndex = Math.max(this.selectedIndex - 1, 0);
                this.selected = this.filteredOptions[this.selectedIndex].value;
            } else if ('Enter' === event.key) {
                this.setEndValues(this.filteredOptions[this.selectedIndex]);
            }
        },
        setEndValues(option) {
            if (!option) {
                option = {
                    value: 'FR',
                    label: 'France',
                };
            }
            this.selected = option.value;
            this.query = '';
            this.placeholder = option.label;
            this.toggle = false;
            this.isValueSet = true;
            this.$refs.select.value = option.value;
            this.$refs.select.dispatchEvent(new Event('change'));
            if (option.value) {
                this.$dispatch(`autocomplete_change:${props.id}`, option.value);
            }
        },
        async onQuery(query) {
            if (props.onQuery) {
                this.$dispatch(`x-validate:${props.id}`, {
                    status: 'loading',
                    message: 'Chargement en cours...',
                });
                return props.onQuery(query)
                    .then((opts) => {
                        this.$dispatch(`x-validate:${props.id}`, {
                            status: 'default',
                            message: '',
                        });
                        return opts;
                    });
            }
            return filterOptions(query, options);
        },
    };
};

export default {
    xReSelect,
};
