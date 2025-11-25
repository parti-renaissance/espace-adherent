/** @typedef  {import('alpinejs').AlpineComponent} AlpineComponent */
import reScrollTo from '../../../utils/scrollTo';

/**
 * First Step component for funnel
 * @param {{
 *   initStep?: number | null,
 *   connectUrl: string,
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
            const stepsEl = Array.from(document.querySelectorAll('.re-step'));
            const parseNumberId = (id) => Number(id.split('_')[1]) - 1;
            if ([1, 2].includes(step)) {
                stepsEl
                    .filter((el) => parseNumberId(el.id) > step)
                    .forEach((el) => {
                        el.classList.add('re-step--disabled');
                    });
            } else {
                document.querySelectorAll(`.re-step:not(#step_${step + 1})`).forEach((el) => {
                    el.classList.add('re-step--disabled');
                });
            }
        }
    },

    init() {
        this.$nextTick(() => {
            const firstError = document.querySelector('[data-status="error"]');
            if (firstError) {
                const stepNumber = Number(firstError.closest('.re-step').id.split('_')[1]) - 1;
                this.blockStep(stepNumber);
                firstError.scrollIntoView({
                    behavior: 'smooth',
                    block: 'center',
                    inline: 'nearest',
                });
            } else {
                if (0 === this.stepToFill) {
                    window.scrollTo({
                        top: 0,
                        behavior: 'smooth',
                    });
                } else {
                    reScrollTo(`step_${this.stepToFill + 1}`);
                }
                this.blockStep(this.stepToFill);
            }
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
