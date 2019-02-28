#!/usr/bin/env node
'use strict';

const fs = require('fs');
const childProcess = require('child_process');

const appsDir = __dirname+'/../public/apps';

fs.readdirSync(appsDir).forEach(appName => {
    if ('example' === appName) {
        return;
    }

    const appDir = appsDir+'/'+appName;

    if (fs.statSync(appDir).isDirectory()) {
        console.log(appName);

        console.log('└─ Installing dependencies');
        childProcess.execSync('yarn', { cwd: appDir });

        console.log('└─ Building the app');
        childProcess.execSync('yarn build', { cwd: appDir });

        console.log("└─ Built!\n");
    }
});
