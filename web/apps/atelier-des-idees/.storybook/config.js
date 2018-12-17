import { configure } from '@storybook/react';
import { addDecorator } from '@storybook/react';
import { withTests } from '@storybook/addon-jest';
import results from '../.jest-test-results.json';
import '../src/App.css';

// load stories dynamically
const req = require.context('../src', true, /\.stories\.js$/);
function loadStories() {
    req.keys().forEach(filename => req(filename));
}

// load tests results dynamically
addDecorator(
    withTests({
        results,
    })
);

configure(loadStories, module);
