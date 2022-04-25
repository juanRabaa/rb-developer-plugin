const paths = require('./paths');
const externals = require('./externals');
const autoprefixer = require('autoprefixer');
const MiniCssExtractPlugin = require('mini-css-extract-plugin');
const glob = require('glob');

//Generate object for webpack entry
var entryObject = glob.sync(paths.relativeScriptsJS).reduce(
    function(entries, entry) {
        const normalizedPath = entry.replace( /\//g, "\\" );
        const pathEscaped = escapeRegExp(paths.relativeScripts);
        const regexpString = `${pathEscaped}\\\\([^\\\\]+)\\\\index\\.js$`;
        const regexp = new RegExp(regexpString, "g");
        var matchForName = regexp.exec(normalizedPath);

        if (matchForName !== null && typeof matchForName[1] !== 'undefined')
            entries[matchForName[1]] = entry;

        return entries;
    }, {}
);

console.log('ENTRIES:', entryObject);

module.exports = {
    entry: entryObject,
    output: {
        filename: (data) => {
            console.log('Output file name:', data.chunk.name);
            return `scripts/${data.chunk.name}/index.min.js`;
        }, //'moduleName/script.js', [name] - key from entryObject
        path: paths.prod,
        // chunkLoadingGlobal: 'testConfigChunks',
    },
    plugins: [
        new MiniCssExtractPlugin({
            filename: ({
                chunk
            }) => function() {
                console.log('CHUNK NAME: ', chunk.name, '\n\n');
                if (chunk.name) {
                    if (chunk.name.startsWith('editorStyles__')) {
                        return `scripts/${chunk.name.replace('editorStyles__', '')}/css/editor.min.css`;
                    } else if (chunk.name.startsWith('frontStyles__')) {
                        return `scripts/${chunk.name.replace('frontStyles__', '')}/css/style.min.css`;
                    }
                }
                return `css/${chunk.name}-styles.min.css`;
            },
            // chunkFilename: "[name].css",
        })
    ],
    optimization: {
        splitChunks: {
            cacheGroups: {
                // blocksJs: {
                // 	name: module => {
                // 		return blockNameByMainScriptModule(module);
                // 	},
                // 	test: module => {
                // 		return blockNameByMainScriptModule(module);
                // 	},
                // 	chunks: 'all',
                // 	enforce: true,
                // },
                editorStyles: {
                    name: module => {
                        return scriptNameByStyleModule(module, 'editor', 'editorStyles__');
                    },
                    test: (module, chunks) => {
                        return module.type == 'css/mini-extract' && scriptNameByStyleModule(module, 'editor', 'editorStyles__');
                    },
                    chunks: 'all',
                    reuseExistingChunk: true,
                    enforce: true,
                },
                frontStyles: {
                    name: module => {
                        return scriptNameByStyleModule(module, 'style', 'frontStyles__');
                    },
                    test: module => {
                        return module.type == 'css/mini-extract' && scriptNameByStyleModule(module, 'style', 'frontStyles__');
                    },
                    chunks: 'all',
                    enforce: true,
                },
                // styles: {
                // 	name: 'styles',
                // 	test: /\.s?css$/,
                // 	chunks: 'all',
                // 	enforce: true,
                // 	priority: 20,
                // },
            }
        },
    },
    module: {
        rules: [{
                test: /\.m?js$/,
                exclude: /(node_modules|bower_components)/,
                use: {
                    loader: 'babel-loader',
                    options: {
                        presets: ['@babel/preset-env']
                    }
                }
            },
            {
                test: /\.(scss|css)$/,
                oneOf: [{
                        test: function(name) {
                            return isEditorStylePath(name) || isFrontStylePath(name);
                        },
                        use: [MiniCssExtractPlugin.loader, "css-loader", "sass-loader"],
                    },
                    {
                        test: name => true,
                        use: ["style-loader", "css-loader", "sass-loader"],
                    }
                ]
            },
            {
                test: /\.(woff|woff2|eot|ttf|otf)$/,
                loader: "file-loader"
            },
            {
                test: /\.(gif|png|jpe?g|svg)$/i,
                use: [
                    'file-loader',
                    {
                        loader: 'image-webpack-loader',
                        options: {
                            bypassOnDebug: true, // webpack@1.x
                            disable: true, // webpack@2.x and newer
                        },
                    },
                ],
            }
        ]
    },
    externals,
    resolve: {
        // Must be mapped in .eslintrc.json import/resolver
        alias: {
            JS: `${paths.dev}`,
            COMPONENTS: `${paths.dev}/components`,
            HELPERS: `${paths.dev}/helpers`,
            REDUX: `${paths.dev}/redux`,
            CONSTS: `${paths.dev}/consts`,
            DATA: `${paths.dev}/data`,
            FONTS: `${paths.dev}/assets/fonts`,
            ASSETS: `${paths.dev}/assets`,
            IMAGES: `${paths.dev}/assets/images`,
            NODE_MODULES: paths.nodeModules,
        },
        extensions: ['.js', '.jsx'],
    },
};

console.log('EXTERNALS', externals);

function escapeRegExp(string) {
    return string.replace(/[.*+?^${}()|[\]\\\/]/g, '\\$&'); // $& means the whole matched string
}

function isScriptCssPath(stylePath, cssFilename) {
    const path = require('path');
    const pathSep = escapeRegExp(path.sep);
    //scripts\\src\\scripts\\(.+?(?=\\))\\css\\${cssFilename}\.s?css$
    const expresion = new RegExp(`scripts${pathSep}src${pathSep}scripts${pathSep}(.+?(?=${pathSep}))${pathSep}css${pathSep}${cssFilename}\\.s?css`);
    const match = stylePath.match(expresion);
    return match ? `${match[1]}` : null;
}

function scriptNameByStyleModule(module, cssFilename = 'editor', prefix = '') {
    const scriptName = isScriptCssPath(module.identifier(), cssFilename);
    return scriptName ? `${prefix}${scriptName}` : null;
}

function scriptNameByMainScriptModule(module) {
    const path = require('path');
    const pathSep = escapeRegExp(path.sep);
    //scripts\\src\\scripts\\(.+?(?=\\))\\css\\index.js$
    const expresion = new RegExp(`gutenberg${pathSep}src${pathSep}scripts${pathSep}(.+?(?=${pathSep}))${pathSep}index\\.js`);
    const match = module.identifier().match(expresion);
    return match ? `${match[1]}` : null;
}

function isEditorStylePath(stylePath) {
    return isScriptCssPath(stylePath, 'editor');
}

function isFrontStylePath(stylePath) {
    return isScriptCssPath(stylePath, 'style');
}
