const merge = require('webpack-merge');
const MiniCssExtractPlugin = require('mini-css-extract-plugin');
const common = require('./webpack.common');

module.exports = merge(common, {
    mode: 'development',
    plugins: [
        new MiniCssExtractPlugin({
            filename: '[name].css',
            chunkFilename: 'app.css',
        }),
    ],
});
