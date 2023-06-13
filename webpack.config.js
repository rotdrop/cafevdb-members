const path = require('path')
const webpack = require('webpack')
const webpackConfig = require('@nextcloud/webpack-vue-config')
const DeadCodePlugin = require('webpack-deadcode-plugin')
const fs = require('fs')
const xml2js = require('xml2js')

const infoFile = path.join(__dirname, 'appinfo/info.xml')
let appInfo
xml2js.parseString(fs.readFileSync(infoFile), function(err, result) {
  if (err) {
    throw err
  }
  appInfo = result
})
const appName = appInfo.info.id[0]

webpackConfig.entry = {
  'admin-settings': path.join(__dirname, 'src', 'admin-settings.js'),
  'personal-settings': path.join(__dirname, 'src', 'personal-settings.js'),
  'project-registration': path.join(__dirname, 'src', 'project-registration.js'),
  main: path.join(__dirname, 'src', 'main.js'),
}

webpackConfig.plugins.push(new webpack.DefinePlugin({
  APP_NAME: JSON.stringify(appName),
}))

webpackConfig.plugins.push(new DeadCodePlugin({
  patterns: [
    'src/**/*.(js|jsx|css)',
    'style/**/*.scss',
  ],
  exclude: [
    'src/toolkit/**/(settings-sync|ajax|dialogs|jquery|on-document-loaded|pangram|print-r|file-download).js',
  ],
}))

webpackConfig.module.rules.push({
  test: /\.xml$/i,
  use: 'xml-loader',
})

module.exports = webpackConfig
