<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$group_id = isset( $lightbox_data['group_id'] ) ? sanitize_key( (string) $lightbox_data['group_id'] ) : '';
$settings = isset( $lightbox_data['settings'] ) && is_array( $lightbox_data['settings'] ) ? $lightbox_data['settings'] : array();

if ( '' === $group_id ) {
	return;
}

$config = array(
	'loop'                => isset( $settings['loop'] ) ? (bool) $settings['loop'] : true,
	'zoomable'            => isset( $settings['zoomable'] ) ? (bool) $settings['zoomable'] : true,
	'draggable'           => isset( $settings['draggable'] ) ? (bool) $settings['draggable'] : true,
	'closeOnOutsideClick' => isset( $settings['closeOnOutsideClick'] ) ? (bool) $settings['closeOnOutsideClick'] : true,
	'openEffect'          => isset( $settings['openEffect'] ) ? sanitize_key( (string) $settings['openEffect'] ) : 'zoom',
	'slideEffect'         => isset( $settings['slideEffect'] ) ? sanitize_key( (string) $settings['slideEffect'] ) : 'slide',
	'descPosition'        => isset( $settings['descPosition'] ) ? sanitize_key( (string) $settings['descPosition'] ) : 'bottom',
);
?>
<div
	class="fg-lightbox-config"
	data-fg-lightbox-provider="glightbox"
	data-fg-group="<?php echo esc_attr( $group_id ); ?>"
	data-fg-config="<?php echo esc_attr( wp_json_encode( $config ) ); ?>"
	hidden
></div>
