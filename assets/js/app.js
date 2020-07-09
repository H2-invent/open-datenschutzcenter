/*
 * Welcome to your app's main JavaScript file!
 *
 */
import '../css/app.css';


import $ from 'jquery';
import 'datatables.net-dt';

global.$ = global.jQuery = $;

import ('jszip');
import('datatables.net');
import ('datatables.net-buttons-dt');
require('datatables.net-buttons/js/buttons.flash.js')(window, $);
require('datatables.net-buttons/js/buttons.html5.js')(window, $);
import('moment');
import ('vis-network');


$(document).ready(function () {
    setTimeout(function () {
        var x = document.getElementById("snackbar");
        x.className = "show";
        setTimeout(function () {
            x.className = x.className.replace("show", "");
        }, 3000);
    }, 2000);


    $('#sidebarCollapse').on('click', function () {
        $('#sidebar').toggleClass('active');
    });


    $('#data-table').DataTable({
        dom: 'Bfrtip',
        buttons: [
            'csv', 'excel'

        ]
    });
});

$(".clickable-row").click(function () {
    window.location = $(this).data("href");
});