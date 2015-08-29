<?php
/*
	Plugin Name: MT8 Secret Comments
	Plugin URI: https://github.com/mt8/mt8-secret-comments
	Description: Write a comment visible only to admin.
	Author: mt8.biz
	Version: 1.0.1
	Author URI: http://mt8.biz
	Domain Path: /languages
	Text Domain: mt8-secret-comments
*/	

	$mt8_sc = new Mt8_Secret_Comments();
	$mt8_sc->register_hooks();

	class Mt8_Secret_Comments {

		const TEXT_DOMAIN = 'mt8-secret-comments';
		const META_KEY = 'mt8_secret_comments';
		
		public function __construct() {
			
		}

		public function register_hooks() {
			
			add_action( 'plugins_loaded', array( &$this, 'plugins_loaded' ) );
			
			add_action( 'admin_init', array( &$this, 'admin_init' ) );
			add_filter( 'comment_save_pre', array( &$this, 'comment_save_pre' ) );			
			add_filter( 'comment_form_field_comment', array( &$this, 'comment_form_field_comment' ) );
			
			add_action( 'comment_post', array( &$this, 'comment_post' ) );
			add_filter( 'get_comment_text', array( &$this, 'get_comment_text' ), 10, 3 );
			
			add_filter( 'manage_edit-comments_columns', array( &$this, 'manage_edit_comments_columns' ) );			
			add_filter( 'manage_comments_custom_column', array( &$this, 'manage_comments_custom_column'), 10, 2 );		

		}
		
		public function plugins_loaded() {
			
			load_plugin_textdomain( self::TEXT_DOMAIN, false, dirname( plugin_basename( __FILE__ ) ).'/languages' );
			
		}
		
		public function admin_init() {

			add_meta_box( 'mt8_sc', __( 'Show only admin.', self::TEXT_DOMAIN ), array( &$this, 'add_meta_box_comment' ), 'comment', 'normal' );
						
		}
		
		public function add_meta_box_comment() {
			
			global $comment_id;
			$secret = get_comment_meta( $comment_id, self::META_KEY, true );
			$checked = checked( $secret, 1, false );
			$comment_field = 
			'
			<label for="'.self::META_KEY.'">
				<input name="'.self::META_KEY.'" id="'.self::META_KEY.'" type="checkbox" value="1" ' . $checked . '>
			</label>
			';
			echo $comment_field;
		}
		
		public function comment_save_pre( $comment_content ) {
			
			global $comment_id;
			$this->comment_post( $comment_id );
			
			return $comment_content;
		}

		public function comment_form_field_comment( $comment_field ) {

			$comment_field .= 
			'
			<label for="'.self::META_KEY.'">
				<input name="'.self::META_KEY.'" id="'.self::META_KEY.'" type="checkbox" value="1" >
				' . __( 'Show only admin.', self::TEXT_DOMAIN ) . '
			</label>
			';
			return $comment_field;

		}
    
		public function comment_post( $comment_id ) {
			
			if ( isset( $_POST[self::META_KEY] ) ) {
				$mt8_sc = ( in_array( $_POST[ self::META_KEY ], array( '0', '1' ) ) ) ? $_POST[ self::META_KEY ] : '0';
			} else {
				$mt8_sc = '0';
			}
			update_comment_meta( $comment_id, self::META_KEY, $mt8_sc );
			
		}
		
		public function get_comment_text( $comment_content, $comment, $args ) {

			if ( !is_singular() ) {
				return $comment_content;
			}
			
			if ( $this->is_secret( $comment->comment_ID ) && ! current_user_can( 'administrator' ) ) {
				return __( 'Only administrators can see this comment.', self::TEXT_DOMAIN );
			} else {
				return $comment_content;
			}
			
		}

		public function manage_edit_comments_columns( $columns ) {
			
			$columns['mt8_sc'] = __( 'Show only admin.', self::TEXT_DOMAIN );
			return $columns;
			
		}

		public function manage_comments_custom_column( $column, $comment_id ) {

			if ( 'mt8_sc' == $column ) {
				if ( $this->is_secret($comment_id) ) {
					echo '*';
				}
			}

		}
		
		public function is_secret( $comment_id ) {
			
			return ( get_comment_meta( $comment_id, self::META_KEY, true ) == '1' );
			
		}
		
	}