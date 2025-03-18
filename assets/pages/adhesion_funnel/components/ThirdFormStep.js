/** @typedef  {import('alpinejs').AlpineComponent} AlpineComponent */
import '../../../components/Validator/typedef';
import CommonFormStep from './CommonFormStep';
import { handlePostAccountResponse, postAccount } from '../shared/utils';

/**
 * First Step component for funnel
 *
 * @param {{
 *   pid?: string | null,
 *   referral?: string | null,
 * }} props
 *
 * @return {AlpineComponent}
 */
const ThirdForm = (props) => ({
    ...CommonFormStep(),
    pid: props.pid ? props.pid : null,
    referral: props.referral ? props.referral : null,
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
        return postAccount({...data, referrer: this.pid, referral: this.referral});
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
            .then((res) => handlePostAccountResponse.call(this, res, (payload) => {
                this.stepToFill = 3;
                this.handleNextStep();
                this.clearLocalStorage();
            }));
    },

});

export default ThirdForm;
