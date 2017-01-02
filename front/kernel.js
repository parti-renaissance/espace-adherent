export default class Kernel {
    static boot(release, sentryDsn, callback) {
        Kernel._import((app, vendor) => {
            if (sentryDsn) {
                vendor.Raven.config(sentryDsn, { release: release }).install();
            }

            callback(app);
        });
    }

    static _import(callback) {
        let callCallbackIfReady = () => {
            if (Kernel.app && Kernel.vendor) {
                Kernel.app.global();
                callback(Kernel.app, Kernel.vendor);
            }
        };

        System.import('vendor').catch(Kernel._handleError).then((module) => {
            Kernel.vendor = module.default;
            callCallbackIfReady();
        });

        System.import('app').catch(Kernel._handleError).then((module) => {
            let App = module.default;

            Kernel.app = new App();
            callCallbackIfReady();
        });
    }

    static _handleError(err) {
        throw err;
    }
}

Kernel.app = null;
Kernel.vendor = null;

window.Kernel = Kernel;
