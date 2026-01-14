/** @typedef  {import('alpinejs').AlpineComponent} AlpineComponent */

/**
 * First Step component for funnel
 *
 * @param {{
 *   packageConfig?: Object | null,
 *   initialPackageValues?: Object | null,
 *   uncheckInputs?: boolean | null,
 *   initialPayedAmount?: float | null,
 *   discountFactor?: float | null,
 *   discountLabel?: string | null,
 * }} props
 *
 * @returns {AlpineComponent}
 */
const Page = (props) => ({
    packageConfig: props.packageConfig || null,
    initialPayedAmount: props.initialPayedAmount,
    packageValues: 'object' === typeof props.initialPackageValues ? props.initialPackageValues : {},
    availabilities: {},
    allowedOptions: {},
    withDiscount: false,
    accessibility: false,

    init() {
        this.$nextTick(() => {
            const tokenInput = dom('input[name="frc-captcha-solution"]:last-child');

            if (dom('.frc-captcha')) {
                friendlyChallenge.autoWidget.opts.doneCallback = (token) => {
                    this.captchaToken = token;
                    this.fieldsValid.captcha = true;
                };
            } else if (tokenInput && tokenInput.value) {
                this.captchaToken = tokenInput.value;
                this.fieldsValid.captcha = true;
            }

            const firstError = Array.from(findAll(document, '.re-text-status--error')).find((el) => '' !== el.textContent.trim());
            if (firstError) {
                firstError.scrollIntoView({
                    behavior: 'smooth',
                    block: 'center',
                });
            }
        });

        this.withDiscount = !!dom('input[name*="[withDiscount]"]')?.checked;
        const accessibilityInput = dom('#inscription_form_accessibility');
        this.accessibility = !!accessibilityInput?.value;

        this.$watch('packageValues', () => this.updateAvailabilities());
        this.$watch('withDiscount', () => {
            if (this.withDiscount && this.packageValues['packageDonation']) {
                delete this.packageValues['packageDonation'];
            }
        });

        this.$watch('accessibility', () => {
            if (false === this.accessibility && accessibilityInput) {
                accessibilityInput.value = '';
            }
        });

        findAll(document, 'input[data-field-name]:checked').forEach((input) => {
            this.packageValues[input.dataset.fieldName] = input.value;
        });

        this.updateAvailabilities('boolean' === typeof props.uncheckInputs ? props.uncheckInputs : true);

        const accessibilityCheckbox = dom('#accessibility-checkbox');
        if (accessibilityCheckbox) {
            accessibilityCheckbox.checked = this.accessibility;
        }
    },

    getSignature(obj) {
        return Object.keys(obj)
            .sort()
            .map((key) => `${key}:${obj[key]}`)
            .join('|');
    },

    hasChanged() {
        return this.initialPayedAmount !== this.getTotalPrice() || this.getSignature(this.packageValues) !== this.getSignature(props.initialPackageValues || {});
    },

    updateAvailabilities(resetHiddenValues = true) {
        const activeValues = Object.values(this.packageValues).flat().map(String);

        this.packageConfig.forEach((config) => {
            const fieldKey = config.cle;
            if (!fieldKey) return;

            let isFieldVisible = true;
            if (config.dependence && Array.isArray(config.dependence) && 0 < config.dependence.length) {
                isFieldVisible = config.dependence.some((depId) => activeValues.includes(String(depId)));
            }

            this.availabilities[fieldKey] = isFieldVisible;

            if (!isFieldVisible) {
                this.allowedOptions[fieldKey] = [];
                if (resetHiddenValues && this.packageValues[fieldKey] !== undefined) {
                    delete this.packageValues[fieldKey];
                }
                return;
            }

            let validOptionIds = [];

            if (config.options && Array.isArray(config.options)) {
                config.options.forEach((option) => {
                    const optionId = 'string' === typeof option ? option : (option.id ?? option.titre);
                    const optionDeps = 'object' === typeof option && option.dependence ? option.dependence : null;

                    let isOptionVisible = true;

                    if (optionDeps && Array.isArray(optionDeps) && 0 < optionDeps.length) {
                        isOptionVisible = optionDeps.some((depId) => activeValues.includes(String(depId)));
                    }

                    if (isOptionVisible) {
                        validOptionIds.push(String(optionId));
                    }
                });
            } else {
                validOptionIds = ['__ALL__'];
            }

            this.allowedOptions[fieldKey] = validOptionIds;

            if (0 === validOptionIds.length) {
                this.availabilities[fieldKey] = false;
            }

            if (resetHiddenValues && this.packageValues[fieldKey]) {
                const currentValue = this.packageValues[fieldKey];

                if (Array.isArray(currentValue)) {
                    const newValue = currentValue.filter((val) => validOptionIds.includes(String(val)));
                    if (newValue.length !== currentValue.length) {
                        this.packageValues[fieldKey] = newValue;
                    }
                } else if (!validOptionIds.includes(String(currentValue)) && '__ALL__' !== validOptionIds[0]) {
                    delete this.packageValues[fieldKey];
                }
            }
        });
    },

    getSummaryItems() {
        const items = [];

        this.packageConfig.forEach((fieldConfig) => {
            const fieldKey = fieldConfig.cle;
            const selectedId = this.packageValues[fieldKey];

            if (!selectedId) {
                return;
            }

            const selectedOption = (fieldConfig.options || []).find((opt) => {
                if ('string' === typeof opt) {
                    return opt === selectedId;
                }

                return opt.id === selectedId;
            });

            if (selectedOption && 'object' === typeof selectedOption && selectedOption.montant) {
                items.push({
                    label: selectedOption.recap_label || selectedOption.titre,
                    price: parseFloat(selectedOption.montant),
                    type: fieldKey,
                });
            }
        });

        if (items.length && this.withDiscount) {
            const total = items.reduce((sum, item) => sum + item.price, 0);

            items.push({
                label: props.discountLabel || 'Je suis étudiant, demandeur d’emploi ou bénéficiaire des minimas sociaux',
                price: -(total - total * props.discountFactor),
            });
        }

        return items;
    },

    getTotalPrice() {
        const items = this.getSummaryItems();
        return items.reduce((sum, item) => sum + item.price, 0);
    },

    fieldsValid: {
        email: false,
        acceptCgu: false,
        acceptMedia: false,
        captcha: false,
        gender: false,
        lastName: false,
        firstName: false,
        postalCode: false,
        birthDay: false,
        birthMonth: false,
        birthYear: false,
    },
    captchaToken: null,

    setFieldValid(field) {
        return (value) => {
            this.fieldsValid[field] = value;
            return this.fieldsValid;
        };
    },

    checkValidity() {
        return Object.values(this.fieldsValid).every((x) => x);
    },

    isFromPayedToFreeUpdate() {
        return 0 < this.initialPayedAmount && 0 === this.getTotalPrice();
    },

    isFromPayedToPayedUpdate() {
        return 0 < this.initialPayedAmount && 0 < this.getTotalPrice() && this.initialPayedAmount !== this.getTotalPrice();
    },
});

export default Page;
