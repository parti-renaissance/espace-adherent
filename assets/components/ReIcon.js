// type xReIcon with jsdoc as Alpine.data second argument
/**
 * @param {{type: string, currentSvg:string}} state
 */
const xReIcon = (state) => ({
    async init() {
        this.$nextTick(async () => {
            if (null == this.currentSvg && 'default' !== this.type) {
                await this.getSvg(state.type);
            }
        });
    },
    currentSvg: null,
    type: state.type,
    getNameIcon(type) {
        if (type.startsWith('arrow')) return 'arrow';
        if (type.startsWith('star')) return 'star';
        return type;
    },
    getUrl(type) {
        return `/images/icons/re-icons/re-${this.getNameIcon(type)}.svg`;
    },
    async getSvg(type) {
        if ('default' === type) return null;
        const url = this.getUrl(type);
        return fetch(url).then((response) => response.text());
    },

});

export default {
    xReIcon,
};
