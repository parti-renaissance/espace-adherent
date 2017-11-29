/*
 * Citizen Project creation page
 */
export default() => {
    const assistanceNeededCheckbox = dom('#citizen_project_assistance_needed');
    const assistanceContentField = dom('#citizen_project_assistance_content');

    const onChange = (event) => {
        if (event.target.checked) {
            removeClass(assistanceContentField.parentElement, 'hidden');
        } else {
            addClass(assistanceContentField.parentElement, 'hidden');
        }
    };

    on(assistanceNeededCheckbox, 'change', onChange);

    assistanceNeededCheckbox.dispatchEvent(new Event('change'));
};
