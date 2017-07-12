<?php $productID = get_post_meta($post->ID,'productID',true);?>

<p class="pex_links_proto_html"><a href="<?php the_permalink() ?>"><?php the_title() ?></a></p>

<p>
	<strong><?php _e( 'Embed Shortcode', 'pex-links' ) ?></strong>
</p>
<textarea readonly spellcheck="false" class="pex_links_embed pex_links_embed_shortcode">[pex <?php echo get_post_meta($post->ID,'productID',true) ?>-<?php echo substr(get_post_meta($post->ID,'_pex_links_variant',true),0,2) ?> id="<?php echo $post->ID ?>"]</textarea>
<button class="pex_links_copy button button-secondary hide-if-no-js" data-source="pex_links_embed_shortcode"><?php _e( 'Copy', 'pex-links' ) ?></button>