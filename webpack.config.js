const Encore = require('@symfony/webpack-encore');
const CopyWebpackPlugin = require('copy-webpack-plugin');

if (!Encore.isRuntimeEnvironmentConfigured()) {
    Encore.configureRuntimeEnvironment(process.env.NODE_ENV || 'dev');
}

Encore
    .setOutputPath('public/build/')
    .setPublicPath('/build')
    .cleanupOutputBeforeBuild()
    .enableSingleRuntimeChunk()
    .enableBuildNotifications()
    .enableSourceMaps(!Encore.isProduction())
    .enableVersioning(Encore.isProduction())
    .addEntry('js/app', './assets/js/app.js')
    .addEntry('js/select2', './assets/js/select2.js')
    .configureBabel((config) => {
        config.plugins.push('@babel/plugin-proposal-class-properties');
    })
    .configureBabelPresetEnv((config) => {
        // config.useBuiltIns = 'usage'; // commented to let the compilation works with symfony/ux packages
        config.corejs = 3;
    })
    .addStyleEntry('css/admin', './assets/css/admin.scss')
    .addStyleEntry('css/user', './assets/css/user.scss')
    .addStyleEntry('css/app', './assets/css/app.scss')
    .addStyleEntry('css/select2', './assets/css/select2.scss')
    .enableSassLoader(function(sassOptions) {}, {
        resolveUrlLoader: false,
    })
    .addPlugin(new CopyWebpackPlugin([{
        from: './assets/images',
        to: 'images',
    }]))

    .enableStimulusBridge('./assets/controllers.json')
;

module.exports = Encore.getWebpackConfig();
