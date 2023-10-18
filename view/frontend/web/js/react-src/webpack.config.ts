import path from 'path'
import type webpack from 'webpack'

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
    'amwal-checkout-button/loader': 'https://cdn.jsdelivr.net/npm/amwal-checkout-button@0.0.53-alpha/loader/index.js'
  }
}
export default config
