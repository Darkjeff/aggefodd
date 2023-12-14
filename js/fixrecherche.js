$(document).ready(function() {
    function prefixInputGlobalSearch(prefix) {
        let input    = $('#top-global-search-input');
        let oldvalue = input.val();
        let newvalue = prefix+"|"+oldvalue;
        input.val(newvalue);
    }

    $('[data-target*=agefodd_session_search_by_id]').click(function (e) {
        prefixInputGlobalSearch('ID');
    });
    $('[data-target*=agefodd_session_search_by_ref]').click(function (e) {
        prefixInputGlobalSearch('REF');
    });

})