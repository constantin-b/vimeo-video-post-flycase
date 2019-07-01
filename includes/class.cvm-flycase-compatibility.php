<?php

class CVM_Flycase_Actions_Compatibility {
	/**
	 * Theme name
	 * @var string
	 */
	private $theme_name;

	/**
	 * CVM_Flycase_Actions_Compatibility constructor.
	 *
	 * @param string $theme_name
	 */
	public function __construct( $theme_name ) {
		$this->theme_name = $theme_name;
		add_filter( 'cvm_theme_support', array( $this, 'theme_support' ) );
		add_filter( 'cvm_video_post_content', array( $this, 'add_url_to_content' ), 10, 3 );
		add_action( 'cvm_post_insert', array( $this, 'action_post_insert' ), 10, 4 );
	}

	/**
	 * @param array $themes
	 *
	 * @return array
	 */
	public function theme_support( $themes ) {
		$theme_name = strtolower( $this->theme_name );
		$themes[ $theme_name ] = array(
			'post_type'    => 'video',
			'taxonomy'     => 'video_type',
			'tag_taxonomy' => 'video_tag',
			'post_meta'    => array(),
			'post_format'  => 'video',
			'theme_name'   => $this->theme_name,
			'url'          => 'https://themeforest.net/item/flycase-complete-music-solution-for-wordpress/6193502/?ref=cboiangiu',
		);

		return $themes;
	}

	/**
	 * @param $post_content
	 * @param $video
	 * @param $theme_import
	 *
	 * @return string
	 */
	public function add_url_to_content( $post_content, $video, $theme_import ) {
		if ( ! $theme_import ) {
			return $post_content;
		}

		$url = cvm_video_url( $video['video_id'] );
		return $url . "\n" . $post_content ;
	}

	public function action_post_insert( $post_id, $video, $theme_import, $post_type ){
		if( !$theme_import ){
			return;
		}

		$url = cvm_video_url( $video['video_id'] );

		update_post_meta( $post_id, '_wv_video_url', $url );

		// get embed plugin options
		$settings = cvm_get_player_settings();
		$params = array(
			'title' => $settings[ 'title' ],
			'byline' => $settings[ 'byline' ],
			'portrait' => $settings[ 'portrait' ],
			'loop' => $settings[ 'loop' ],
			'color' => $settings[ 'color' ],
			//'fullscreen' => $settings[ 'fullscreen' ]
		);
		$embed_url = 'https://player.vimeo.com/video/' . $video[ 'video_id' ] . '?' . http_build_query( $params, '', '&' );
		$embed_html = '<iframe class="%s" src="' . $embed_url . '" %s frameborder="0" webkitAllowFullScreen mozallowfullscreen allowFullScreen></iframe>';

		update_post_meta( $post_id, '_wv_video_iframe', sprintf( $embed_html, 'wv-vimeo', 'width="640" height="360"' ) );

		update_post_meta( $post_id, '_wv_video_iframe_bg', sprintf( $embed_html, '_wv_video_iframe_bg', '' ) );
	}

}