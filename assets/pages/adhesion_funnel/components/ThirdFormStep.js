/** @typedef  {import('alpinejs').AlpineComponent} AlpineComponent */
import '../../../components/Validator/typedef';
import { captureException } from '@sentry/browser';
import CommonFormStep from './CommonFormStep';

const snakeToCamel = (str) => str.toLowerCase()
    .replace(/([-_][a-z])/g, (group) => group
        .replace('-', '')
        .replace('_', ''));

/**
 * First Step component for funnel
 * @return {AlpineComponent}
 */
const ThirdForm = () => ({
    ...CommonFormStep(),
    nextStepId: 'step_4',
    defaultNextStepId: 'step_4',
    id: 'step_3',
    fieldsValid: {
        exclusiveMembership: false,
        partyMembership: true,
        isPhysicalPerson: false,
    },
    notExclusiveMember: false,
    isMemberOfAnotherParty: false,

    init() {
        const notExclusiveMember = document.querySelector('#membership_request_exclusiveMembership_1');
        this.notExclusiveMember = notExclusiveMember ? notExclusiveMember.checked : false;

        const isMemberOfAnotherParty = document.querySelector('#membership_request_partyMembership_2');
        this.isMemberOfAnotherParty = isMemberOfAnotherParty ? isMemberOfAnotherParty.checked : false;
        this.$nextTick(() => {
            window.isExlusiveMember = this.getIsExclusiveMember.bind(this);
        });
    },

    handleExclusiveMembershipChange(e) {
        const { value } = e.target;
        if ('1' === value || '0' === value) {
            const bool = !Number(value);
            this.notExclusiveMember = bool;
            this.fieldsValid.partyMembership = !bool;
        }
    },

    handlePartyMembershipChange(e) {
        const { value } = e.target;
        this.isMemberOfAnotherParty = '3' === value;
    },

    getIsExclusiveMember() {
        return !this.notExclusiveMember;
    },

    createAccount(data) {
        this.loading = true;
        return fetch('/api/create-account', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                ...data,
                utm_source: document.querySelector('#membership_request_utmSource').value,
                utm_campaign: document.querySelector('#membership_request_utmCampaign').value,
            }),
        });
    },

    saveFormToLocalStorage() {
        const form = document.querySelector('form[name="membership_request"]');
        const formData = new FormData(form);
        const data = Object.fromEntries(formData.entries());
        localStorage.setItem('membership_request', JSON.stringify(data));
    },
    _handleBadRequest(data) {
        data.violations.forEach((x) => {
            const prop = x.property.startsWith('address') ? `address_${snakeToCamel(x.property)}` : snakeToCamel(x.property);
            this.$dispatch(`x-validate:membership_request_${prop}`, {
                status: data.status,
                message: x.message,
            });
        });
    },

    async handleOnSubmit(e) {
        e.preventDefault();
        if (!this._handleOnSubmitBase(e)) {
            return;
        }

        if (this.notExclusiveMember && this.isMemberOfAnotherParty) {
            this.nextStepId = 'fake_email_validation';
        } else {
            this.nextStepId = this.defaultNextStepId;
        }

        // this.saveFormToLocalStorage();

        this.setStepData([], (name, value) => {
            if (['exclusiveMembership'].includes(name)) {
                return !!Number(value);
            }
            if (['partyMembership'].includes(name)) {
                return Number(value);
            }
            return value;
        });

        await this.createAccount(this.formData)
            // eslint-disable-next-line consistent-return
            .then((res) => res.json())
            .then((payload) => {
                if (this.isNotifResponse(payload)) {
                    if ('success' === payload.status) {
                        this.stepToFill = 3;
                        this.handleNextStep();
                        return;
                    }
                    if ('redirect' === payload.status) {
                        window.location.href = payload.location;
                        return;
                    }
                    if (payload.violations) {
                        this._handleBadRequest(payload);
                        this.scrollToFirstError();
                        return;
                    }
                    this.generalNotification = payload;
                    if ('error' === payload.status) {
                        this.scrollToFirstError();
                    }
                } else {
                    throw new Error('Invalid response');
                }
            })
            .catch((err) => {
                this.generalNotification = {
                    status: 'error',
                    message: 'Une erreur est survenue lors de la création de votre compte',
                };
                this.scrollToFirstError();
                captureException(err, {
                    tags: {
                        component: 'membership-request',
                        step: 'create-account',
                    },
                });
            })
            .finally(() => {
                this.loading = false;
            });
    },

});

export default ThirdForm;
