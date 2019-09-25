<?php
/**
 * An extension for the Connections Business Directory which limits the organizations to previously entered organization when adding an individual.
 *
 * @package   Connections Business Directory Extension - Organization Individuals
 * @category  Extension
 * @author    Steven A. Zahm
 * @license   GPL-2.0+
 * @link      https://connections-pro.com
 * @copyright 2019 Steven A. Zahm
 *
 * @wordpress-plugin
 * Plugin Name:       Connections Business Directory Extension - Organization Individuals
 * Plugin URI:        https://connections-pro.com
 * Description:       An extension for the Connections Business Directory which limits the organizations to previously entered organization when adding an individual.
 * Version:           1.0
 * Author:            Steven A. Zahm
 * Author URI:        https://connections-pro.com
 * License:           GPL-2.0+
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       connections-entry-organization
 * Domain Path:       /languages
 */

if ( ! class_exists( 'Connections_Entry_Organization' ) ) :

	final class Connections_Entry_Organization {

		/**
		 * @since 1.0
		 */
		const VERSION = '1.0';

		/**
		 * Stores the instance of this class.
		 *
		 * @var $instance Connections_Entry_Organization
		 *
		 * @access private
		 * @static
		 * @since  1.0
		 */
		private static $instance;

		/**
		 * @var string The absolute path this this file.
		 *
		 * @since 1.1
		 */
		private static $file = '';

		/**
		 * @var string The URL to the plugin's folder.
		 *
		 * @since 1.1
		 */
		private static $url = '';

		/**
		 * @var string The absolute path to this plugin's folder.
		 *
		 * @since 1.1
		 */
		private static $path = '';

		/**
		 * @var string The basename of the plugin.
		 *
		 * @since 1.1
		 */
		private static $basename = '';

		/**
		 * A dummy constructor to prevent the class from being loaded more than once.
		 *
		 * @access public
		 * @since  1.0
		 */
		public function __construct() { /* Do nothing here */ }

		/**
		 * The main plugin instance.
		 *
		 * @access  private
		 * @static
		 * @since   1.0
		 *
		 * @return Connections_Entry_Organization
		 */
		public static function instance() {

			if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Connections_Entry_Organization ) ) {

				self::$instance = $self = new self;

				self::$file       = __FILE__;
				self::$url        = plugin_dir_url( self::$file );
				self::$path       = plugin_dir_path( self::$file );
				self::$basename   = plugin_basename( self::$file );

				self::hooks();

				/**
				 * This should run on the `plugins_loaded` action hook. Since the extension loads on the
				 * `plugins_loaded` action hook, load immediately.
				 */
				cnText_Domain::register(
					'connections-entry-organization',
					self::$basename,
					'load'
				);

			}

			return self::$instance;
		}

		/**
		 * Register the plugin's hooks.
		 *
		 * @access private
		 * @static
		 * @since  1.0
		 */
		private static function hooks() {

			// Enqueue the admin scripts and CSS.
			add_action( 'cn_admin_enqueue_edit_scripts', array( __CLASS__, 'enqueueAdminScripts' ) );

			// Register the valid entry types.
			add_filter( 'cn_metabox_publish_atts', array( __CLASS__, 'registerEntryTypes' ), 11 );
		}

		/**
		 * Callback for the `cn_admin_enqueue_edit_scripts` action.
		 *
		 * @param string $pageHook
		 */
		public static function enqueueAdminScripts( $pageHook ) {

			$baseURL = plugin_dir_url( __FILE__ );

			wp_enqueue_script( 'cn-entry-organization', $baseURL . 'admin.js' , array( 'cn-ui-admin', 'jquery-validate' ), self::VERSION );

			$values = array(
				'options' => self::getOrganizations(),
				'string'  => array(
					'select_default' => __( 'Select Organization', 'connections-entry-organization' ),
				),
			);

			wp_localize_script( 'cn-entry-organization', 'cnEntryOrganizationOptions', $values );
		}

		/**
		 * Callback for the `cn_metabox_publish_atts` filter.
		 *
		 * @access private
		 * @since  1.1
		 *
		 * @param array $atts
		 *
		 * @return array
		 */
		public static function registerEntryTypes( $atts ) {

			$atts['entry_type'] = array(
				__( 'Individual', 'connections' ) => 'individual' ,
				__( 'Organization', 'connections' ) => 'organization' );

			$atts['default'] = array(
				'type'       => 'individual',
				'visibility' => 'public',
			);

			return $atts;
		}

		/**
		 * @access public
		 * @static
		 * @since  1.0
		 *
		 * @param array $atts
		 *
		 * @return array
		 */
		public static function getOrganizations( $atts = array() ) {

			/** @var wpdb $wpdb */
			global $wpdb;

			$out = array();
			$where[] = 'WHERE 1=1';

			$defaults = array(
				'status'                => array( 'approved' ),
				'visibility'            => array(),
				'allow_public_override' => FALSE,
				'private_override'      => FALSE
			);

			$atts = wp_parse_args( $atts, $defaults );

			// Limit the results to the "organization" entry type.
			$where[] = 'AND `entry_type` = \'organization\'';

			// Limit the characters that are queried based on if the current user can view public, private or unlisted entries.
			$where = cnRetrieve::setQueryVisibility( $where, $atts );

			// Limit the characters that are queried based on if the current user can view approved and/or pending entries.
			$where = cnRetrieve::setQueryStatus( $where, $atts );

			$select = '`id`, `organization` as name';

			$results = $wpdb->get_results( 'SELECT DISTINCT ' . $select . ' FROM ' . CN_ENTRY_TABLE . ' '  . implode( ' ', $where ) . ' ORDER BY `organization`' );

			foreach ( $results as $row ) {

				$out[ $row->id ] = $row->name;
			}

			return $out;
		}

		/**
		 * @access public
		 * @static
		 * @since  1.0
		 *
		 * @param string $name
		 * @param array  $atts
		 *
		 * @return array
		 */
		public static function organizationByName( $name, $atts = array() ) {

			/** @var wpdb $wpdb */
			global $wpdb;

			$out = array();
			$where[] = 'WHERE 1=1';

			$defaults = array(
				'status'                => array( 'approved' ),
				'visibility'            => array(),
				'allow_public_override' => FALSE,
				'private_override'      => FALSE
			);

			$atts = wp_parse_args( $atts, $defaults );

			$where[] = $wpdb->prepare( 'AND `organization` = %s', $name );

			// Limit the results to the "organization" entry type.
			$where[] = 'AND `entry_type` = \'organization\'';

			// Limit the characters that are queried based on if the current user can view public, private or unlisted entries.
			$where = cnRetrieve::setQueryVisibility( $where, $atts );

			// Limit the characters that are queried based on if the current user can view approved and/or pending entries.
			$where = cnRetrieve::setQueryStatus( $where, $atts );

			$select = '`id`, `organization` as name';

			$results = $wpdb->get_results( 'SELECT DISTINCT ' . $select . ' FROM ' . CN_ENTRY_TABLE . ' '  . implode( ' ', $where ) . ' ORDER BY `organization`' );

			foreach ( $results as $row ) {

				$out[ $row->id ] = $row->name;
			}

			return $out;
		}

		/**
		 * @access public
		 * @static
		 * @since  1.0
		 *
		 * @param       $name
		 * @param array $atts
		 *
		 * @return array|null|object
		 */
		public static function getEntriesByOrganization( $name, $atts = array() ) {

			/** @var wpdb $wpdb */
			global $wpdb;

			$where[] = 'WHERE 1=1';

			$defaults = array(
				'status'                => array( 'approved' ),
				'visibility'            => array(),
				'allow_public_override' => FALSE,
				'private_override'      => FALSE
			);

			$atts = wp_parse_args( $atts, $defaults );

			$where[] = $wpdb->prepare( 'AND `organization` = %s', $name );

			// Limit the results to the "organization" entry type.
			$where[] = 'AND `entry_type` = \'individual\'';

			// Limit the characters that are queried based on if the current user can view public, private or unlisted entries.
			$where = cnRetrieve::setQueryVisibility( $where, $atts );

			// Limit the characters that are queried based on if the current user can view approved and/or pending entries.
			$where = cnRetrieve::setQueryStatus( $where, $atts );

			$select = '`id`';

			$results = $wpdb->get_results( 'SELECT ' . $select . ' FROM ' . CN_ENTRY_TABLE . ' '  . implode( ' ', $where ) . ' ORDER BY `last_name`' );

			return $results;
		}

		/**
		 * @access public
		 * @static
		 * @since  1.0
		 *
		 * @param cnOutput $entry
		 *
		 * @return string
		 */
		public static function displayEmployees( $entry ) {

			$html   = '';

			if ( $relations = self::getEntriesByOrganization( $entry->getName( array(), 'db' ) ) ) {

				$search = array( '%name%', '%thumbnail%' );

				// Grab an instance of the Connections object.
				//$instance = Connections_Directory();

				foreach ( $relations as $relationData ) {

					$relation = new cnOutput();
					$replace  = array();

					if ( $relation->set( $relationData->id ) ) {

						$replace[] = cnURL::permalink(
							array(
								'type'       => 'name',
								'slug'       => $relation->getSlug(),
								'title'      => $relation->getName(),
								'text'       => $relation->getName(),
								'home_id'    => $entry->directoryHome['page_id'],
								'force_home' => $entry->directoryHome['force_home'],
								'return'     => TRUE,
							)
						);

						//$replace[] = '<span class="cn-separator">:</span>';

						$image = $relation->getImageMeta(
							array(
								'type'     => 'photo',
								'width'    => 100,
								'height'   => 125,
								//'return'   => TRUE,
								//'fallback' => array(
								//	'type'   => 'block',
								//	'string' => 'No Photo',
								//),
							)
						);

						if ( ! is_wp_error( $image ) ) {

							$replace[] = '<span class="employee-profile-photo"><img src="' . $image['url'] . '"/></span>';

						} else {

							$replace[] = '';
						}

						$row = str_ireplace(
							$search,
							$replace,
							'<%1$s class="cn-relation">%thumbnail%<span class="cn-employee-profile-link">%name%</span></%1$s>'
						);

						//if ( $title = $relation->getTitle() ) $row .= '<div><em>' . $title . '</em></div>';

						//if ( $email = $relation->getEmailAddressBlock( array( 'limit' => 1, 'return' => TRUE ) ) ) $row .= $email;

						//$row .= '</%1$s>';

						$html .= "\t" . sprintf( $row, 'p' ) . PHP_EOL;
					}
				}

				//$html = sprintf(
				//	'<%1$s class="cn-relations">' . PHP_EOL . '%2$s</%1$s>',
				//	'ul',
				//	$html
				//);

				//echo $html;
			}

			return $html;
		}
	}

	/**
	 * Start up the extension.
	 *
	 * @access public
	 * @since  1.0
	 *
	 * @return Connections_Entry_Organization|false
	 */
	function Connections_Entry_Organization() {

		if ( class_exists( 'connectionsLoad' ) ) {

			return Connections_Entry_Organization::instance();

		} else {

			add_action(
				'admin_notices',
				function() {
					echo '<div id="message" class="error"><p><strong>ERROR:</strong> Connections must be installed and active in order use the Connections Organization Individuals Extension.</p></div>';
				}
			);

			return FALSE;
		}
	}

	/**
	 * We'll load the extension on `plugins_loaded` so we know Connections will be loaded and ready first.
	 */
	add_action( 'plugins_loaded', 'Connections_Entry_Organization' );

endif;
