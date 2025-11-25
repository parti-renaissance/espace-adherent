/** @typedef  {import('alpinejs').AlpineComponent} AlpineComponent */
import '../../../components/Validator/typedef';

/**
 * First Step component for funnel
 * @param {{price: number, onChange?: (p:number)=>void}} props
 * @return {AlpineComponent}
 */
const ReContributionOpt = (props) => ({
    init() {
        this.sliderValue = this.reverseLogSlider(props.price);
    },
    onChange() {
        if (props.onChange) {
            props.onChange(this.price);
        }
    },
    price: props.price,
    sliderValue: 0,
    onInputChange(_value) {
        const target = document.querySelector('#custom-price-input');
        const value = Math.min(Math.max(_value, 50), 7500);
        const event = new CustomEvent('custom-price-input:set-value', {
            detail: this.reverseLogSlider(value),
        });
        target.dispatchEvent(event);
        this.price = value;
        if (props.onChange) props.onChange(this.price);
    },

    /**
     * @param {InputEvent} e
     */
    handleFocus: (e) => {
        e.currentTarget.select();
    },

    logSlider(position) {
        const minp = 0;
        const maxp = 100;

        // The result should be between 100 an 10000000
        const minv = Math.log(50);
        const maxv = Math.log(7500);

        // calculate adjustment factor
        const scale = (maxv - minv) / (maxp - minp);

        return Math.round(Math.exp(minv + scale * (position - minp)));
    },

    reverseLogSlider(value) {
        // position will be between 0 and 100
        const minp = 0;
        const maxp = 100;

        // The result should be between 100 an 10000000
        const minv = Math.log(50);
        const maxv = Math.log(7500);

        // calculate adjustment factor
        const scale = (maxv - minv) / (maxp - minp);

        return (Math.log(value) - minv) / scale + minp;
    },
});

export default ReContributionOpt;
