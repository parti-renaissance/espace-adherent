const fs = require('fs');
const path = require('path');
const MiniCssExtractPlugin = require('mini-css-extract-plugin');
const TerserJSPlugin = require('terser-webpack-plugin');
const OptimizeCSSAssetsPlugin = require('optimize-css-assets-webpack-plugin');
const { merge } = require('webpack-merge');
const common = require('./webpack.common');

module.exports = merge(common, {
    mode: 'production',
    optimization: {
        minimizer: [
            new TerserJSPlugin({}),
            new OptimizeCSSAssetsPlugin({}),
        ],
    },
    plugins: [
        (compiler) => {
            compiler.hooks.done.tap('save-assets-version', (stats) => {
                fs.writeFile(
                    path.join(__dirname, 'config/packages/prod/', 'assets_version.yaml'),
                    `parameters:\n    assets_hash: ${stats.hash}\n`,
                    (err) => {
                        if (err) throw err;

                        fs.copyFile(
                            path.resolve(__dirname, 'config/packages/prod/assets_version.yaml'),
                            path.resolve(__dirname, 'config/packages/test/assets_version.yaml'),
                            (e) => { if (e) throw e; }
                        );
                    }
                );
            });
        },
    ],
});
