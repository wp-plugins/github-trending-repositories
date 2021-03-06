<?php
/*
 * Plugin Name: Github Trending Repositories
 * Description: Show Trending Github Repositories
 * Version: 1.0
 * Author: Aqib Gatoo
 * Author URI: http://aqibgatoo.com
 * License: GPLv2

*/

//
function github_widget_init() {
	//register widget
	register_widget( 'github_widget' );
}

// Action Hook for adding widget
add_action( 'widgets_init', 'github_widget_init' );

/**
 * Adds github_widget widget.
 */
class github_widget extends WP_Widget {

	/**
	 * Register widget with WordPress.
	 */
	function __construct() {
		parent::__construct(
			'github_widget',
			__( 'Trending Repositories', 'text_domain' ),
			array(
				'classname' => 'github-widget',
				'description' => __( 'Shows Trending Github Repositories', 'text_domain' )
			)
		);
	}

	/**
	 * Front-end display of widget.
	 */
	public function widget( $args, $instance ) {


		$repositories = $this->get_trending_repos();

		echo $args['before_widget'];
		if ( !empty( $instance['title'] ) ) {
			echo $args['before_title'] . apply_filters( 'widget_title', $instance['title'] ) . $args['after_title'];
		}

		if ( $repositories !== false ) {
             echo '<ul>';
			for ( $i = 0; $i < 6; $i ++ ) {
				echo "<li><a href='" . $repositories->repoUrl[$i] . "'>" . $repositories->repoName[$i] . "</a></li>";
			}
		} else {	
			echo "<p>Unable to fetch data.Try again</p>";
		}

		echo $args['after_widget'];
	}

	private function get_trending_repos() {

		$repositories = get_transient( 'my_github_repositories' );

		if ( !$repositories ) {

			$repositories = $this->fetch_repos( "https://www.kimonolabs.com/api/9kg7km3i?apikey=VCzI2k65176RZW2VzF87sdiisii5rhJN" );
		}

		return $repositories;
	}

	private function fetch_repos( $url ) {
		$options = array( 'timeout' => 200 );
		$response = wp_remote_get( $url, $options );

		if ( is_wp_error( $response ) ) {
			return false;
		}
		$payload = json_decode( $response['body'] );

		$repos = new stdClass();
		$repos->repoUrl = array();
		$repos->repoName = array();

		for ( $i = 0; $i < 6; $i ++ ) {
			$repos->repoUrl[] = $payload->results->repos[$i]->name->href;
			$repos->repoName[] = $payload->results->repos[$i]->name->text;
		}

		set_transient( 'my_github_repositories', $repos, 60 * 30 );

		return $repos;
	}

	/**
	 * Back-end widget form.
	 */
	public function form( $instance ) {
		$title = !empty( $instance['title'] ) ? $instance['title'] : __( 'Trending Repositories', 'text_domain' );
		?>
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>"
			       name="<?php echo $this->get_field_name( 'title' ); ?>" type="text"
			       value="<?php echo esc_attr( $title ); ?>">
		</p>
		<?php
	}

	/**
	 * Sanitize widget form values as they are saved.
	 */
	public function update( $new_instance, $old_instance ) {
		$instance = array();
		$instance['title'] = ( !empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';

		return $instance;
	}


} // class github_widget

add_action( 'wp_enqueue_scripts', 'github_widget_css' );

function github_widget_css() {

	wp_enqueue_style( 'github-widget-style-css', plugins_url( 'github-trending-style.css',__FILE__ ) );

}
