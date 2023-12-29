const xReIcon = () => ({
    getIconId(type) {
        if (type.startsWith('arrow')) return 'arrow';
        if (type.startsWith('star')) return 'star';
        return type;
    },
    getClassName(type) {
        return `re-icon-${type.startsWith('loading') ? 'loading' : type}`;
    },
    getUrl(type) {
        return `#re-icon-${this.getIconId(type)}`;
    },
});

export default { xReIcon };
