/*
 * Manage the participants of event/citizen action
 */
export default() => {
    const allCheckboxes = dom('#members-check-all');
    const selectionCheckboxes = findAll(document, 'input[name="members[]"]');
    const contactButton = dom('#members-contact-button');
    const contactSelection = dom('#members-contact-selection');
    const exportButton = dom('#members-export-button');
    const exportSelection = dom('#members-export-selection');
    const printButton = dom('#members-print-button');
    const printSelection = dom('#members-print-selection');

    function getSelectedMembers() {
        const members = [];

        selectionCheckboxes.forEach((element) => {
            if (element.checked) {
                members.push(element.value);
            }
        });

        return members;
    }

    // Toggle action buttons
    function toggleButtons(value) {
        if (null != contactButton) contactButton.disabled = !value;
        if (null != exportButton) exportButton.disabled = !value;
        if (null != printButton) printButton.disabled = !value;
    }

    // Toggle each 'selection' checkbox
    function toggleCheckboxes(value) {
        selectionCheckboxes.forEach((element) => {
            element.checked = !!value;
        });
    }

    // Bind the 'select all' checkbox
    const bindAllCheckboxes = (event) => {
        toggleCheckboxes(allCheckboxes.checked);
        toggleButtons(allCheckboxes.checked);
    };

    on(allCheckboxes, 'change', bindAllCheckboxes);
    allCheckboxes.dispatchEvent(new Event('change'));

    const toggle = (event) => {
        let all = true;

        // Default behaviour
        toggleButtons(false);
        allCheckboxes.checked = false;

        const toggleAllButtons = (elem) => {
            if (elem.checked) {
                // Enable button if at least one selection checkbox is checked
                toggleButtons(true);
            } else {
                all = false;
            }
        };

        selectionCheckboxes.forEach(toggleAllButtons);

        // When all selection checkbox are checked, also check the 'select all' checkbox
        if (all) {
            allCheckboxes.checked = true;
        }
    };

    // Bind each 'selection' checkbox
    selectionCheckboxes.forEach((element) => {
        on(element, 'change', toggle);
        allCheckboxes.dispatchEvent(new Event('change'));
    });

    const exportMembers = (event) => {
        exportSelection.value = JSON.stringify(getSelectedMembers());
    };

    // Bind the export button (build a Json list of members to export)
    if (null != exportButton) {
        on(exportButton, 'click', exportMembers);
        exportButton.dispatchEvent(new Event('click'));
    }

    const contactMembers = (event) => {
        contactSelection.value = JSON.stringify(getSelectedMembers());
    };

    // Bind the export button (build a Json list of members to export)
    if (null != contactButton) {
        on(contactButton, 'click', contactMembers);
        contactButton.dispatchEvent(new Event('click'));
    }

    const printMembers = (event) => {
        printSelection.value = JSON.stringify(getSelectedMembers());
    };

    // Bind the print button (build a Json list of members to print)
    if (null != printButton) {
        on(printButton, 'click', printMembers);
        printButton.dispatchEvent(new Event('click'));
    }
};
