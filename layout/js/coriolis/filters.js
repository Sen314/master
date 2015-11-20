/* 
 * JS pour les filtres
 */

$(function() {
    // datepickers
    $('.datepicker').datepicker({
        onClose: function(selectedDate) {
            if ($(this).attr('id') == 'begin_date') {    
                $('#end_date').datepicker('option', 'minDate', selectedDate);
            } else if ($(this).attr('id') == 'end_date') {
                $('#begin_date').datepicker('option', 'maxDate', selectedDate);
            }
        }
    });    
    
    // affichage des filtres avec fancytree
    if (typeof filtersData !== 'undefined') {
        $("#filters").fancytree({
            checkbox: true,
            selectMode: 3,
            source: filtersData,
            dblclick: function(e, data) {
                data.node.toggleSelected();
            },
            keydown: function(e, data) {
                if( e.which === 32 ) {
                    data.node.toggleSelected();
                    return false;
                }
            }
        });
    }
    // btn pour lancer la recherche avec les filtres sélectionnés
    $('#btn-filter').click( function() {        
        // animate loading
        var l = Ladda.create(this);
        l.start();
        
        var filters = [];
        // get tree selected nodes        
        if (typeof filtersData !== 'undefined') {
            filters = $.map($('#filters').fancytree('getTree').getSelectedNodes(), function(node){
                    return node.key;
            });
        }
        
        // ajout des filtres de type form
        $("input[name*='filters']").each(function() {
            if ($(this).val() != '') {
                filters.push($(this).attr('id') + ':' + $(this).val());
            }
        });
        
        var current_url = $(location).attr('href') + '&ajax';

        $('#main-content').load(current_url + ' #main-content > *', {
            'begin_date': $('#begin_date').datepicker({ dateFormat: 'dd/mm/yyyy' }).val(),
            'end_date': $('#end_date').datepicker({ dateFormat: 'dd/mm/yyyy' }).val(),
            'date': $('#date').datepicker({ dateFormat: 'dd/mm/yyyy' }).val(),
            'filters' : filters
        }, function(response) {
            l.stop();
            // redirect vers index si déconnecté
            if (response == 'loggedout') {
                $(location).attr('href', 'index.php');
            }
        });
        return false;
    });
    
    // trigger click qd on tape la touche "Entrée" dans un input
    $(document).on('keyup', '.filters-sidebar input', function(event) {
        if(event.keyCode == 13) { // 13 = Enter Key
            $('#btn-filter').trigger('click');
        }
    });
});