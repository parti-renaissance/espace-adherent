/** @typedef  {import('alpinejs').AlpineComponent} AlpineComponent */

import reScrollTo from '../../../utils/scrollTo';

/**
 * First Step component for funnel
 * @param {{
 *   initStep?: number | null,
 *   isReContribution: boolean,
 *   steps: Object,
 * }} props
 * @returns {AlpineComponent}
 */
const Page = (props) => ({
    isReContribution: props.isReContribution,
    enabledSteps: props.steps,
    allSteps: ['step_1', 'step_2', 'step_3', 'step_4', 'legal-mention'],
    currentStep: props.initStep ?? 0,
    stepToFill: props.initStep ?? 0,
    setCurrentStep(step) {
        this.currentStep = step;
    },
    disableDispatchToStepper: false,

    handleStepperChange(step) {
        this.disableDispatchToStepper = true;
        this.currentStep = step;
        reScrollTo(`step_${step + 1}`);
    },

    isStepDisabled(step) {
        return step < this.stepToFill;
    },

    blockStep(step) {
        if ('number' === typeof step) {
            const stepsEl = Array.from(document.querySelectorAll('.re-step'));
            const parseNumberId = (id) => Number(id.split('_')[1]) - 1;
            if ([1, 2].includes(step)) {
                stepsEl.filter((el) => parseNumberId(el.id) > step)
                    .forEach((el) => {
                        el.classList.add('re-step--disabled');
                    });
            } else {
                document.querySelectorAll(`.re-step:not(#step_${step + 1})`)
                    .forEach((el) => {
                        el.classList.add('re-step--disabled');
                    });
            }
        }
    },

    retrieveLocalStorage() {
        const data = localStorage.getItem('membership_request');
        if (data) {
            const parsedData = JSON.parse(data);
            const form = document.querySelector('form[name="membership_request"]');
            Object.entries(parsedData)
                .forEach(([key, value]) => {
                    form.querySelectorAll(`[name="${key}"]`)
                        .forEach((el) => {
                            if ('radio' === el.type) {
                                el.checked = el.value === value;
                            } else if ('checkbox' === el.type) {
                                el.checked = value;
                            } else if (!el.value) {
                                if ('membership_request_address_autocomplete' === el.id) {
                                    const fulladress = parsedData['membership_request[address][address]'];
                                    if (fulladress) {
                                        el.value = 'prefilled';
                                        window[`options_${el.id}`] = [{
                                            label: fulladress,
                                            value: 'prefilled',
                                        }];
                                    }
                                } else {
                                    el.value = value;
                                }
                            }
                        });
                });
        }
    },

    init() {
        this.allSteps.forEach((stepId) => {
            if (this.enabledSteps[stepId]) {
                return;
            }

            dom(`#${stepId}`).style.display = 'none';
        });
        this.retrieveLocalStorage();
        this.blockStep(this.stepToFill);
        this.$nextTick(() => reScrollTo(`step_${this.stepToFill + 1}`));
        this.$watch('stepToFill', (value) => {
            this.blockStep(value);
            this.$nextTick(() => {
                reScrollTo(`step_${this.stepToFill + 1}`);
            });
        });
        const that = this;
        const observer = new IntersectionObserver(
            (entries) => {
                entries.forEach((entry) => {
                    const el = entry.target;
                    const stepId = el.id;
                    const stepNumber = Number(stepId.split('_')[1]) - 1;
                    if (!this.isStepDisabled(stepNumber)) {
                        el.classList.toggle('re-step--active', entry.isIntersecting);
                    }
                    if (entry.isIntersecting) {
                        if (!this.isStepDisabled(stepNumber) && !that.disableDispatchToStepper) {
                            that.$dispatch('setcurrentstep:adhesion-stepper', stepNumber);
                            this.currentStep = stepNumber;
                        }

                        if (this.currentStep === stepNumber) {
                            that.disableDispatchToStepper = false;
                        }
                    }
                });
            },
            { threshold: 0.5 }
        );
        const steps = document.querySelectorAll('.re-step');
        steps.forEach((step) => observer.observe(step));
    },

});

export default Page;
