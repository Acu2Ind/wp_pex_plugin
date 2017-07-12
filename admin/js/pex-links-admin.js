(function($) {
    // handle embed form controls
    ALEmbedForm();
    // handle html/shortcode copy to clipboard
    ALHandleEmbedCopy();
    // handle link copy to clipboard
    ALHandleLinkCopy();
    // handle reset count button
    ALHandleReset();
    // handle admin settings tabs
    ALHandleTabs();
})( jQuery );

function ALEmbedForm(){

    var htmlForm       = jQuery('.pex_links_embed_html');
    var SCForm         = jQuery('.pex_links_embed_shortcode');
    var htmlProto      = jQuery('.pex_links_proto_html');
    var htmlProtoLink  = jQuery('a', htmlProto);
    var embedHtml      = '';
    var embedShortcode = '';

    jQuery('.pex_links_control[type=checkbox]').on('change', function(){

        var _self = jQuery(this);
        var attr  = _self.data('attr');
        var value = _self.data('value');

        if(_self.is(':checked')) {
            htmlProtoLink.attr(attr, value);
        } else {
            htmlProtoLink.removeAttr(attr);
        }

        embedHtml = htmlProto.html();
        embedShortcode = shortcodeHTML(htmlProto);
        htmlForm.val(embedHtml);
        SCForm.val(embedShortcode);

    });

    jQuery('.pex_links_control[type=text]').on('keyup keydown input', function(){

        var _self = jQuery(this);
        var attr  = _self.data('attr');
        var value = _self.val();

        if(typeof value == 'undefined' || value == '' || value == null ) {
            if(attr == 'anchor') {
                htmlProtoLink.text('Click here!');
             } else {
                htmlProtoLink.removeAttr(attr);
            }
        } else {
            if(attr == 'anchor') {
                htmlProtoLink.text(value);
            } else {
                htmlProtoLink.attr(attr, value);
            }
        }

        embedHtml = htmlProto.html();
        embedShortcode = shortcodeHTML(htmlProto);
        htmlForm.val(embedHtml);
        SCForm.val(embedShortcode);

    });

    function shortcodeHTML(htmlProto){

        var shortCodeProto = htmlProto.clone();

        jQuery(shortCodeProto)
            .find('a')
            .removeAttr('href')
            .attr('id', afLinksAdmin.linkId );

        return shortCodeProto.html()
            .replace('</a>', '[/px_link]')
            .replace('<a', '[px_link')
            .replace(">", "]");
    }

}

function ALHandleEmbedCopy(){

    var btns = jQuery('.pex_links_copy');

    btns.each(function(){

        var btn = jQuery(this);
        var textareaClass = btn.data('source');

        btn.on('click', function(e){

            e.preventDefault();

            var copyTextarea = document.querySelector('.'+textareaClass);
            copyTextarea.select();

            try {
                document.execCommand('copy');
            } catch (err) {
                console.log('Unable to copy');
            }

        });

    });

}

function ALHandleLinkCopy(){

    var btn = jQuery('.pex_links_copy_button');

    btn.on('click', function(e){

        e.preventDefault();

        var copyLink = document.querySelector('.pex_links_copy_link');

        try {
            window.getSelection().removeAllRanges();
            var range = document.createRange();
            range.selectNode(copyLink);
            window.getSelection().addRange(range);
            document.execCommand('copy');
            window.getSelection().removeAllRanges();
        } catch (err) {
            console.log('Unable to copy');
        }

    });

}

function ALHandleReset(){

    var btn        = jQuery('.pex_links_reset_button');
    var countField = jQuery('.pex_links_total_count');

    btn.on('click', function(e){

        e.preventDefault();

        var sureText = btn.data('confirm');
        var sure = confirm(sureText);

        if (sure === true) {
            var metaName  = btn.data('meta');
            var inputHTML = '<input type="hidden" value="0" name="'+metaName+'">';
            btn.append(inputHTML);
            countField.text('0');
            btn.hide();
        }

    });

}

function ALHandleTabs(){

    jQuery( '#px_links-settings-form a.nav-tab' ).on( 'click', function() {
            jQuery( '#px_links-settings-form a.nav-tab' ).removeClass( 'nav-tab-active' );
            jQuery( '.px_links-nav-tab' ).removeClass( 'active' );

            var id = jQuery( this ).attr( 'id' ).replace( '-tab', '' );
            jQuery( '#' + id ).addClass( 'active' );
            jQuery( this ).addClass( 'nav-tab-active' );
        }
    );

}

var afLink;

(function($) {

    var SCForm,
        htmlProto,
        htmlProtoLink,
        embedHtml,
        embedShortcode  = '',
        editor = {},
        inputs  = {};

    afLink = {
        textarea: '',

        open: function(editorId) {
            editor = window.tinymce.get( editorId );
            //editor = ed;
            afLink.range = null;

            if ( editorId ) {
                window.wpActiveEditor = editorId;
            }

            if ( ! window.wpActiveEditor ) {
                return;
            }

            this.textarea = $( '#' + window.wpActiveEditor ).get( 0 );

            if ( ! afLink.isMCE() && document.selection ) {
                this.textarea.focus();
                this.range = document.selection.createRange();
            }

            inputs.backdrop.show();
            inputs.wrap.show();
            afLink.refresh();
        },

        isMCE: function() {
            //var editor = window.tinymce.get( window.wpActiveEditor );
            return editor && ! editor.isHidden();
        },

        close: function() {
            $( document.body ).removeClass( 'modal-open' );
            inputs.backdrop.hide();
            inputs.wrap.hide();
            $( document ).trigger( 'wplink-close', inputs.wrap );
        },

        update: function() {
            if ( afLink.isMCE() ) {
                afLink.mceUpdate();
            } else {
                afLink.htmlUpdate();
            }
        },

        htmlUpdate: function() {
            var attrs, text, html, begin, end, cursor, selection,
                textarea = afLink.textarea;

            if ( ! textarea ) {
                return;
            }

            html = this.getShortcodeLink();

            // W3C
            begin = textarea.selectionStart;
            end = textarea.selectionEnd;
            cursor = begin + html.length;

            textarea.value = (
                textarea.value.substring( 0, begin ) +
                html +
                textarea.value.substring( end, textarea.value.length )
            );

            // Update cursor position
            textarea.selectionStart = textarea.selectionEnd = cursor;

            afLink.close();
            textarea.focus();

        },

        mceUpdate: function() {
            editor.execCommand( 'mceInsertContent', false, this.getShortcodeLink() );
            this.close();
        },

        getShortcodeLink: function() {
            return $('#px-link-shortcode').val();
        },

        shortcodeHTML: function(embedHtml){
            embedHtml = embedHtml.replace('</a>', '[/px_link]');
            embedHtml = embedHtml.replace('<a', '[px_link');
            embedHtml = embedHtml.replace(">", "]");
            return embedHtml;
        },

        updateLinkId: function() {
            var attr  = inputs.links.find(':selected').data('attr');
            var value = inputs.links.find(':selected').data('value');

            if(!jQuery('.pex_links_control[data-attr=anchor]').val().length) {
                htmlProtoLink.text(inputs.links.find(':selected').text());
            }

            htmlProtoLink.removeAttr(attr);
            htmlProtoLink.attr(attr, value);

            embedHtml = htmlProto.html();

            embedShortcode = afLink.shortcodeHTML(embedHtml);
            SCForm.val(embedShortcode);
        },

        refresh: function() {

            $(inputs.textFields).each(function(){
                $(this).val('');
                var attr = $(this).data('attr');
                htmlProtoLink.removeAttr(attr);

            });

            $(inputs.checkboxes).each(function(){
                $(this).prop('checked', false);
                var attr = $(this).data('attr');
                htmlProtoLink.removeAttr(attr);
            });

            htmlProtoLink.text(inputs.links.find(':selected').text());

            afLink.updateLinkId();
        },

        init: function() {

            inputs.wrap         = $( '#px-link-wrap');

            SCForm              = inputs.wrap.find( '.pex_links_embed_shortcode' );
            htmlProto           = inputs.wrap.find( '.pex_links_proto_html' );
            htmlProtoLink       = inputs.wrap.find( 'a', htmlProto );

            inputs.dialog       = $( '#px-link' );
            inputs.backdrop     = $( '#px-link-backdrop' );
            inputs.submit       = $( '#px-link-submit' );
            inputs.close        = $( '#px-link-close' );

            inputs.links        = inputs.wrap.find( '#links.pex_links_control' );
            inputs.textFields   = inputs.wrap.find( '.pex_links_control[type=text]');
            inputs.checkboxes   = inputs.wrap.find( '.pex_links_control[type=checkbox]');

            inputs.submit.click( function( event ) {
                event.preventDefault();
                afLink.update();
            });

            inputs.close.add( inputs.backdrop ).add( '#px-link-cancel a' ).click( function( event ) {
                event.preventDefault();
                afLink.close();
            });
            if ( inputs.links.length ) {
                afLink.updateLinkId();
            }

            inputs.links.on('change', afLink.updateLinkId);

            inputs.checkboxes.on('change', function(){

                var _self = $(this);
                var attr  = _self.data('attr');
                var value = _self.data('value');

                if(_self.is(':checked')) {
                    htmlProtoLink.attr(attr, value);
                } else {
                    htmlProtoLink.removeAttr(attr);
                }

                embedHtml = htmlProto.html();
                embedShortcode = afLink.shortcodeHTML(embedHtml);
                SCForm.val(embedShortcode);

            });

            inputs.textFields.on('keyup keydown input', function(){

                var _self = $(this);
                var attr  = _self.data('attr');
                var value = _self.val();

                if(typeof value == 'undefined' || value == '' || value == null ) {
                    if(attr == 'anchor') {
                        htmlProtoLink.text(inputs.links.find(':selected').text());
                    } else {
                        htmlProtoLink.removeAttr(attr);
                    }
                } else {
                    if(attr == 'anchor') {
                        htmlProtoLink.text(value);
                    } else {
                        htmlProtoLink.attr(attr, value);
                    }
                }

                embedHtml = htmlProto.html();
                embedShortcode = afLink.shortcodeHTML(embedHtml);
                SCForm.val(embedShortcode);

            });
        }
    };
    $( document ).ready( afLink.init );
})(jQuery);