import './bootstrap.js';
import "trix";
import './scripts/tailmater.js';
import './scripts/datatables.js';

import 'trix/dist/trix.css';
import './styles/materialDesignIcons.css';
import './styles/tailmater.css';
import './styles/colors.css';
import './styles/dataTablesTailwind.css';
import './styles/dataTablesApp.css';
import './styles/app.css';

import $ from 'jquery';
global.$ = global.jQuery = $;
import {initFreeFields} from "./scripts/freeField";

document.getElementById('snackbar-trigger')?.click();

(function() {
    addEventListener("trix-initialize", function(e) {
        const file_tools = document.querySelector(".trix-button-group--file-tools");
        file_tools?.remove();
    })
    addEventListener("trix-file-accept", function(e) {
        e.preventDefault();
    })
})();

$(document).ready(function() {
    initFreeFields();
});