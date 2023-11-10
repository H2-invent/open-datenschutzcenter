import './styles/tailmater.css';
import './styles/colors.css';
import './styles/data_tables.css';
import './scripts/tailmater.js';
import DataTable from "datatables.net-dt";

Array.prototype.forEach.call(document.getElementsByClassName('dataTable'), function (element) {
    new DataTable('#' + element.getAttribute('id'), {
        dom: 'Bfrtip',
        buttons: [
            {
                extend: 'csv',
                exportOptions: {
                    columns: ':not(.hide-in-export)'
                }
            },
            {
                extend: 'excel',
                exportOptions: {
                    columns: ':not(.hide-in-export)'
                }
            }
        ]
    });
});