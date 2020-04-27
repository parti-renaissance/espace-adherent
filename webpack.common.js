const path = require('path');
const MiniCssExtractPlugin = require('mini-css-extract-plugin');
const CopyWebpackPlugin = require('copy-webpack-plugin');

module.exports = {
    context: path.join(__dirname, './front'),
    entry: {
        kernel: ['kernel'],
    },
    output: {
        path: path.join(__dirname, './web/built'),
        publicPath: '/built/',
        filename: '[name].js',
        chunkFilename: '[name].js',
    },
    module: {
        rules: [
            {
                test: /\.(html|gif|png|jpg|jpeg|ttf|otf|eot|svg|woff(2)?)(\?[a-z0-9]+)?$/,
                use: [
                    {
                        loader: 'file-loader',
                        options: {
                            name: '[name].[ext]',
                        },
                    },
                ],
            },
            {
                test: /\.scss$/,
                use: [
                    MiniCssExtractPlugin.loader,
                    'css-loader',
                    'sass-loader',
                ],
            },
            {
                test: /\.js$/,
                exclude: /node_modules/,
                parser: {
                    system: true,
                },
                use: {
                    loader: 'babel-loader',
                    options: {
                        cacheDirectory: true,
                    },
                },
            },
        ],
    },
    resolve: {
        extensions: ['.js'],
        modules: [
            path.resolve('./front'),
            'node_modules',
        ],
        alias: {
            react: path.resolve(__dirname, 'node_modules/react'),
            'react-dom': path.resolve(__dirname, 'node_modules/react-dom'),
        },
    },
    plugins: [
        new CopyWebpackPlugin([
            { from: path.resolve(__dirname, 'node_modules/select2/dist/js/select2.min.js'), to: './../select2/' },
            { from: path.resolve(__dirname, 'node_modules/select2/dist/js/i18n/fr.js'), to: './../select2/' },
            { from: path.resolve(__dirname, 'node_modules/select2/dist/css/select2.min.css'), to: './../select2/' },
            { from: path.resolve(__dirname, 'node_modules/cropperjs/dist/cropper.min.css'), to: './../css/' },
        ]),
    ],
};
