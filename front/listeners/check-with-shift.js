/*
 * Check multiple checkboxes with Shift.
 * No need to use with UserListDefinitionWidget, it takes it into account automatically.
 */
export default () => {
    const checkboxes = findAll(document, '.check-with-shift');
    let lastChecked = null;

    checkboxes.forEach((checkbox) => {
        on(checkbox, 'click', (event) => {
            if (!lastChecked) {
                lastChecked = checkbox;

                return;
            }

            if (event.shiftKey) {
                const start = $(checkboxes).index(checkbox);
                const end = $(checkboxes).index(lastChecked);

                $(checkboxes)
                    .slice(Math.min(start, end), Math.max(start, end) + 1)
                    .prop('checked', lastChecked.checked);
                $(checkboxes).slice(Math.min(start, end), Math.max(start, end) + 1).each((key, element) => {
                    $(element).trigger('change');
                });
            }

            lastChecked = checkbox;
        });
    });
};
