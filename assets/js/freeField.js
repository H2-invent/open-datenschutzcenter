import $ from "jquery";
function addFormToCollection($collectionHolderClass) {
    var $collectionHolder = $('.' + $collectionHolderClass);
    var prototype = $collectionHolder.data('prototype');
    var index = $collectionHolder.data('index');
    var newForm = prototype;
    newForm = newForm.replace(/__name__/g, index);
    $collectionHolder.data('index', index + 1);
    console.log('index', index);
    let  $newFormLi = $('<li class="d-inline-block list-group-item d-flex justify-content-between align-items-center"></li>')
        .append(newForm)
    $collectionHolder.append($newFormLi);
    addTagFormDeleteLink($newFormLi);
}

function addTagFormDeleteLink($tagFormLi) {
    var $removeFormButton = $('<a href="#" class="deleteFreeField" type="remove-group"><div class="text-danger px-1 " data-toggle="tooltip" title="Lösche Freifeld" data-original-title="Lösche Freifeld">X</div></a>');
    $tagFormLi.append($removeFormButton);
    $removeFormButton.on('click', function(e) {
        $tagFormLi.remove();
    });
}
function initFreeFields(){
    $('#add_item_link').off('click')
    var $groupsCollectionHolder = $('ul.freeField');
    $groupsCollectionHolder.find('li').each(function() {
        addTagFormDeleteLink($(this));
    });

    $groupsCollectionHolder.data('index', $groupsCollectionHolder.find('input').length);
    $('#add_item_link').on('click', function(e) {

        var $collectionHolderClass = $(e.currentTarget).data('collectionHolderClass');
        addFormToCollection($collectionHolderClass);
    })
};
export {initFreeFields};