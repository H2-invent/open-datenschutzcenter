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


$(document).ready(function () {
    setTimeout(function () {
        var x = document.getElementById("snackbar");
        x.className = "show";
        setTimeout(function () {
            x.className = x.className.replace("show", "");
        }, 3000);
    }, 500);


    $('#dismiss, .overlay').on('click', function () {
        // hide sidebar
        $('#sidebar').removeClass('active');
        // hide overlay
        $('.overlay').removeClass('active');
    });

    $('#sidebarCollapse').on('click', function () {
        // open sidebar
        $('#sidebar').addClass('active');
        // fade in the overlay
        $('.overlay').addClass('active');
        $('.collapse.in').toggleClass('in');
        $('a[aria-expanded=true]').attr('aria-expanded', 'false');
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