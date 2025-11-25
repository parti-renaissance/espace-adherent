// type xReIcon with jsdoc as Alpine.data second argument
/** @typedef  {import('alpinejs').AlpineComponent} AlpineComponent */

import { range } from 'lodash';

/**
 * @param {{
 * min: number,
 * max: number,
 * onChange: (value: number) => void,
 * value: number,
 * stepBy: number|null
 * step: number|null,
 * pipe: (value: number) => number
 * syncValue: string
 * }} props
 * @returns {AlpineComponent}
 */
const xReSlider = ({ onChange, pipe, syncValue, ...props }) => {
    const _defaultStep = props.step ?? (props.stepBy ? (props.max - props.min) / props.stepBy : 1);
    return {
        ...props,
        defaultStep: _defaultStep,
        step: _defaultStep,

        init() {
            this.$el.value = _defaultStep;
        },

        /**
         * @param {InputEvent} e
         */
        onChange: (e) => {
            const _value = pipe ? pipe(e.target.value) : e.target.value;
            onChange(_value);
        },

        setValue(value) {
            this.step = 0;
            this.value = !value || value < props.min ? props.min : value;
        },

        getValue() {
            if (pipe) return pipe(props.value);
            return props.value;
        },

        getSeparators() {
            if (!props.stepBy) return [];
            return range(0, props.stepBy);
        },
    };
};

export default {
    xReSlider,
};
