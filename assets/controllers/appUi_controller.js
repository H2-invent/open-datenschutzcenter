import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    connect() {
        this.initTabs();
    }

    initTabs() {
        self = this;
        Array.prototype.forEach.call(document.querySelectorAll('[data-type="tabs"]'), function (element) {
            element.addEventListener('click', function(event) {
                event.preventDefault();
                //window.location.hash = self.getAttributeFromElementOrParents(event.target, 'data-target');
                history.replaceState(null, null, self.getAttributeFromElementOrParents(event.target, 'data-target'));
            });
        });
        if (document.location.toString().match('#')) {
            document.querySelector('[data-target="#' + document.location.toString().split('#')[1] + '"]').click();
        }
    }

    loadContentModal(event) {
        event.preventDefault();
        const url = event.target.getAttribute('href');
        const modal = document.getElementById('modal-remote-content');
        const contentContainer = modal.querySelector('.modal-inner');
        contentContainer.innerHTML = '<p class="p-10 italic">Loading ...</p>'

        fetch(url)
            .then(response => response.text())
            .then(data => { contentContainer.innerHTML = data; })
            .catch(error => console.error(error));
    }

    getAttributeFromElementOrParents(element, attribute, i = 1) {
        if (5 == i) { // max 5 recursive calls
            return null;
        }

        if (element.getAttribute(attribute)) {
            return element.getAttribute(attribute);
        } else if (element.parentNode) {
            return this.getAttributeFromElementOrParents(element.parentNode, attribute, ++i);
        }

        return null;
    }
}
