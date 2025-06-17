require.config({
    paths: {
        'vue': '../addons/shop/js/vue.min',
        'china': '../addons/shop/js/china',
        'jquery-colorpicker': '../addons/shop/js/jquery.colorpicker.min',
    },
    shim: {
        'jquery-colorpicker': {
            deps: ['jquery'],
            exports: '$.fn.extend'
        }
    }
});
