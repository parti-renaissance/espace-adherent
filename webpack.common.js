const path = require('path');
const MiniCssExtractPlugin = require('mini-css-extract-plugin');
const { WebpackManifestPlugin } = require('webpack-manifest-plugin');

module.exports = {
    entry: {
        kernel: './front/kernel.js',
        bootstrap: './assets/bootstrap.js',
        admin: './assets/admin.js',
    },
    output: {
        path: path.join(__dirname, './public/built'),
        publicPath: '/built/',
        filename: '[name].[fullhash:12].js',
        chunkFilename: '[id].[chunkhash].js',
        clean: true,
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
                test: /\.s?css$/,
                use: [
                    MiniCssExtractPlugin.loader,
                    {
                        loader: 'css-loader',
                        options: {
                            url: false,
                        },
                    },
                    'postcss-loader',
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
        modules: [path.resolve('./front'), path.resolve('./assets'), 'node_modules'],
    },
    plugins: [
        new WebpackManifestPlugin({}),
        new MiniCssExtractPlugin({
            filename: '[name].[fullhash].css',
        }),
    ],
};
