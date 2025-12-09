import js from '@eslint/js';
import globals from 'globals';
import react from 'eslint-plugin-react';
import prettierPlugin from 'eslint-plugin-prettier';
import prettierConfig from 'eslint-config-prettier';
import importPlugin from 'eslint-plugin-import';

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

export default [
    js.configs.recommended,

    prettierConfig,

    {
        files: ['**/*.js', '**/*.jsx'],

        plugins: {
            react,
            prettier: prettierPlugin,
            import: importPlugin,
        },

        languageOptions: {
            ecmaVersion: 'latest',
            sourceType: 'module',
            parserOptions: {
                ecmaFeatures: { jsx: true },
            },
            globals: {
                ...globals.browser,
                ...globals.jquery,
                ...customGlobals,
            },
        },

        settings: {
            react: { version: 'detect' },
            'import/resolver': {
                webpack: {
                    config: 'webpack.development.js',
                },
            },
        },

        rules: {
            ...react.configs.recommended.rules,

            'prettier/prettier': 'error',

            'no-var': 'error',
            'no-underscore-dangle': 'off',
            'no-param-reassign': 'off',
            'class-methods-use-this': 'off',
            'default-case': 'off',

            'import/no-named-as-default': 'off',
            'import/no-named-as-default-member': 'off',
            'import/no-extraneous-dependencies': 'off',

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
