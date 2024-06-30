const Encore = require('@symfony/webpack-encore');

if (!Encore.isRuntimeEnvironmentConfigured()) {
    Encore.configureRuntimeEnvironment(process.env.NODE_ENV || 'dev');
}

Encore
    .setOutputPath('public/build/')
    .setPublicPath('/build')
    .addEntry('app', './assets/app.js')
    .splitEntryChunks()
    .enableSingleRuntimeChunk()
    .cleanupOutputBeforeBuild()
    .enableBuildNotifications()
    .enableSourceMaps(!Encore.isProduction())
    .enableVersioning(Encore.isProduction())
    .configureBabelPresetEnv((config) => {
        config.useBuiltIns = 'usage';
        config.corejs = '3.23';
    })
    .enableVueLoader(() => {}, { version: 3 })
    .configureDefinePlugin(options => {
        options['__VUE_OPTIONS_API__'] = JSON.stringify(true);
        options['__VUE_PROD_DEVTOOLS__'] = JSON.stringify(false);
        options['__VUE_PROD_HYDRATION_MISMATCH_DETAILS__'] = JSON.stringify(false);
    })
    .enablePostCssLoader()
    .configureDevServerOptions(options => {
        options.liveReload = true;
        options.hot = true;
        options.watchFiles = ['templates/**/*.html.twig'];
    });

module.exports = Encore.getWebpackConfig();
