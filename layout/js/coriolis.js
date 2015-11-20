$(function() {
    // fix pour combinaison tabs & graph 
    // @see http://blog.b3rgstrom.se/post/68081765878/morris-js-charts-in-bootstrap-tabs
    $('ul.nav a').on('shown.bs.tab', function (e) {
        var types = $(this).attr("data-identifier");
        var typesArray = types.split(",");
        $.each(typesArray, function (key, value) {
            eval(value + ".redraw()");
        });
    });    
});

