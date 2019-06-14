var Encore = require('@symfony/webpack-encore');

Encore
    .setOutputPath('src/Resources/public/js/')
    .addEntry('contao-no-ui-slider-bundle', './src/Resources/assets/js/contao-no-ui-slider-bundle.js')
    .setPublicPath('/public/js/')
    .disableSingleRuntimeChunk()
    .addExternals({
        'nouislider': 'noUiSlider',
        '@hundh/contao-utils-bundle': 'utilsBundle'
    })
    .configureBabel(function (babelConfig) {
    }, {
        // include to babel processing
        includeNodeModules: ['@hundh/contao-no-ui-slider-bundle']
    })
    .enableSourceMaps(!Encore.isProduction())
;

module.exports = Encore.getWebpackConfig();