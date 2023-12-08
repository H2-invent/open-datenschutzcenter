import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    answer() {
        const self = this;
        let disabledButton = true;

        Array.prototype.forEach.call(document.querySelectorAll('input[type=checkbox], input[type=radio]'), function (element) {
            console.log(element.checked);
            if (element.checked) {
                disabledButton = false;
            }
        });

        if (disabledButton) {
            document.getElementById('dynamic_question_continue').setAttribute('disabled', 'disabled');
        } else {
            document.getElementById('dynamic_question_continue').removeAttribute('disabled');
        }
    }
}