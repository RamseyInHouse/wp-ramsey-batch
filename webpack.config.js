module.exports = {
  mode: process.env.NODE_ENV,
  entry: {
    ramseyBatch: "./js/src/ramsey-batch.js"
  },
  output: {
    path: __dirname + "/js/dist",
    filename: "ramsey-batch.min.js"
  },
  module: {
    rules: [
      {
        test: /\.js$/,
        exclude: /node_modules/,
        loader: "babel-loader",
        query: {
          compact: true
        }
      }
    ]
  }
};
