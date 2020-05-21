/* admin_lte??
const $ = require('jquery');
const Popper = require('popper.js');
require('@fortawesome/fontawesome-free/css/all.min.css');
require('@fortawesome/fontawesome-free/js/all.js');
require('datatables.net-bs4');

require('bootstrap');  // is this duplicated?
// any CSS you require will output into a single css file (app.scss in this case)
require('../css/app.scss');
*/


/**
 console.log("Initial datatables");
 global.$('.js-datatable').DataTable({});

 bootstrap
\u0040fortawesome\/fontawesome\u002Dfree
fontawesome
jquery
popper.js
**/

// ------ jquery and bootstrap basics ------
// create global $ and jQuery variables
const $ = require('jquery');
global.$ = global.jQuery = $;

require('jquery-ui'); // TODO is this required?
require('bootstrap');
require('jquery-slimscroll');
require('bootstrap-select');

const Moment = require('moment');
global.moment = Moment;
require('daterangepicker');

// ------ AdminLTE framework ------
// require('admin-lte/dist/css/AdminLTE.min.css');

// require('../../vendor/kevinpapst/adminlte-bundle/Resources/assets/admin-lte');
require('../../vendor/kevinpapst/adminlte-bundle/Resources/assets/admin-lte.scss');
require('../../vendor/kevinpapst/adminlte-bundle/Resources/assets/admin-lte-extensions.scss');
// require('./admin-lte.scss');
// require('./admin-lte-extensions.scss');

global.$.AdminLTE = {};
global.$.AdminLTE.options = {};
// require('admin-lte/dist/js/adminlte.min');
// require('admin-lte');

// ------ Theme itself ------
// require('./default_avatar.png');
// require('./adminltelogo.png');

// ------ icheck for enhanced radio buttons and checkboxes ------
// require('icheck');
// require('icheck/skins/square/blue.css');
