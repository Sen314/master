/* 
 * Coriolis App JS
 */

$(function() {
    $(document).on('click', 'a#exportExcel', function() {
        $('#stat').tableExport({type: 'excel', escape: 'false'});
        return false;
    });
    
    $(document).on('click', '#open_priorize_form', function() {
        $('#priorize_form').toggle();
    });
    $(document).on('click', '#close_priorize_form', function() {
        $('#priorize_form').toggle();
    });    
    $(document).on('click', 'tr[class^="file_"] > td:not(:first-child)', function() {
        var classname = $(this).parent('tr').attr('class');
        $('input[name=' + classname + ']').trigger('click');
    });
    
    /**
     * Pilotage
     */
    $(document).on('click', '.pilot_function', function() {        
        // nom de la fonction (activer/desactiver/prioriser)
        var function_name = $(this).attr('id');
        // récup des options (pour la priorisation)
        var function_opt = {};
        if (function_name === 'prioriser') {
            function_opt["priority"] = $('#priority').val();
            function_opt["priority_moment"] = $('#priority_moment').val();
        }
        // récupération des id de fichiers sélectionnés
        var file_ids = [];
        $('input:checked[name^=file_]').each(function() {
            file_ids.push($(this).val());
        });        
        // si sélection ok
        if (file_ids.length > 0) {   
            // animate loading
            var l = Ladda.create(this);
            l.start();
            $('#overlay').removeClass('hide');        
            // récupération des filtres initiaux
            var filters = null; 
            if (typeof filtersData !== 'undefined') {
                filters = $.map($('#filters').fancytree('getTree').getSelectedNodes(), function(node){
                        return node.key;
                });
            }            
            // récupération des easycodes concernés
            var contacts = [];
            $.each(file_ids, function(i, val) {
                contacts.push($('input#file_' + val).val());
            });
            // get current url
            var current_url = $(location).attr('href');
            // refresh content en ajax
            $('#main-content').load(current_url + ' #main-content > *', {
                'filters' : filters,
                'contacts' : contacts,
                'function' : function_name,
                'function_opt' : function_opt
            }, function() {
                l.stop();
                $('#overlay').addClass('hide');
            });
            return false;
        } else {
            alert('Veuillez sélectionner au moins un fichier');
            return false;
        }
    });
    
    $(document.body).tooltip({ selector: '[data-toggle="tooltip"]' });
});


