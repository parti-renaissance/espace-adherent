const webpack = require('webpack');
const path = require('path');
const ExtractTextPlugin = require('extract-text-webpack-plugin');
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
        loaders: [
            {
                test: /\.(html|gif|png|jpg|jpeg|ttf|otf|eot|svg|woff(2)?)(\?[a-z0-9]+)?$/,
                loader: 'file-loader',
                query: { name: '[name].[ext]' },
            },
            {
                test: /\.scss$/,
                loaders: ExtractTextPlugin.extract({
                    fallbackLoader: 'style-loader',
                    loader: 'css-loader!sass-loader',
                }),
            },
            {
                test: /\.js$/,
                exclude: /node_modules/,
                loaders: [
                    {
                        loader: 'babel-loader',
                        query: { cacheDirectory: true },
                    },
                ],
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
        new webpack.DefinePlugin({
            'process.env': { NODE_ENV: JSON.stringify('development') },
        }),
        new webpack.LoaderOptionsPlugin({
            minimize: false,
            debug: true,
        }),
        new ExtractTextPlugin({
            filename: 'app.css',
            allChunks: true,
        }),
        new CopyWebpackPlugin([
            { from: './../node_modules/select2/dist/js/select2.min.js', to: './../select2/' },
            { from: './../node_modules/select2/dist/js/i18n/fr.js', to: './../select2/' },
            { from: './../node_modules/select2/dist/css/select2.min.css', to: './../select2/' },
        ]),
    ],
};
