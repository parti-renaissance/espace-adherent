window.Kernel = class {
    static boot(release, sentryDsn) {
        Kernel.release = release;
        Kernel.sentryDsn = sentryDsn;

        let app = false,
            vendor = false;

        let runIfReady = () => {
            if (app && vendor) {
                let sentryDsn = Kernel.sentryDsn;
                let release = Kernel.release;

                if (sentryDsn) {
                    Raven.config(sentryDsn, { release: release }).install();
                }

                App.run({
                    sentryDsn: sentryDsn,
                    release: release,
                });
            }
        };

        let handleError = (error) => {
            throw error;
        };

        System.import('vendor').catch(handleError).then(() => {
            vendor = true;
            runIfReady();
        });

        System.import('app').catch(handleError).then(() => {
            app = true;
            runIfReady();
        });
    }
};

Kernel.release = null;
Kernel.sentryDsn = null;
