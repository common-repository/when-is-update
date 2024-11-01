(function()
{
    tinymce.create("tinymce.plugins.whenisupdate_button_plugin",
    {
        //url argument holds the absolute url of our plugin directory
        init : function(ed, url)
        {
            //add new button     
            ed.addButton("whenisupdate",
            {
                title : "When Is Update",
                cmd : "whenisupdate_command",
                image : "//whenisupdate.com/img/favico/favicon-32x32.png"
            });

            //button functionality.
            ed.addCommand("whenisupdate_command", function()
            {
                var return_text = "[whenisupdate]";
                ed.execCommand("mceInsertContent", 0, return_text);
            });

        },

        createControl : function(n, cm)
        {
            return null;
        },

        getInfo : function()
        {
            return {
                longname : "When Is Update",
                author : "Pauli 'Dids' Jokela",
                version : "1.0.8"
            };
        }
    });

    tinymce.PluginManager.add("whenisupdate_button_plugin", tinymce.plugins.whenisupdate_button_plugin);
})();
