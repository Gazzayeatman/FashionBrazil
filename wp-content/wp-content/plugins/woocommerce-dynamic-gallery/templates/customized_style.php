<style>
<?php
global $wc_dgallery_admin_interface, $wc_dgallery_fonts_face;

$g_thumb_spacing            = get_option(WOO_DYNAMIC_GALLERY_PREFIX . 'thumb_spacing');

$main_bg_color              = get_option(WOO_DYNAMIC_GALLERY_PREFIX . 'main_bg_color');
$main_border                = get_option(WOO_DYNAMIC_GALLERY_PREFIX . 'main_border');
$main_shadow                = get_option(WOO_DYNAMIC_GALLERY_PREFIX . 'main_shadow');
$main_margin_top            = get_option(WOO_DYNAMIC_GALLERY_PREFIX . 'main_margin_top');
$main_margin_bottom         = get_option(WOO_DYNAMIC_GALLERY_PREFIX . 'main_margin_bottom');
$main_margin_left           = get_option(WOO_DYNAMIC_GALLERY_PREFIX . 'main_margin_left');
$main_margin_right          = get_option(WOO_DYNAMIC_GALLERY_PREFIX . 'main_margin_right');
$main_padding_top           = get_option(WOO_DYNAMIC_GALLERY_PREFIX . 'main_padding_top');
$main_padding_bottom        = get_option(WOO_DYNAMIC_GALLERY_PREFIX . 'main_padding_bottom');
$main_padding_left          = get_option(WOO_DYNAMIC_GALLERY_PREFIX . 'main_padding_left');
$main_padding_right         = get_option(WOO_DYNAMIC_GALLERY_PREFIX . 'main_padding_right');

$navbar_font                = get_option(WOO_DYNAMIC_GALLERY_PREFIX . 'navbar_font');
$navbar_bg_color            = get_option(WOO_DYNAMIC_GALLERY_PREFIX . 'navbar_bg_color');
$navbar_border              = get_option(WOO_DYNAMIC_GALLERY_PREFIX . 'navbar_border');
$navbar_shadow              = get_option(WOO_DYNAMIC_GALLERY_PREFIX . 'navbar_shadow');
$navbar_margin_top          = get_option(WOO_DYNAMIC_GALLERY_PREFIX . 'navbar_margin_top');
$navbar_margin_bottom       = get_option(WOO_DYNAMIC_GALLERY_PREFIX . 'navbar_margin_bottom');
$navbar_margin_left         = get_option(WOO_DYNAMIC_GALLERY_PREFIX . 'navbar_margin_left');
$navbar_margin_right        = get_option(WOO_DYNAMIC_GALLERY_PREFIX . 'navbar_margin_right');
$navbar_padding_top         = get_option(WOO_DYNAMIC_GALLERY_PREFIX . 'navbar_padding_top');
$navbar_padding_bottom      = get_option(WOO_DYNAMIC_GALLERY_PREFIX . 'navbar_padding_bottom');
$navbar_padding_left        = get_option(WOO_DYNAMIC_GALLERY_PREFIX . 'navbar_padding_left');
$navbar_padding_right       = get_option(WOO_DYNAMIC_GALLERY_PREFIX . 'navbar_padding_right');

$navbar_separator           = get_option(WOO_DYNAMIC_GALLERY_PREFIX . 'navbar_separator');

$caption_font               = get_option(WOO_DYNAMIC_GALLERY_PREFIX . 'caption_font');
$caption_bg_color           = get_option(WOO_DYNAMIC_GALLERY_PREFIX . 'caption_bg_color');
$caption_bg_transparent     = get_option(WOO_DYNAMIC_GALLERY_PREFIX . 'caption_bg_transparent');

$transition_scroll_bar      = get_option( WOO_DYNAMIC_GALLERY_PREFIX.'transition_scroll_bar' );

$thumb_show_type            = get_option(WOO_DYNAMIC_GALLERY_PREFIX . 'thumb_show_type', 'slider' );
$thumb_border_color         = get_option(WOO_DYNAMIC_GALLERY_PREFIX . 'thumb_border_color', 'transparent' );
$thumb_current_border_color = get_option(WOO_DYNAMIC_GALLERY_PREFIX . 'thumb_current_border_color', '#96588a' );

?>
#TB_window {
    width: auto !important;
}
.product .onsale {
    z-index: 100;
}
.a3-dgallery .a3dg-image-wrapper {
	<?php echo $wc_dgallery_admin_interface->generate_background_color_css( $main_bg_color ); ?>
    <?php echo $wc_dgallery_admin_interface->generate_border_css( $main_border ); ?>
    <?php echo $wc_dgallery_admin_interface->generate_shadow_css( $main_shadow ); ?>
    margin: <?php echo $main_margin_top; ?>px <?php echo $main_margin_right; ?>px <?php echo $main_margin_bottom; ?>px <?php echo $main_margin_left; ?>px !important;
    padding: <?php echo $main_padding_top; ?>px <?php echo $main_padding_right; ?>px <?php echo $main_padding_bottom; ?>px <?php echo $main_padding_left; ?>px !important;
}
.a3-dgallery .a3dg-image-wrapper .a3dg-image {
    margin-top: <?php echo $main_padding_top; ?>px !important;
}
.a3-dgallery .a3dg-thumbs li {
    margin-right: <?php echo $g_thumb_spacing; ?>px !important;
<?php if ( 'static' == $thumb_show_type ) { ?>
    margin-bottom: <?php echo $g_thumb_spacing; ?>px !important;
<?php } ?>
}

/* Caption Text */
.a3dg-image-wrapper .a3dg-image-description {
    <?php echo $wc_dgallery_fonts_face->generate_font_css( $caption_font ); ?>;
    <?php echo $wc_dgallery_admin_interface->generate_background_color_css( $caption_bg_color, $caption_bg_transparent ); ?>
}

/* Navbar Separator */
.product_gallery .a3dg-navbar-separator {
    <?php echo str_replace( 'border', 'border-left', $wc_dgallery_admin_interface->generate_border_style_css( $navbar_separator ) ); ?>
    margin-left: -<?php echo ( (int)$navbar_separator['width'] / 2 ); ?>px;
}

/* Navbar Control */
.product_gallery .a3dg-navbar-control {
    <?php echo $wc_dgallery_fonts_face->generate_font_css( $navbar_font ); ?>
    <?php echo $wc_dgallery_admin_interface->generate_background_color_css( $navbar_bg_color ); ?>
    <?php echo $wc_dgallery_admin_interface->generate_border_css( $navbar_border ); ?>
    <?php echo $wc_dgallery_admin_interface->generate_shadow_css( $navbar_shadow ); ?>
    margin: <?php echo $navbar_margin_top; ?>px <?php echo $navbar_margin_right; ?>px <?php echo $navbar_margin_bottom; ?>px <?php echo $navbar_margin_left; ?>px !important;
}
.product_gallery .a3dg-navbar-control .slide-ctrl,
.product_gallery .a3dg-navbar-control .icon_zoom {
    padding: <?php echo $navbar_padding_top; ?>px <?php echo $navbar_padding_right; ?>px <?php echo $navbar_padding_bottom; ?>px <?php echo $navbar_padding_left; ?>px !important;
}

/* Lazy Load Scroll */
.a3-dgallery .lazy-load {
    background-color: <?php echo $transition_scroll_bar; ?> !important;
}

.product_gallery .a3-dgallery .a3dg-thumbs li a {
    border: 1px solid <?php echo $thumb_border_color; ?> !important;
}

.a3-dgallery .a3dg-thumbs li a.a3dg-active {
    border: 1px solid <?php echo $thumb_current_border_color; ?> !important;
}

<?php
$icons_display_type                 = get_option(WOO_DYNAMIC_GALLERY_PREFIX . 'icons_display_type' );

$nextpre_icons_size                 = get_option(WOO_DYNAMIC_GALLERY_PREFIX . 'nextpre_icons_size', 30 );
$nextpre_icons_color                = get_option(WOO_DYNAMIC_GALLERY_PREFIX . 'nextpre_icons_color', '#000');
$nextpre_icons_background           = get_option(WOO_DYNAMIC_GALLERY_PREFIX . 'nextpre_icons_background', array( 'enable' => 1, 'color' => '#FFF' ) );
$nextpre_icons_opacity              = get_option(WOO_DYNAMIC_GALLERY_PREFIX . 'nextpre_icons_opacity', 70 );
$nextpre_icons_border               = get_option(WOO_DYNAMIC_GALLERY_PREFIX . 'nextpre_icons_border', array( 'width' => '0px', 'style' => 'solid', 'color' => '#666', 'corner' => 'square' , 'top_left_corner' => 3 , 'top_right_corner' => 3 , 'bottom_left_corner' => 3 , 'bottom_right_corner' => 3 ) );
$nextpre_icons_shadow               = get_option(WOO_DYNAMIC_GALLERY_PREFIX . 'nextpre_icons_shadow', array( 'enable' => 0, 'h_shadow' => '0px' , 'v_shadow' => '1px', 'blur' => '0px' , 'spread' => '0px', 'color' => '#555555', 'inset' => 'inset' ) );
$nextpre_icons_padding_top          = get_option(WOO_DYNAMIC_GALLERY_PREFIX . 'nextpre_icons_padding_top', 5 );
$nextpre_icons_padding_bottom       = get_option(WOO_DYNAMIC_GALLERY_PREFIX . 'nextpre_icons_padding_bottom', 5 );
$nextpre_icons_padding_left         = get_option(WOO_DYNAMIC_GALLERY_PREFIX . 'nextpre_icons_padding_left', 5 );
$nextpre_icons_padding_right        = get_option(WOO_DYNAMIC_GALLERY_PREFIX . 'nextpre_icons_padding_right', 5 );
$nextpre_icons_margin_left          = get_option(WOO_DYNAMIC_GALLERY_PREFIX . 'nextpre_icons_margin_left', 10 );
$nextpre_icons_margin_right         = get_option(WOO_DYNAMIC_GALLERY_PREFIX . 'nextpre_icons_margin_right', 10 );

$pauseplay_icon_size                = get_option(WOO_DYNAMIC_GALLERY_PREFIX . 'pauseplay_icon_size', 25 );
$pauseplay_icon_color               = get_option(WOO_DYNAMIC_GALLERY_PREFIX . 'pauseplay_icon_color', '#000');
$pauseplay_icon_background          = get_option(WOO_DYNAMIC_GALLERY_PREFIX . 'pauseplay_icon_background', array( 'enable' => 1, 'color' => '#FFF' ) );
$pauseplay_icon_opacity             = get_option(WOO_DYNAMIC_GALLERY_PREFIX . 'pauseplay_icon_opacity', 70 );
$pauseplay_icon_border              = get_option(WOO_DYNAMIC_GALLERY_PREFIX . 'pauseplay_icon_border', array( 'width' => '0px', 'style' => 'solid', 'color' => '#666', 'corner' => 'square' , 'top_left_corner' => 3 , 'top_right_corner' => 3 , 'bottom_left_corner' => 3 , 'bottom_right_corner' => 3 ) );
$pauseplay_icon_shadow              = get_option(WOO_DYNAMIC_GALLERY_PREFIX . 'pauseplay_icon_shadow', array( 'enable' => 0, 'h_shadow' => '0px' , 'v_shadow' => '1px', 'blur' => '0px' , 'spread' => '0px', 'color' => '#555555', 'inset' => 'inset' ) );
$pauseplay_icon_padding_top         = get_option(WOO_DYNAMIC_GALLERY_PREFIX . 'pauseplay_icon_padding_top', 10 );
$pauseplay_icon_padding_bottom      = get_option(WOO_DYNAMIC_GALLERY_PREFIX . 'pauseplay_icon_padding_bottom', 10 );
$pauseplay_icon_padding_left        = get_option(WOO_DYNAMIC_GALLERY_PREFIX . 'pauseplay_icon_padding_left', 10 );
$pauseplay_icon_padding_right       = get_option(WOO_DYNAMIC_GALLERY_PREFIX . 'pauseplay_icon_padding_right', 10 );
$pauseplay_icon_margin_top          = get_option(WOO_DYNAMIC_GALLERY_PREFIX . 'pauseplay_icon_margin_top', 10 );
$pauseplay_icon_margin_bottom       = get_option(WOO_DYNAMIC_GALLERY_PREFIX . 'pauseplay_icon_margin_bottom', 10 );
$pauseplay_icon_margin_left         = get_option(WOO_DYNAMIC_GALLERY_PREFIX . 'pauseplay_icon_margin_left', 10 );
$pauseplay_icon_margin_right        = get_option(WOO_DYNAMIC_GALLERY_PREFIX . 'pauseplay_icon_margin_right', 10 );
$pauseplay_icon_vertical_position   = get_option(WOO_DYNAMIC_GALLERY_PREFIX . 'pauseplay_icon_vertical_position', 'center' );
$pauseplay_icon_horizontal_position = get_option(WOO_DYNAMIC_GALLERY_PREFIX . 'pauseplay_icon_horizontal_position', 'center' );

$thumb_nextpre_icons_size           = get_option(WOO_DYNAMIC_GALLERY_PREFIX . 'thumb_nextpre_icons_size', 20 );
$thumb_nextpre_icons_color          = get_option(WOO_DYNAMIC_GALLERY_PREFIX . 'thumb_nextpre_icons_color', '#000');
$thumb_nextpre_icons_background     = get_option(WOO_DYNAMIC_GALLERY_PREFIX . 'thumb_nextpre_icons_background', array( 'enable' => 1, 'color' => '#FFF' ) );
$thumb_nextpre_icons_border         = get_option(WOO_DYNAMIC_GALLERY_PREFIX . 'thumb_nextpre_icons_border', array( 'width' => '1px', 'style' => 'solid', 'color' => '#666', 'corner' => 'square' , 'top_left_corner' => 3 , 'top_right_corner' => 3 , 'bottom_left_corner' => 3 , 'bottom_right_corner' => 3 ) );
$thumb_nextpre_icons_shadow         = get_option(WOO_DYNAMIC_GALLERY_PREFIX . 'thumb_nextpre_icons_shadow', array( 'enable' => 0, 'h_shadow' => '0px' , 'v_shadow' => '1px', 'blur' => '0px' , 'spread' => '0px', 'color' => '#555555', 'inset' => 'inset' ) );
$thumb_nextpre_icons_padding_left   = get_option(WOO_DYNAMIC_GALLERY_PREFIX . 'thumb_nextpre_icons_padding_left', 5 );
$thumb_nextpre_icons_padding_right  = get_option(WOO_DYNAMIC_GALLERY_PREFIX . 'thumb_nextpre_icons_padding_right', 5 );

$thumb_slider_background            = get_option(WOO_DYNAMIC_GALLERY_PREFIX . 'thumb_slider_background' );
$thumb_slider_border                = get_option(WOO_DYNAMIC_GALLERY_PREFIX . 'thumb_slider_border' );
$thumb_slider_shadow                = get_option(WOO_DYNAMIC_GALLERY_PREFIX . 'thumb_slider_shadow' );

?>

<?php if ( 'show' == $icons_display_type ) { ?>
.a3dg-image-wrapper .slide-ctrl,
.a3-dgallery .a3dg-image-wrapper .a3dg-next,
.a3-dgallery .a3dg-image-wrapper .a3dg-prev {
    display: block !important;
}
<?php } ?>

/* Next / Previous Icons */
.a3-dgallery .fa-caret-left:before,
.a3-dgallery .fa-caret-right:before  {
    font-size: <?php echo $nextpre_icons_size; ?>px !important;
    color: <?php echo $nextpre_icons_color; ?> !important;
}
.a3-dgallery .a3dg-image-wrapper .a3dg-next,
.a3-dgallery .a3dg-image-wrapper .a3dg-prev {
    <?php echo $wc_dgallery_admin_interface->generate_background_color_css( $nextpre_icons_background ); ?>
    <?php echo $wc_dgallery_admin_interface->generate_border_css( $nextpre_icons_border ); ?>
    <?php echo $wc_dgallery_admin_interface->generate_shadow_css( $nextpre_icons_shadow ); ?>
    padding: <?php echo $nextpre_icons_padding_top; ?>px <?php echo $nextpre_icons_padding_right; ?>px <?php echo $nextpre_icons_padding_bottom; ?>px <?php echo $nextpre_icons_padding_left; ?>px !important;
    <?php if ( isset( $nextpre_icons_background['enable'] ) && 0 == $nextpre_icons_background['enable'] ) { ?>
    opacity: 1 !important;
    <?php } else { ?>
    opacity: <?php echo ( $nextpre_icons_opacity / 100 ); ?> !important;
    <?php } ?>
}
.a3-dgallery .a3dg-image-wrapper .a3dg-prev {
    left: <?php echo $nextpre_icons_margin_left; ?>px !important;
}
.a3-dgallery .a3dg-image-wrapper .a3dg-next {
    right: <?php echo $nextpre_icons_margin_right; ?>px !important;
}

/* Pause | Play icon */
.a3-dgallery .fa-pause:before,
.a3-dgallery .fa-play:before  {
    font-size: <?php echo $pauseplay_icon_size; ?>px !important;
    color: <?php echo $pauseplay_icon_color; ?> !important;
}

.a3dg-image-wrapper .slide-ctrl .a3dg-slideshow-start-slide,
.a3dg-image-wrapper .slide-ctrl .a3dg-slideshow-stop-slide {
    <?php echo $wc_dgallery_admin_interface->generate_background_color_css( $pauseplay_icon_background ); ?>
    <?php echo $wc_dgallery_admin_interface->generate_border_css( $pauseplay_icon_border ); ?>
    <?php echo $wc_dgallery_admin_interface->generate_shadow_css( $pauseplay_icon_shadow ); ?>
    padding: <?php echo $pauseplay_icon_padding_top; ?>px <?php echo $pauseplay_icon_padding_right; ?>px <?php echo $pauseplay_icon_padding_bottom; ?>px <?php echo $pauseplay_icon_padding_left; ?>px !important;
    <?php if ( isset( $pauseplay_icon_background['enable'] ) && 0 == $pauseplay_icon_background['enable'] ) { ?>
    opacity: 1 !important;
    <?php } else { ?>
    opacity: <?php echo ( $pauseplay_icon_opacity / 100 ); ?> !important;
    <?php } ?>
}

.a3dg-image-wrapper .slide-ctrl {

<?php if ( 'top' == $pauseplay_icon_vertical_position ) { ?>
top: 0 !important;
margin-top: <?php echo $pauseplay_icon_margin_top; ?>px !important;
<?php } elseif ( 'bottom' == $pauseplay_icon_vertical_position ) { ?>
top: auto !important;
bottom: 0 !important;
margin-bottom: <?php echo $pauseplay_icon_margin_bottom; ?>px !important;
<?php } ?>

<?php if ( 'left' == $pauseplay_icon_horizontal_position ) { ?>
left: 0 !important;
margin-left: <?php echo $pauseplay_icon_margin_left; ?>px !important;
<?php } elseif ( 'right' == $pauseplay_icon_horizontal_position ) { ?>
left: auto !important;
right: 0 !important;
margin-right: <?php echo $pauseplay_icon_margin_right; ?>px !important;
<?php } ?>
}

/* Thumbnail Slider Next / Previous icons */
.a3-dgallery .fa-angle-left:before,
.a3-dgallery .fa-angle-right:before  {
    font-size: <?php echo $thumb_nextpre_icons_size; ?>px !important;
    color: <?php echo $thumb_nextpre_icons_color; ?> !important;
}

.a3-dgallery .a3dg-forward,
.a3-dgallery .a3dg-back {
    <?php echo $wc_dgallery_admin_interface->generate_background_color_css( $thumb_nextpre_icons_background ); ?>
    <?php echo $wc_dgallery_admin_interface->generate_border_css( $thumb_nextpre_icons_border ); ?>
    <?php echo $wc_dgallery_admin_interface->generate_shadow_css( $thumb_nextpre_icons_shadow ); ?>
    padding-left: <?php echo $thumb_nextpre_icons_padding_left; ?>px !important;
    padding-right: <?php echo $thumb_nextpre_icons_padding_right; ?>px !important;
}

<?php if ( 'slider' == $thumb_show_type ) { ?>
/* Thumbnail Slider Container */
.a3-dgallery .a3dg-nav {
    <?php echo $wc_dgallery_admin_interface->generate_background_color_css( $thumb_slider_background ); ?>
    <?php echo $wc_dgallery_admin_interface->generate_border_css( $thumb_slider_border ); ?>
    <?php echo $wc_dgallery_admin_interface->generate_shadow_css( $thumb_slider_shadow ); ?>
}
<?php } ?>

</style>
