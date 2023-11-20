<?php

$init_data = array(
	'lms-pages',
	'database',
	'old-course-builder',
);

if ( ! stm_lms_has_generated_pages( stm_lms_generate_pages_list() ) ) {
	$init_data['lms-pages'] = array(
		'notice_type'          => 'cog-notice',
		'notice_logo'          => 'cog.svg',
		'notice_title'         => esc_html__( 'The LMS pages are not specified!', 'masterstudy-lms-learning-management-system' ),
		'notice_desc'          => esc_html__( 'Please create pages and indicate them in the LMS settings or use the page generator.', 'masterstudy-lms-learning-management-system' ),
		'notice_btn_one_title' => esc_html__( 'Open settings', 'masterstudy-lms-learning-management-system' ),
		'notice_btn_one_class' => 'ms_settings_open',
		'notice_btn_one'       => admin_url( 'admin.php?page=stm-lms-settings#section_routes' ),
	);
}

$current_database = get_option( 'stm_lms_db_version', 1 );
$has_new_database = version_compare( STM_LMS_DB_VERSION, $current_database );
if ( $has_new_database ) {
	$init_data['database'] = array(
		'notice_type'          => 'animate-triangle-notice',
		'notice_logo'          => 'attent_triangle.svg',
		'notice_title'         => esc_html__( 'MasterStudy LMS database update required', 'masterstudy-lms-learning-management-system' ),
		'notice_desc'          => esc_html__( 'We added new features, and need to update your database to latest version.', 'masterstudy-lms-learning-management-system' ),
		'notice_btn_one'       => '#',
		'notice_btn_one_title' => esc_html__( 'Update', 'masterstudy-lms-learning-management-system' ),
		'notice_btn_one_class' => 'ms-lms-table-update',
	);
}

$notice_status = get_option( 'course_builder_notice_status' );
$current_date  = gmdate( 'Y-m-d H:i' );
$deadline      = '2023-09-01 00:00';
$difference    = strtotime( $deadline ) - strtotime( $current_date );
$days          = floor( $difference / ( 60 * 60 * 24 ) );
$hours         = floor( ( $difference % ( 60 * 60 * 24 ) ) / ( 60 * 60 ) );
$minutes       = floor( ( $difference % ( 60 * 60 ) ) / 60 );
$seconds       = $difference % 60;

if ( empty( $notice_status ) && $difference > 0 ) {
	$init_data['old-course-builder'] = array(
		'notice_type'          => 'cb-info',
		'notice_logo'          => 'ms.svg',
		'notice_title'         => esc_html__( 'Legacy course creation process will be entirely replaced by the new Сourse Builder starting from September 1st, 2023.', 'masterstudy-lms-learning-management-system' ),
		'notice_desc'          => esc_html__( 'The legacy course creation process will be switched to the new Course Builder interface starting from September 1st, 2023. All your existing content (courses, lessons, quizzes, assignments, etc.) will be safely transferred to the new course builder interface.', 'masterstudy-lms-learning-management-system' ),
		'notice_btn_two'       => 'https://stylemixthemes.com/wp/masterstudy-lms-ending-support-for-legacy-course-creation/',
		'notice_btn_two_title' => esc_html__( 'Learn more', 'masterstudy-lms-learning-management-system' ),
		'notice_btn_one'       => 'https://forms.gle/fKs9vssCKSzJ9DQV6',
		'notice_btn_one_title' => esc_html__( 'Report a problem', 'masterstudy-lms-learning-management-system' ),
		'notice_btn_one_class' => 'course-builder-notice',
	);

	function stm_lms_add_notice_popup_script() {
		wp_enqueue_script( 'ms_lms_notice-popup', STM_LMS_URL . 'assets/js/notices/notices.js', array(), STM_LMS_VERSION, true );
		wp_localize_script(
			'ms_lms_notice-popup',
			'ms_lms_notice_data',
			array(
				'nonce'    => wp_create_nonce( 'skip_cb_popup' ),
				'ajax_url' => admin_url( 'admin-ajax.php' ),
			)
		);
	}
	add_action( 'admin_enqueue_scripts', 'stm_lms_add_notice_popup_script' );

	function stm_lms_ajax_close_cb_notice() {
		check_ajax_referer( 'skip_cb_popup', 'security' );
		update_option( 'course_builder_notice_status', sanitize_text_field( $_GET['add_pear_hb_status'] ) );
	}
	add_action( 'wp_ajax_stm_close_cb_notice', 'stm_lms_ajax_close_cb_notice' );
}

foreach ( $init_data as $item ) {
	stm_admin_notices_init( $item );
}

$themes       = array( 'ms-lms-starter-theme', 'masterstudy', 'globalstudy', 'smarty' );
$theme_exists = array_reduce(
	$themes,
	function ( $carry, $theme ) {
		return $carry || wp_get_theme( $theme )->exists();
	},
	false
);

if ( ! $theme_exists ) {
	function stm_lms_add_starter_theme_notice_popup() {
		$skip_key    = 'starter_theme';
		$install_url = add_query_arg(
			array( 'page' => 'starter_lms_demo_installer' ),
			admin_url( 'themes.php' ),
		);
		$rate_item   = new \ANP\Popup\Theme\ItemStarterTheme(
			'https://s3-us-west-2.amazonaws.com/freemius/plugins/3434/icons/d69c5c1be41a4ff8b5de0f1989bd3473.png',
			'Thank you for installing the Masterstudy LMS Plugin.',
			'What to do next, try the Masterstudy Starter theme.',
			$install_url,
			'https://masterstudy.stylemixthemes.com/lms-plugin/',
			$skip_key
		);

		ANP\NotificationEnqueueControl::addSecondItem( $skip_key, $rate_item->createHtml() );
	}
	add_action( 'anp_popup_items', 'stm_lms_add_starter_theme_notice_popup', 10, 1 );
}

if ( ! class_exists( 'RateNotification' ) ) {
	require_once STM_LMS_LIBRARY . '/admin-notification-popup/classes/RateNotification.php';
}

$rate_data = array(
	'plugin_title' => 'Masterstudy LMS Plugin',
	'plugin_name'  => 'masterstudy-lms-learning-management-system',
	'plugin_file'  => MS_LMS_FILE,
	'logo'         => STM_LMS_URL . 'assets/img/ms-logo.png',
);

RateNotification::init( $rate_data );
