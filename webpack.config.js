const path = require('path');
var webpack = require('webpack')
const MiniCssExtractPlugin = require('mini-css-extract-plugin');
const OptimizeCSSAssetsPlugin = require('optimize-css-assets-webpack-plugin');

module.exports = {
    module: {
        rules: [
            {
                test: /\.css$/,
                use: [MiniCssExtractPlugin.loader, "css-loader"],
            },
            {
                test: /\.(woff(2)?|ttf|eot|svg)(\?v=\d+\.\d+\.\d+)?$/,
                use: [
                    {
                        loader: 'file-loader',
                        options: {
                            name: '[name].[ext]',
                            outputPath: 'fonts/'
                        }
                    }
                ],
            },
        ],
    },
    plugins: [
        new MiniCssExtractPlugin({
            filename: "[name].css",
        }),
        new OptimizeCSSAssetsPlugin({})
    ],
    entry: {
        main: './src/scripts/styles.js',
        index: './src/scripts/index.js',
        users: './src/scripts/users.js',
        settings: './src/scripts/settings.js',
        courses: './src/scripts/courses.js',
        enrolments: './src/scripts/enrolments.js'
    },
    mode: 'development',
    devtool: 'eval-source-map',
    output: {
        filename: '[name].js',
        path: path.resolve(__dirname, 'dist/static'),
    },
};