module.exports = {
    filenameHashing: false,
    configureWebpack: {
        externals: {
            'vue': 'Vue',
            'axios': 'axios'
        }
    },
    devServer: {
        headers: {"Access-Control-Allow-Origin": "*"},
        https: true,

        // Fix bug caused by webpack-dev-server 3.1.11.
        // https://github.com/vuejs/vue-cli/issues/3173#issuecomment-449573901
        disableHostCheck: true,
    },
}