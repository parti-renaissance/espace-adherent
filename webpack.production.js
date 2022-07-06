const TerserJSPlugin = require('terser-webpack-plugin');
const CssMinimizerPlugin = require('css-minimizer-webpack-plugin');
const { merge } = require('webpack-merge');
const common = require('./webpack.common');

module.exports = merge(common, {
    mode: 'production',
    optimization: {
        minimizer: [
            new TerserJSPlugin({}),
            new CssMinimizerPlugin({}),
        ],
    },
});
