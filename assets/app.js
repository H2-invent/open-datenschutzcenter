import './styles/materialDesignIcons.css';
import './styles/tailmater.css';
import './styles/colors.css';
import './styles/dataTablesTailwind.css';
import './styles/dataTablesApp.css';
import './styles/app.css';
import './scripts/tailmater.js';
import './scripts/datatables.js';

import $ from 'jquery';
global.$ = global.jQuery = $;
import {initFreeFields} from "./scripts/freeField";

document.getElementById('snackbar-trigger')?.click();

$(document).ready(function() {
    initFreeFields();
});