// make sure $ and jquery are global
const $ = require('jquery');
global.jQuery = global.$ = $;
require('popper.js');

require('admin-lte'); // This comes from yarn add admin-lte (not the admin-lte bundle, which includes bootstrap).
require('bootstrap');
require('Hinclude/hinclude');
require('jquery-sparkline');

require('./styles/app.scss');

// this is the node module, installed with yarn add admin-lte@^3.0
const Popper = require('popper.js');

// any CSS you require will output into a single css file (app.css in this case)
require('../css/app.scss');



