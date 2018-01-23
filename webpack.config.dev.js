const webpack = require('webpack');
const path = require('path');
const ExtractTextPlugin = require('extract-text-webpack-plugin');
const ExtractTextPluginForPrint = new ExtractTextPlugin({
    filename: 'print.css',
    allChunks: true,
});
const ExtractTextPluginForApp = new ExtractTextPlugin({
    filename: 'app.css',
    allChunks: true,
});

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
                loaders: ExtractTextPluginForApp.extract({
                    fallbackLoader: 'style-loader',
                    loader: 'css-loader!sass-loader',
                }),
            },
            {
                test: /print.scss/,
                loaders: ExtractTextPluginForPrint.extract({
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
        ExtractTextPluginForApp,
        ExtractTextPluginForPrint,
    ],
};
