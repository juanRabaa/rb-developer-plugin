const path = require('path');

const packageRelative = '../js';
const devRelativePath = `${packageRelative}/src`;
const relativeScripts = `${devRelativePath}/scripts`;

module.exports = {
    prod: path.resolve(__dirname, `${packageRelative}/dist`),
    dev: path.resolve(__dirname, `${devRelativePath}`),
    externals: path.resolve(__dirname, `externals.js`),
    relativeScripts: path.resolve(__dirname, relativeScripts),
    relativeScriptsJS: path.resolve(__dirname, `${relativeScripts}/*/index.js`),
    nodeModules: path.resolve(__dirname, `${packageRelative}/../node_modules`),
}
