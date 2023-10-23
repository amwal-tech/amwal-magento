import path from 'path'
import type webpack from 'webpack'
import package_lock from './package-lock.json'

const config: webpack.Configuration = {
  entry: './src/index.tsx',
  module: {
    rules: [
      {
        test: /\.tsx?$/,
        use: {
          loader: 'babel-loader',
          options: {
            presets: [
              '@babel/preset-env',
              '@babel/preset-react',
              '@babel/preset-typescript'
            ]
          }
        },
        exclude: /node_modules/
      }
    ]
  },
  resolve: {
    extensions: ['.tsx', '.ts', '.js']
  },
  output: {
    filename: 'bundle.js',
    path: path.resolve(__dirname, 'dist')
  },
  experiments: {
    outputModule: true
  },
  target: 'web',
  externalsType: 'module',
  externals: {
    'amwal-checkout-button/loader': `https://cdn.jsdelivr.net/npm/amwal-checkout-button@${package_lock.packages['node_modules/amwal-checkout-button'].version}/loader/index.js`
  }
}
export default config
