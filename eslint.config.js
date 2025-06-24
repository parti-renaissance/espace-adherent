import js from '@eslint/js';
import eslintRecommended from 'eslint-config-airbnb-base';
import prettier from 'eslint-config-prettier';

export default [
    js.configs.recommended,
    eslintRecommended,
    prettier,
    {
        files: ['**/*.js'],
        languageOptions: {
            ecmaVersion: 2022,
            sourceType: 'module',
        },
        linterOptions: {
            reportUnusedDisableDirectives: true,
        },
        rules: {
            indent: ['error', 4, { SwitchCase: 1 }],
            quotes: ['error', 'single', { avoidEscape: true }],
            'no-var': 'error',
            'no-underscore-dangle': 'off',
            semi: ['error', 'always'],
            'no-trailing-spaces': 'error',
            'eol-last': 'error',
            yoda: ['error', 'always'],
            'max-len': ['error', { code: 180, ignoreStrings: true }],
            'no-param-reassign': 'off',
            'no-unused-vars': 'off',
            'class-methods-use-this': 'off',
            'arrow-parens': ['error', 'always'],
            'default-case': 'off',
            'comma-dangle': [
                'error',
                {
                    arrays: 'always-multiline',
                    objects: 'always-multiline',
                    imports: 'always-multiline',
                    exports: 'always-multiline',
                    functions: 'never',
                },
            ],
        },
    },
];
