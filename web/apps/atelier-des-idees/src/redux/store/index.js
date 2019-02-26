// Incomment before Merge

if ('production' === process.env.NODE_ENV) {
    module.exports = require('./configStore.prod');
} else {
    module.exports = require('./configStore.dev');
}

// Comment before Merge
// Activate REDUX_DEVTOOLS_EXTENSION_COMPOSE in symfony dev env

// module.exports = require('./configStore.dev');
