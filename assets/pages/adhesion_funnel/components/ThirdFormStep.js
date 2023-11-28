/** @typedef  {import('alpinejs').AlpineComponent} AlpineComponent */
import '../../../components/Validator/typedef';
import CommonFormStep from './CommonFormStep';

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
        this.$nextTick(() => {
            window.notExlusiveMember = this.getIsNotExclusiveMember.bind(this);
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

    getIsNotExclusiveMember() {
        return this.notExclusiveMember;
    },

    saveFormToLocalStorage() {
        const form = document.querySelector('form[name="membership_request"]');
        const formData = new FormData(form);
        const data = Object.fromEntries(formData.entries());
        localStorage.setItem('membership_request', JSON.stringify(data));
    },

    async handleOnSubmit(e) {
        if (!this._handleOnSubmitBase(e)) {
            return;
        }

        if (this.notExclusiveMember && this.isMemberOfAnotherParty) {
            this.nextStepId = 'fake_email_validation';
        } else {
            this.nextStepId = this.defaultNextStepId;
        }

        this.saveFormToLocalStorage();

        this.handleNextStep.call(this);
    },

});

export default ThirdForm;
