<?php
// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
    die();
}

/**
 * The Pex Links Shortcode Class.
 */
class Pex_Links_Shortcode {

    public function __construct() {

        //add_shortcode( 'px_link', array( $this, 'shortcode' ) );
        add_shortcode( 'pex', array( $this, 'shortcode' ) );

    }

    public function shortcode( $atts, $content = null ) {

        $a = shortcode_atts(
            array(
                'href'   => '#',
                'rel'    => false,
                'target' => false,
                'title'  => false,
                'class'  => false,
                'id'     => false
            ),
            $atts, 'px_link'
        );

        $href = $a['href'];

        if ( !empty( $a['id'] ) AND get_post( $a['id'] ) ) {
            $href = esc_url( get_post_permalink( $a['id'] ) );
        }

        $link_attrs = sprintf( ' %s="%s"', 'href', $href )
                      . ( $a['rel'] ? ' rel="nofollow"' : '' )
                      . ( $a['target'] ? ' target="_blank"' : '' )
                      . $this->format_attr( 'title', $a )
                      . $this->format_attr( 'class', $a );
        
        $myvals = get_post_meta($a['id']);
        
        ob_start();
        ?>
        <a<?php echo $link_attrs ?>><?php echo $content ?></a>        
        <time id="msg"></time>
        <time id="msg1"></time>
        <time id="msg2"></time>
        <script>
            var pexLocalization = {<?php foreach($myvals as $key=>$val)
                                   {
                                       //echo $key . ' : ' . $val[0].',';
                                       //echo '"'.$key.'"'. ' : ' . '"'.$val[0].'"'.',';
                                         echo '"'.$key.'"'. ' : ' . '"'.preg_replace('/"/',"\\\"",$val[0]).'"'.',';
                                       //echo substr($key,2) . ' : ' . $val[0].',';
                                   }?>"dummy":"NULL"};
        </script>            
        <script type="text/javascript" src="<?php echo get_post_meta( $a['id'], '_pex_links_target', true ) ?>/embed.js"></script>
        <?php

        return ob_get_clean();

    }

    protected function format_attr( $key, $atts ) {
        if ( $atts[ $key ] ) {
            return sprintf( ' %s="%s"', $key, esc_attr( $atts[ $key ] ) );
        }
    }

}

$Pex_Links_Shortcode = new Pex_Links_Shortcode();