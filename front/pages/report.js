/*
 * Report form listener
 */
export default() => {
    const otherReasonCheckbox = dom('#report_command_reasons_other');
    const commentContentField = dom('#report_command_comment');

    const onChange = (event) => {
        if (event.target.checked) {
            removeClass(commentContentField.parentElement, 'hidden');
        } else {
            addClass(commentContentField.parentElement, 'hidden');
            commentContentField.value = '';
        }
    };

    on(otherReasonCheckbox, 'change', onChange);

    otherReasonCheckbox.dispatchEvent(new Event('change'));
};
