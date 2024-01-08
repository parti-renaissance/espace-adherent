/** @typedef  {import('alpinejs').AlpineComponent} AlpineComponent */

/**
 * @param {{
 *     committees: Array,
 *     defaultCommittee: string,
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
        this.setSelectCommittee(props.defaultCommittee);
    },

    /**
     * @param {string} committeeSlug
     */
    setSelectCommittee(committeeSlug) {
        this.selectedCommittee = props.committees.find((x) => x.slug === committeeSlug);
        this.committees = props.committees.filter((x) => x.slug !== committeeSlug);
        this.isOpen = false;
    },

    /**
     * @param {string} committeeSlug
     */
    handleCommitteeClick(committeeSlug) {
        this.setSelectCommittee(committeeSlug);
    },

    handleCommitteeChange(e) {
        e.preventDefault();
        this.isOpen = !this.isOpen;
    },
});

export default Form;
