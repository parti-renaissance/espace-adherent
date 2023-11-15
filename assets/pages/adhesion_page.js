export default () => {
    const emailInput = dom('#membership_request_email');
    on(emailInput, 'change', (event) => {
        console.log(event.target.value);
    });
};
