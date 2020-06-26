const Encore = require('@symfony/webpack-encore');
const CopyWebpackPlugin = require('copy-webpack-plugin');

Encore
    .setOutputPath('public/build/')
    .setPublicPath('/build')
    .cleanupOutputBeforeBuild()

    .enableVersioning(Encore.isProduction())
    .enableSourceMaps(!Encore.isProduction())
    .enableBuildNotifications()

    .addEntry('js/app', './assets/js/app.js')
    .configureBabel(function(babelConfig) {
        babelConfig.presets.push('es2015');
    })

    .addStyleEntry('css/admin', './assets/css/admin.scss')
    .addStyleEntry('css/app', './assets/css/app.scss')
    .enableSassLoader(function(sassOptions) {}, {
        resolveUrlLoader: false,
    })

    .addPlugin(new CopyWebpackPlugin([{
        from: './assets/images',
        to: 'images',
    }]))

    .autoProvidejQuery()
;

module.exports = Encore.getWebpackConfig();
