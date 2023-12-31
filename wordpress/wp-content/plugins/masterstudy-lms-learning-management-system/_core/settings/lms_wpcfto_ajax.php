<?php // phpcs:ignore WordPress.Files.FileName.InvalidClassFileName

new STM_LMS_WPCFTO_AJAX();

class STM_LMS_WPCFTO_AJAX {

	protected static $available_post_types       = array( 'stm-lessons', 'stm-quizzes', 'stm-questions', 'stm-assignments', 'stm-google-meets' );
	protected static $possible_taxonomies        = array(
		'stm_lms_course_taxonomy',
		'stm_lms_question_taxonomy',
	);
	protected static $question_category_taxonomy = 'stm_lms_question_taxonomy';

	public function __construct() {
		add_action( 'wp_ajax_stm_curriculum_create_item', array( $this, 'stm_curriculum_create_item' ) );

		add_action( 'wp_ajax_stm_curriculum_get_item', array( $this, 'stm_curriculum_get_item' ) );

		add_action( 'wp_ajax_stm_save_questions', array( $this, 'stm_save_questions' ) );

		add_action( 'wp_ajax_stm_lms_terms', array( $this, 'stm_lms_terms' ) );

		add_action( 'wp_ajax_stm_lms_questions', array( $this, 'stm_lms_questions' ) );

		add_action( 'wp_ajax_stm_lms_create_term', array( $this, 'stm_lms_create_term' ) );

		add_action( 'wp_ajax_stm_save_title', array( $this, 'stm_save_title' ) );

		add_filter( 'stm_wpcfto_autocomplete_review_user', array( $this, 'users_search' ), 10, 2 );
	}

	/**
	 * Ajax query to get stm questions
	 */
	public static function stm_lms_questions() {
		check_ajax_referer( 'stm_lms_questions', 'nonce' );

		$result = array();

		$args = array(
			'posts_per_page' => 10,
		);

		if ( isset( $_GET['ids'] ) && empty( $_GET['ids'] ) ) {
			wp_send_json( $result );
		}

		if ( ! empty( $_GET['post_types'] ) ) {
			$args['post_type'] = explode( ',', sanitize_text_field( $_GET['post_types'] ) );
		}

		if ( ! empty( $_GET['s'] ) ) {
			$args['s'] = sanitize_text_field( $_GET['s'] );
		}

		if ( isset( $_GET['ids'] ) ) {
			$args['post__in'] = explode( ',', sanitize_text_field( $_GET['ids'] ) );
		}

		if ( ! empty( $_GET['exclude_ids'] ) ) {
			$args['post__not_in'] = explode( ',', sanitize_text_field( $_GET['exclude_ids'] ) );
		}

		if ( ! empty( $_GET['orderby'] ) ) {
			$args['orderby'] = sanitize_text_field( $_GET['orderby'] );
		}

		if ( ! empty( $_GET['posts_per_page'] ) ) {
			$args['posts_per_page'] = sanitize_text_field( $_GET['posts_per_page'] );
		}

		if ( ! empty( $_GET['order'] ) ) {
			$args['order'] = sanitize_text_field( $_GET['order'] );
		}

		$user  = wp_get_current_user();
		$roles = (array) $user->roles;

		if ( ! in_array( 'administrator', $roles, true ) ) {
			$args['author'] = get_current_user_id();
		}

		$args = apply_filters( 'wpcfto_search_posts_args', $args );

		$q = new WP_Query( $args );
		if ( $q->have_posts() ) {
			while ( $q->have_posts() ) {
				$q->the_post();

				$id = get_the_ID();

				if ( empty( $id ) ) {
					continue;
				}

				$response = array(
					'id'         => get_the_ID(),
					'title'      => get_the_title(),
					'post_type'  => get_post_type( get_the_ID() ),
					'excerpt'    => get_the_excerpt( get_the_ID() ),
					'image'      => get_the_post_thumbnail_url( get_the_ID(), 'thumbnail' ),
					'is_edit'    => false,
					'categories' => wp_get_post_terms( get_the_ID(), 'stm_lms_question_taxonomy' ),
				);

				$result[] = apply_filters( 'wpcfto_search_posts_response', $response, $args['post_type'] );
			}

			wp_reset_postdata();
		}

		if ( ! empty( $_GET['ids'] ) ) {
			$insert_sections = array();

			foreach ( $args['post__in'] as $key => $post ) {
				if ( ! empty( $post ) && ! is_numeric( $post ) ) {
					$insert_sections[ $key ] = array(
						'id'    => $post,
						'title' => $post,
					);
				}
			}

			foreach ( $insert_sections as $position => $inserted ) {
				array_splice( $result, $position, 0, array( $inserted ) );
			}
		}

		wp_send_json( $result );
	}

	/**
	 * Ajax query to get stm term taxonomy
	 */
	public static function stm_lms_terms() {
		check_ajax_referer( 'stm_lms_terms', 'nonce' );

		$taxonomy = '';
		if ( ! empty( $_GET['taxonomy'] ) && in_array( trim( $_GET['taxonomy'] ), self::$possible_taxonomies ) ) {
			$taxonomy = sanitize_text_field( $_GET['taxonomy'] );
		}

		$result = self::stm_get_terms( $taxonomy );
		wp_send_json( $result );
	}

	/**
	 * Get stm terms by taxonomy
	 */
	private static function stm_get_terms( $taxonomy = 'stm_lms_question_taxonomy' ) {
		$term_query = new WP_Term_Query(
			array(
				'taxonomy'   => $taxonomy,
				'orderby'    => 'name',
				'order'      => 'ASC',
				'parent'     => 0,
				'fields'     => 'all',
				'hide_empty' => false,
			)
		);

		foreach ( $term_query->terms as $term ) {
			$result[] = array_merge(
				(array) $term,
				array(
					'id' => $term->term_id,
					'show' => true,
				)
			);
		}
		return $result;
	}

	/**
	 * Ajax query to create term
	 */
	public static function stm_lms_create_term() {

		if ( empty( $_GET['taxonomy'] ) || ! in_array( trim( $_GET['taxonomy'] ), self::$possible_taxonomies ) ) {
			wp_send_json(
				array(
					'error'   => true,
					'message' => esc_html__( 'Taxonomy is required', 'masterstudy-lms-learning-management-system' ),
				)
			);
		}

		if ( empty( $_GET['name'] ) ) {
			wp_send_json(
				array(
					'error'   => true,
					'message' => esc_html__( 'Name is required', 'masterstudy-lms-learning-management-system' ),
				)
			);
		}

		$taxonomy = sanitize_text_field( $_GET['taxonomy'] );
		$name     = sanitize_text_field( $_GET['name'] );

		$result = wp_insert_term( $name, $taxonomy, array( 'parent' => 0 ) );
		$list   = self::stm_get_terms( $taxonomy );

		wp_send_json(
			array(
				'list'    => $list,
				'term_id' => $result['term_id'],
				'name'    => $name,
			)
		);
	}

	public static function stm_curriculum_create_item() {

		check_ajax_referer( 'stm_curriculum_create_item', 'nonce' );

		/*Check if data passed*/
		if ( empty( sanitize_text_field( $_GET['post_type'] ) ) ) {
			wp_send_json(
				array(
					'error'   => true,
					'message' => esc_html__( 'Post Type is required', 'masterstudy-lms-learning-management-system' ),
				)
			);
		}

		/*Check if data passed*/
		if ( empty( $_GET['title'] ) ) {
			wp_send_json(
				array(
					'error'   => true,
					'message' => esc_html__( 'Title is required', 'masterstudy-lms-learning-management-system' ),
				)
			);
		}

		$category_ids = null; // Question categories
		$post_type    = sanitize_text_field( $_GET['post_type'] );
		$title        = sanitize_text_field( urldecode( $_GET['title'] ) );

		// comma separated category ids
		if ( ! empty( $_GET['category_ids'] ) ) {
			$category_ids = sanitize_text_field( $_GET['category_ids'] );
			$category_ids = array_map( 'intval', explode( ',', $category_ids ) );
		}

		/*Check if available post type*/
		if ( ! in_array( $post_type, apply_filters( 'stm_lms_available_post_types', self::$available_post_types ) ) ) {
			wp_send_json(
				array(
					'error'   => true,
					'message' => esc_html__( 'Wrong post type', 'masterstudy-lms-learning-management-system' ),
				)
			);
		}

		if ( ! apply_filters( 'stm_lms_allow_add_lesson', true ) && 'stm-lessons' === $post_type ) {
			return;
		}

		$result   = array();
		$is_front = (bool) ( ! empty( $_GET['is_front'] ) ) ? sanitize_text_field( $_GET['is_front'] ) : false;
		$item     = array(
			'post_type'   => $post_type,
			'post_title'  => html_entity_decode( $title ),
			'post_status' => 'publish',
		);

		$result['id'] = wp_insert_post( $item );

		/** add question category if was sent */
		if ( null !== $category_ids ) {
			wp_set_object_terms( $result['id'], $category_ids, 'stm_lms_question_taxonomy' );
		}

		do_action(
			'stm_lms_item_added',
			array(
				'id'    => $result['id'],
				'front' => $is_front,
			)
		);

		$result['categories'] = wp_get_post_terms( $result['id'], 'stm_lms_question_taxonomy' );
		$result['is_edit']    = false;
		$result['title']      = html_entity_decode( get_the_title( $result['id'] ) );
		$result['post_type']  = $post_type;
		$result['edit_link']  = html_entity_decode( ms_plugin_edit_item_url( $result['id'], $post_type ) );

		$result = apply_filters( 'stm_lms_wpcfto_create_question', $result, array( $post_type ) );

		do_action(
			'stm_lms_item_question_added',
			array(
				'id'    => $result['id'],
				'front' => $is_front,
			)
		);

		wp_send_json( $result );
	}

	public function stm_curriculum_get_item() { // phpcs:ignore Squiz.Scope.MethodScope.Missing

		check_ajax_referer( 'stm_curriculum_get_item', 'nonce' );

		$post_id = intval( $_GET['id'] );
		$r       = array();

		$r['meta'] = STM_LMS_Helpers::simplify_meta_array( get_post_meta( $post_id ) );
		if ( ! empty( $r['meta']['lesson_video_poster'] ) ) {
			$image = wp_get_attachment_image_src( $r['meta']['lesson_video_poster'], 'img-870-440' );
			if ( ! empty( $image[0] ) ) {
				$r['meta']['lesson_video_poster_url'] = $image[0];
			}
		}
		if ( ! empty( $r['meta']['lesson_video'] ) ) {
			$video = wp_get_attachment_url( $r['meta']['lesson_video'] );

			if ( ! empty( $video ) ) {
				$r['meta']['uploaded_lesson_video'] = $video;
			}
		}
		if ( ! empty( $r['meta']['lesson_files_pack'] ) ) {
			$r['meta']['lesson_files_pack'] = json_decode( $r['meta']['lesson_files_pack'] );
		}
		$r['content'] = get_post_field( 'post_content', $post_id );

		wp_send_json( $r );
	}

	public function stm_save_questions() { // phpcs:ignore Squiz.Scope.MethodScope.Missing

		check_ajax_referer( 'stm_save_questions', 'nonce' );

		$r            = array();
		$request_body = file_get_contents( 'php://input' );

		do_action( 'stm_lms_before_save_questions' );

		if ( ! empty( $request_body ) ) {

			$fields = STM_LMS_WPCFTO_HELPERS::get_question_fields();

			$data = json_decode( $request_body, true );

			foreach ( $data as $question ) {

				/** add question category part */
				if ( array_key_exists( 'categories', $question ) ) {
					$exist_categories   = wp_get_post_terms( $question['id'], self::$question_category_taxonomy );
					$exist_category_ids = array_column( $exist_categories, 'term_id' );

					/** remove old terms connection */
					if ( count( $exist_category_ids ) > 0 ) {
						wp_remove_object_terms( $question['id'], $exist_category_ids, self::$question_category_taxonomy );
					}

					$category_ids = array_column( $question['categories'], 'term_id' );
					/** append new terms connection */
					if ( count( $category_ids ) > 0 ) {
						wp_set_object_terms( $question['id'], $category_ids, self::$question_category_taxonomy );
					}
				}
				/** add question category part | End */

				if ( empty( $question['id'] ) ) {
					continue;
				}
				$post_id = $question['id'];

				foreach ( $fields as $field_key => $field ) {
					if ( isset( $question[ $field_key ] ) ) {
						foreach ( $question[ $field_key ] as $index => $value ) {
							if ( is_array( $question[ $field_key ][ $index ] ) ) {
								$question[ $field_key ][ $index ]['text'] = sanitize_text_field( wp_slash( $value['text'] ) );
							}
						}

						$r[ $field_key ] = update_post_meta( $post_id, $field_key, $question[ $field_key ] );
					}
				}
			}
		}

		wp_send_json( $r );
	}

	public function stm_save_title() { // phpcs:ignore Squiz.Scope.MethodScope.Missing

		check_ajax_referer( 'stm_save_title', 'nonce' );

		if ( empty( $_GET['id'] ) && ! empty( $_GET['title'] ) ) {
			return false;
		}

		$post = array(
			'ID'         => intval( $_GET['id'] ),
			'post_title' => sanitize_text_field( $_GET['title'] ),
		);

		wp_update_post( $post );

		wp_send_json( $post );
	}

	public function users_search( $r, $args ) { // phpcs:ignore Squiz.Scope.MethodScope.Missing

		$s_args = array();

		if ( ! empty( $_GET['s'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$s                        = sanitize_text_field( $_GET['s'] );
			$s_args['search']         = "*{$s}*";
			$s_args['search_columns'] = array(
				'user_login',
				'user_nicename',
			);
		}

		if ( ! empty( $_GET['ids'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$s_args['include'] = explode( ',', sanitize_text_field( $_GET['ids'] ) );
		}

		$users = new WP_User_Query( $s_args );
		$users = $users->get_results();

		$data = array();

		foreach ( $users as $user ) {
			$data[] = array(
				'id'        => $user->ID,
				'title'     => $user->data->user_nicename,
				'post_type' => 'user',
			);
		}

		return $data;

	}
}
