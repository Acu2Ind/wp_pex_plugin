<?php
/**
 * @var $this Pex_Links
 */
?>
<div id="px-link-backdrop" class="px-link" style="display: none;"></div>
<div id="px-link-wrap" style="display: none; margin-top: -200px" class="px-link">
    <div id="px-link">
        <div id="link-modal-title"><?php _e( 'Insert Pex link', 'pex-links' ) ?>
            <button type="button" id="px-link-close" class="px-link">
                <span class="screen-reader-text">Close</span>
            </button>
        </div>
        <div id="link-selector">
            <div>
                <label>
                    <span><?php _e( 'Pex Links', 'pex-links' ) ?></span>
                    <select id="links" class="pex_links_control" name="link_id" style="width: 100%">
                        <?php foreach($this->get_links() as $link): ?>
                            <option data-attr="id" data-value="<?php echo $link->ID ?>"><?php echo $link->post_title; ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
            </div>
            <div id="link-options">
                <div>
                    <label>
                        <span><?php _e( 'Add link title', 'pex-links' ) ?></span>
                        <input data-attr="title" class="pex_links_control" type="text" >
                    </label>
                </div>

                <div>
                    <label>
                        <span><?php _e( 'Add link class', 'pex-links' ) ?></span>
                        <input data-attr="class" class="pex_links_control" type="text" >
                    </label>
                </div>
                <div>
                    <label>
                        <span><?php _e( 'Add link anchor', 'pex-links' ) ?></span>
                        <input data-attr="anchor" class="pex_links_control" type="text" >
                    </label>
                </div>

                <div class="link-checkbox">
                    <label for="">
                        <span><?php _e( 'Add rel="nofollow"', 'pex-links' ) ?></span>
                        <input data-attr="rel" data-value="nofollow" class="pex_links_control" type="checkbox" >
                    </label>

                </div>
                <div class="link-checkbox">
                    <label for="">
                        <span><?php _e( 'Add target="_blank', 'pex-links' ) ?></span>
                        <input data-attr="target" data-value="_blank" class="pex_links_control" type="checkbox">
                    </label>
                </div>
            </div>
            <p class="pex_links_proto_html"><a></a></p>
            <p>
                <strong><?php _e( 'Embed Shortcode', 'pex-links' ) ?></strong>
            </p>
            <textarea id="px-link-shortcode" readonly spellcheck="false" class="pex_links_embed pex_links_embed_shortcode">[px_link][/px_link]</textarea>
            <button id="px-link-submit" class="pex_links_copy button button-secondary hide-if-no-js" data-source="pex_links_embed_shortcode"><?php _e( 'Insert link', 'pex-links' ) ?></button>
    </div>
</div>

