/* admin_lte??
const $ = require('jquery');
require('bootstrap');
const Popper = require('popper.js');
require('@fortawesome/fontawesome-free/css/all.min.css');
require('@fortawesome/fontawesome-free/js/all.js');

*/
// any CSS you require will output into a single css file (app.css in this case)
require('../css/app.css');

require('datatables.net');
require('datatables.net-bs4');

console.log("Initial datatables");
global.$('.js-datatable').DataTable({});
/**
bootstrap
\u0040fortawesome\/fontawesome\u002Dfree
fontawesome
jquery
popper.js
**/