/** @typedef  {import('alpinejs').AlpineComponent} AlpineComponent */

/**
 * @param {{
 *     committees: Array,
 *     defaultCommittee: Object,
 * }} props
 * @returns {AlpineComponent}
 */
const Form = (props) => ({
    isOpen: true,
    committees: null,
    selectedCommittee: null,
    setFieldValid(field) {
        return (value) => {
            this.fieldsValid[field] = value;
            return this.fieldsValid;
        };
    },

    init() {
        this.selectedCommittee = props.defaultCommittee;
        this.committees = props.committees.filter((x) => x.uuid !== props.defaultCommittee.uuid);
        this.isOpen = false;
    },

    /**
     * @param {string} committeeUuid
     */
    setSelectCommittee(committeeUuid) {
        this.selectedCommittee = props.committees.find((x) => x.uuid === committeeUuid);
        this.committees = props.committees.filter((x) => x.uuid !== committeeUuid);
        this.isOpen = false;
    },

    /**
     * @param {string} committeeUuid
     */
    handleCommitteeClick(committeeUuid) {
        this.setSelectCommittee(committeeUuid);
    },

    handleCommitteeChange(e) {
        e.preventDefault();
        this.isOpen = !this.isOpen;
    },
});

export default Form;
