// type xReIcon with jsdoc as Alpine.data second argument

/** @typedef {{label:string, value:string}} Option */
/** @typedef  {import('alpinejs').AlpineComponent} AlpineComponent */

/**
 * Replace Caps, Diacritics for a string
 * @param {string} string
 */
const normaliseString = (string) =>
    string
        .toLowerCase()
        .normalize('NFD')
        .replace(/\p{Diacritic}/gu, '');

/**
 * Search for option in the label of the options
 * @param {string} query
 * @param {Option[]} options
 */
const filterOptions = (query, options) => options.filter((option) => normaliseString(option.label).includes(normaliseString(query)));

const scrollIntoViewWithOffset = (el, offset) => {
    window.scrollTo({
        behavior: 'smooth',
        top: el.getBoundingClientRect().top - document.body.getBoundingClientRect().top - offset,
    });
};
/**
 * Alpine Component for ReSelect
 * @param {{
 *  id: string,
 *  preferredOptions?:Option[],
 *  options: Option[],
 *  placeholder?:string
 *  onQuery: (query:string)=>Promise<Option[]> | null,
 *  blocked?:boolean
 * }} props
 *
 * @returns {AlpineComponent}
 */
const xReSelect = (props) => {
    const options = [...props.preferredOptions, ...props.options.filter((o) => !props.preferredOptions.find((p) => p.value === o.value))];
    const firstOption = options[0];
    const defaultOption =
        !props.placeholder && firstOption
            ? firstOption
            : {
                  value: '',
                  label: '',
              };

    const placeholder = props.placeholder || defaultOption.label;

    return {
        initialOptions: options,
        filteredOptions: options,
        selected: defaultOption,
        selectedIndex: 0,
        query: '',
        placeholder,
        toggle: false,
        blocked: props.blocked || false,
        isValueSet: !props.placeholder,

        init() {
            this.$nextTick(() => {
                const selectInput = this.$refs.select;
                if (!selectInput.value) {
                    if (defaultOption.value) this.$refs.select.value = defaultOption.value;
                } else {
                    this.isValueSet = true;
                    const option = this.initialOptions.find((o) => o.value === selectInput.value);

                    if (option) {
                        this.selected = option;
                        this.query = option.label;
                    }
                }
            });
        },

        /**
         * @param {?Event} event
         */
        handleChangeSetValue() {
            this.toggle = true;

            this.$nextTick(() => {
                this.scrollToTop();
                this.$refs.input.focus();
                this.$refs.input.select();
                this.$refs.input.dispatchEvent(new Event('change'));
            });
        },

        scrollToTop() {
            if (0 < this.filteredOptions.length && 768 > window.innerWidth) {
                scrollIntoViewWithOffset(this.$refs.input, 120);
                document.querySelector('body').style.overflow = 'hidden';
            }
        },

        /**
         * @param {?Event} event
         */
        handleClickAway() {
            if (false === this.toggle) return;
            if (!this.isValueSet) {
                if (this.filteredOptions[0]) {
                    this.setEndValues(this.filteredOptions[0]?.attr?.disabled ? null : this.filteredOptions[0]);
                } else if (defaultOption.value) {
                    this.setEndValues(defaultOption);
                } else {
                    this.setEndValues(null);
                }
            } else {
                this.setEndValues(this.selected, true);
            }
        },

        /**
         * Handle when user type in the input
         * @param {Event} e
         * @return {Promise<void>}
         */
        async handleInput(e) {
            if (this.isValueSet) this.isValueSet = false;
            this.filteredOptions = await this.onQuery(e.target.value);
            this.activeFirst();
            this.scrollToTop();
        },
        /**
         * Handle when user type in the input
         */
        activeFirst() {
            if (0 === this.filteredOptions.length) {
                this.selected = null;
            }
            const [first] = this.filteredOptions;
            this.selected = first?.attr?.disabled ? null : first;
            this.selectedIndex = 0;
        },

        /**
         * @param {KeyboardEvent} event
         */
        handleKeyDown(event) {
            if ('ArrowDown' === event.key || 'ArrowUp' === event.key) {
                let index = Math.max(this.selectedIndex - 1, 0);
                if ('ArrowDown' === event.key) {
                    index = Math.min(this.selectedIndex + 1, this.filteredOptions.length - 1);
                }

                const element = this.filteredOptions[index];
                this.selected = element?.attr?.disabled ? this.selected : element;
            } else if ('Enter' === event.key) {
                event.preventDefault();
                this.setEndValues(this.filteredOptions[this.selectedIndex]?.attr?.disabled ? null : this.filteredOptions[this.selectedIndex]);
            }
        },

        isOptionSelected(option) {
            if (!option) return false;
            if (!this.selected) return false;
            return option.value === this.selected.value;
        },

        /**
         * @param {Option | null} option
         * @param {boolean} silent=false
         */
        setEndValues(option, silent = false) {
            if (!option && defaultOption.value) {
                option = defaultOption;
            }
            if (!option || option?.attr?.disabled) {
                this.$refs.select.value = '';
                this.$refs.select.dispatchEvent(new Event('change'));
                this.$dispatch(`autocomplete_change:${props.id}`, '');
                this.toggle = false;
                return;
            }

            if (option) {
                this.selected = option;
                this.isValueSet = true;
            }

            this.$refs.select.value = option && option.value ? option.value : '';
            this.query = option && option.label ? option.label : '';
            this.toggle = false;
            document.querySelector('body').style.overflow = 'scroll';

            this.$refs.select.dispatchEvent(new Event('change'));
            if (!silent) {
                this.$refs.input.dispatchEvent(new Event('change'));
                this.$nextTick(() => {
                    this.$refs.input.blur();
                    this.$refs.input.dispatchEvent(new Event('change'));
                });
            }
            if (option.value) {
                this.$dispatch(`autocomplete_change:${props.id}`, option.value);
            }
        },
        async onQuery(query) {
            if (props.onQuery) {
                this.$dispatch(`x-validate:${props.id.toLowerCase()}`, {
                    status: 'loading',
                    message: 'Chargement en cours...',
                });
                return props.onQuery(query).then((opts) => {
                    this.$dispatch(`x-validate:${props.id.toLowerCase()}`, {
                        status: 'default',
                        message: '',
                    });
                    return opts;
                });
            }
            return filterOptions(query, this.initialOptions);
        },
    };
};

export default {
    xReSelect,
};
