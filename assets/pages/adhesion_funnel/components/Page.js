/** @typedef  {import('alpinejs').AlpineComponent} AlpineComponent */
import { reScrollTo } from '../utils';

const camelToSnakeCase = (str) => str.replace(/[A-Z]/g, (letter) => `_${letter.toLowerCase()}`);

/**
 * First Step component for funnel
 * @param {{
 *   initStep?: number | null,
 * }} props
 * @returns {AlpineComponent}
 */
const Page = (props) => ({
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
            document.querySelectorAll(`.re-step:not(#step_${step + 1})`)
                .forEach((el) => {
                    el.classList.add('re-step--disabled');
                });
        }
    },

    init() {
        if (2 < this.stepToFill) {
            this.blockStep(this.stepToFill);
        }
        this.$nextTick(() => {
            reScrollTo(`step_${this.currentStep + 1}`);
        });
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
