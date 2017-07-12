(function() {
    tinymce.create('tinymce.plugins.pex_links', {
        init : function(ed, url) {
            ed.addCommand('pex_links', function() {
                afLink.open(ed.id);
            });

            ed.addButton('pex_links', {
                title: 'Add Pex Link',
                icon: 'px_link',
                text: 'AfL',
                cmd : 'pex_links'
            });
        }
    });

    // Register plugin
    tinymce.PluginManager.add('pex_links', tinymce.plugins.pex_links);
})();