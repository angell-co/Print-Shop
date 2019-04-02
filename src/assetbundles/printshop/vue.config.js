module.exports = {
    filenameHashing: false,
    configureWebpack: {
        externals: {
            'vue': 'Vue',
            'axios': 'axios'
        }
    },
}