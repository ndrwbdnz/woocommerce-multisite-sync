jQuery(document).ready(function ($) {
    $('input[name=wms-expand-bttn]').click(function (e) {
        e.preventDefault();
        var bttnID = $(this).attr('id');
        bttnID = bttnID.replace('expand-', '');
        var tableRowID = $('#' + bttnID + '-wms-stock-log');
        if (!tableRowID.is(':visible')) {
            tableRowID.show();
        } else {
            tableRowID.hide();
        }
    });

    $('input[name=wms-check-all]').click(function (e) {
        $('input:checkbox').not(this).prop('checked', this.checked);
    });
    $('a.qtip-tooltip').each(function () {
        $(this).qtip({
            content: {
                text: function (event, api) {

                    var content = '';
                    var tmp_id = api.elements.target.attr('id')
                    content += '<ul>';
                    $.each(wmsBookedProducts[tmp_id]['orders'], function (key, value) {
                        content += '<li><a href="' + admin_url + 'post.php?post=' + value.id + '&action=edit" target="_blank" >Zamówienie: ' + value.id + ' Dla: ' + value.dla + ' Sztuk: ' + value.szt + '</a>';
                    });
                    content += '</ul>'
                    api.set({'content.text': content});
                    return content;
                },
                title: {
                    text: 'Zamówienia',
                    button: true,
                },
            },
            hide: {
                fixed: true,
                delay: 300
            },
            position: {
                viewport: $(window)
            },
            style: 'qtip-light'
        })

    });
});