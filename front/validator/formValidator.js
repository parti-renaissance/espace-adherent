import reqwest from 'reqwest';

let idErrors = [];

function displayErrors(node, id) {
    if (Object.prototype.hasOwnProperty.call(node, 'children')) {
        Object.keys(node.children).forEach((key) => {
            displayErrors(node.children[key], `${id}_${key}`);
        });
    }

    let htmlErrors = dom(`#${id}_errors`);

    if (Object.prototype.hasOwnProperty.call(node, 'errors')) {
        if (!htmlErrors) {
            const htmlInput = dom(`#${id}`);

            if (!htmlInput) {
                return;
            }
            htmlInput.parentNode.insertAdjacentHTML(
                'beforeend',
                `<ul id="${id}_errors" class="form form__errors"></ul>`
            );
            htmlErrors = dom(`#${id}_errors`);
        }

        idErrors.push(`${id}_errors`);
        htmlErrors.innerHTML = '';
        node.errors.forEach((value) => {
            htmlErrors.insertAdjacentHTML('beforeend', `<li class="form__error">${value}</li>`);
        });
    } else if (htmlErrors) {
        htmlErrors.parentNode.removeChild(htmlErrors);
    }
}

export default (formType, form) => {
    formType = encodeURI(formType);
    form.addEventListener('change', () => {
        const url = `/api/form/validate/${formType}`;
        const formData = new FormData(form);
        const emptyFieldNames = [];

        for (const field of formData.entries()) {
            if (!field[1]) {
                emptyFieldNames.push(field[0]);
            } else {
                const targetField = form.querySelector(`[name="${field[0]}"`);
                const id = targetField.dataset.validatedWith;

                if (id) {
                    for (const element of findAll(document, `[id^="${id}"]`)) {
                        if (!element.value) {
                            emptyFieldNames.push(field[0]);
                            break;
                        }
                    }
                }
            }
        }

        for (const fieldName of emptyFieldNames) {
            formData.delete(fieldName);
        }

        reqwest({
            url,
            method: 'post',
            data: formData,
            processData: false,
            success(resp) {
                for (const idError of idErrors) {
                    const htmlErrors = dom(`#${idError}`);
                    htmlErrors.parentNode.removeChild(htmlErrors);
                }
                idErrors = [];
                displayErrors(resp, form.name);
            },
        });
    });
};
