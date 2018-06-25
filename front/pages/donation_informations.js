import formValidator from '../validator/formValidator';

export default (formType) => {
    const form = dom('form[name="app_donation"]');

    formValidator(formType, form);
};
