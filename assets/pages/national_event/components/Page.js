/** @typedef  {import('alpinejs').AlpineComponent} AlpineComponent */

/**
 * First Step component for funnel
 *
 * @param {{
 *   transportConfig?: Object | null,
 *   uncheckInputs?: boolean | null,
 *   transport?: string | null,
 *   accommodation?: string | null,
 * }} props
 *
 * @returns {AlpineComponent}
 */
const Page = (props) => ({
    accessibility: false,
    transportConfig: props.transportConfig || null,
    visitDay: null,
    availableTransports: [],
    availableAccommodations: [],
    transport: props.transport || null,
    accommodation: props.accommodation || null,
    initialTransport: props.transport || null,
    initialAccommodation: props.accommodation || null,
    selectedTransportConfig: null,
    selectedAccommodationConfig: null,
    withDiscount: false,

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
        });

        this.$watch('transport', () => {
            this.selectedTransportConfig = this.getSelectedTransportConfig();
        });
        this.$watch('accommodation', () => {
            this.selectedAccommodationConfig = this.getSelectedAccommodationConfig();
        });
        this.visitDay = dom('input[name*="[visitDay]"]:checked')?.value || null;
        this.accessibility = !!dom('#campus_event_inscription_accessibility')?.value;
        const accessibilityCheckbox = dom('#accessibility-checkbox');
        if (accessibilityCheckbox) {
            accessibilityCheckbox.checked = this.accessibility;
        }

        const uncheckInputs = 'boolean' === typeof props.uncheckInputs ? props.uncheckInputs : true;

        this.updateAvailableTransports(uncheckInputs);
        this.updateAvailableAccommodations(uncheckInputs);
    },

    handleAccessibilityChange(e) {
        this.accessibility = e.target.checked;
        if (false === this.accessibility) {
            document.querySelector('#campus_event_inscription_accessibility').value = '';
        }
    },

    handleVisitDayChange(e) {
        this.visitDay = e.target.value;
        this.updateAvailableTransports();
        this.updateAvailableAccommodations();
    },

    updateAvailableTransports(uncheckInputs = true) {
        this.availableTransports = [];
        this.selectedTransportConfig = null;

        if (!this.visitDay || !this.transportConfig?.transports) {
            return;
        }

        this.availableTransports = (this.transportConfig.transports ?? []).filter(
            (transport) => transport.jours_ids.includes(this.visitDay)
        );

        if (uncheckInputs) {
            this.transport = null;
            findAll(document, 'input[name*="[transport]"]').forEach((input) => {
                input.checked = false;
            });
        }
    },

    updateAvailableAccommodations(uncheckInputs = true) {
        this.availableAccommodations = [];
        this.selectedAccommodationConfig = null;

        if (!this.visitDay || !this.transportConfig?.hebergements) {
            return;
        }

        this.availableAccommodations = (this.transportConfig.hebergements ?? []).filter(
            (accommodation) => accommodation.jours_ids.includes(this.visitDay)
        );

        if (uncheckInputs) {
            this.accommodation = null;
            findAll(document, 'input[name*="[accommodation]"]').forEach((input) => {
                input.checked = false;
            });
        }
    },

    getSelectedTransportConfig() {
        if (!this.transport || this.transport.startsWith('gratuit')) {
            return null;
        }

        const configs = (this.transportConfig?.transports ?? []).filter(
            (transport) => transport.id === this.transport
        );

        return 0 < configs.length ? configs[0] : null;
    },

    getSummaryItems() {
        const items = [];

        if (this.selectedTransportConfig && !!this.selectedTransportConfig.montant) {
            items.push({
                label: this.selectedTransportConfig.recap_label,
                price: this.selectedTransportConfig.montant,
            });
        }

        if (this.selectedAccommodationConfig && !!this.selectedAccommodationConfig.montant) {
            items.push({
                label: this.selectedAccommodationConfig.recap_label,
                price: this.selectedAccommodationConfig.montant,
            });
        }

        if (items.length && this.withDiscount) {
            const total = items.reduce((sum, item) => sum + item.price, 0);

            items.push({
                label: 'Je suis étudiant, demandeur d’emploi ou bénéficiaire des minimas sociaux',
                price: -total * 0.5,
            });
        }

        return items;
    },

    getTotalPrice() {
        const items = this.getSummaryItems();
        return items.reduce((sum, item) => sum + item.price, 0);
    },

    getSelectedAccommodationConfig() {
        if (!this.accommodation || this.accommodation.startsWith('gratuit')) {
            return null;
        }

        const configs = (this.transportConfig?.hebergements ?? []).filter(
            (accommodation) => accommodation.id === this.accommodation
        );

        return 0 < configs.length ? configs[0] : null;
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
        return Object.values(this.fieldsValid)
            .every((x) => x);
    },
});

export default Page;
