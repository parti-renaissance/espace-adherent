export default class Container {
    constructor(values) {
        this._values = {};
        this._keys = {};
        this._instances = {};

        Object.keys(values || {}).forEach((key) => {
            this.set(key, values[key]);
        });
    }

    set(key, callback) {
        this._values[key] = callback;
        this._keys[key] = true;
    }

    get(key) {
        if ('undefined' === typeof this._keys[key]) {
            throw new ReferenceError(`Identifier ${key} is not defined.`);
        }

        if ('function' !== typeof this._values[key]) {
            return this._values[key];
        }

        if ('undefined' === typeof this._instances[key]) {
            this._instances[key] = this._values[key]();
        }

        return this._instances[key];
    }

    has(key) {
        return 'undefined' !== typeof this._keys[key];
    }
}
