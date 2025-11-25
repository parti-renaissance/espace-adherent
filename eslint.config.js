/* eslint-disable import/no-extraneous-dependencies */
const { FlatCompat } = require('@eslint/eslintrc');
const js = require('@eslint/js');
const globals = require('globals');
const react = require('eslint-plugin-react');
const prettierPlugin = require('eslint-plugin-prettier');
const prettierConfig = require('eslint-config-prettier');

const compat = new FlatCompat({
    baseDirectory: __dirname,
    recommendedConfig: js.configs.recommended,
    allConfig: js.configs.all,
});

const customGlobals = {
    dom: 'readonly',
    findOne: 'readonly',
    findAll: 'readonly',
    startsWith: 'readonly',
    on: 'readonly',
    once: 'readonly',
    off: 'readonly',
    insertAfter: 'readonly',
    remove: 'readonly',
    show: 'readonly',
    hide: 'readonly',
    addClass: 'readonly',
    hasClass: 'readonly',
    toggleClass: 'readonly',
    removeClass: 'readonly',
    App: 'readonly',
    Kernel: 'readonly',
    Cookies: 'readonly',
    reqwest: 'readonly',
    Main: 'readonly',
    Bootstrap: 'readonly',
    google: 'readonly',
    getUrlParameter: 'readonly',
    friendlyChallenge: 'readonly',
};

module.exports = [
    js.configs.recommended,

    ...compat.extends('airbnb-base'),

    {
        files: ['**/*.js', '**/*.jsx'],
        plugins: {
            react,
        },
        languageOptions: {
            parserOptions: {
                ecmaFeatures: { jsx: true },
            },
        },
        settings: {
            react: { version: 'detect' },
        },
        rules: {
            ...react.configs.recommended.rules,
        },
    },

    prettierConfig,

    {
        files: ['**/*.js', '**/*.jsx'],

        plugins: {
            prettier: prettierPlugin,
        },

        languageOptions: {
            ecmaVersion: 'latest',
            sourceType: 'module',
            globals: {
                ...globals.browser,
                ...globals.jquery,
                ...customGlobals,
            },
        },

        settings: {
            'import/resolver': {
                webpack: {
                    config: 'webpack.development.js',
                },
            },
        },

        rules: {
            'prettier/prettier': 'error',

            'no-var': 'error',
            'no-underscore-dangle': 'off',
            'no-param-reassign': 'off',
            'class-methods-use-this': 'off',
            'default-case': 'off',
            'import/no-named-as-default': 'off',
            'import/no-named-as-default-member': 'off',

            yoda: ['error', 'always'],

            'no-unused-vars': [
                'warn',
                {
                    argsIgnorePattern: '^_',
                    varsIgnorePattern: '^_',
                },
            ],
        },
    },
];
