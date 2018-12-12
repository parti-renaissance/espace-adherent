import { configure } from '@storybook/react';

// load stories dynamically
const req = require.context('../src', true, /\.stories\.js$/);
function loadStories() {
    req.keys().forEach(filename => req(filename));
}

configure(loadStories, module);
