<?php

namespace MBT {

	use \Walker_Nav_Menu as WalkerNavMenu;
	use \WP_Query;

	/**
	 * Class BemWalkerNavMenu
	 * @package MBT
	 *
	 * @wp-filter MBT/WalkerNavMenu/menuToggleTitle - string $title
	 * @wp-filter MBT/WalkerNavMenu/menuToggleContent - string $content
	 * @wp-filter MBT/WalkerNavMenu/autoArchiveMenu - bool $render, int $depth, object $item
	 * @wp-filter MBT/WalkerNavMenu/autoTaxonomyMenu - bool $render, int $depth, object $item
	 * @wp-filter MBT/WalkerNavMenu/PostTypeArchive/queryArgs/postType={$postType} - array $query_args
	 * @wp-filter MBT/WalkerNavMenu/TermChildren/queryArgs/taxonomy={$taxonomy} - array $query_args
	 */

	class BemWalkerNavMenu extends WalkerNavMenu {
		/**
		 * @var int
		 */
		private $lvl_index = 0;

		/**
		 * @var string
		 */
		private $baseClass = '';

		/**
		 * BemWalkerNavMenu constructor.
		 *
		 * @param string $baseClass
		 */
		public function __construct($baseClass = 'menu') {
			global $mbt;
			$this->lvl_index = isset($mbt['lvl_index']) ? $mbt['lvl_index'] : -1;
			$this->baseClass = $baseClass;
		}

		/**
		 * @param string $output
		 * @param int $depth
		 * @param array $args
		 */
		public function start_lvl( &$output, $depth = 0, $args = array() ) {
			$this->lvl_index++;
			$title = apply_filters('MBT/WalkerNavMenu/menuToggleTitle', 'Toggle menu');
			$output .= "<a href=\"#{$this->baseClass}__list--{$depth}-{$this->lvl_index}\" class=\"{$this->baseClass}__toggle\" title=\"{$title}\">";
			$output .= apply_filters('MBT/WalkerNavMenu/menuToggleContent', '<svg viewBox="0 0 40 40"><path d="M20,26.5 11.4,17.8 15.7,13.5 20,17.9 24.3,13.5 28.6,17.8 "></path></svg>');
			$output .= "</a>";
			$output .= "<ul class=\"{$this->baseClass}__list {$this->baseClass}__list--{$depth}\" id=\"{$this->baseClass}__list--{$depth}-{$this->lvl_index}\">";
		}

		/**
		 * @param string $output
		 * @param int $depth
		 * @param array $args
		 */
		public function end_lvl( &$output, $depth = 0, $args = array() ) {
			$output .= "</ul>";
			global $mbt;
			$mbt['lvl_index'] = $this->lvl_index;
		}

		/**
		 * @param string $output
		 * @param object $item
		 * @param int $depth
		 * @param array $args
		 * @param int $id
		 */
		public function start_el( &$output, $item, $depth = 0, $args = array(), $id = 0 ) {
			$classes = empty( $item->classes ) ? array() : (array) $item->classes;
			$classes[] = $this->baseClass.'__item';
			foreach ($classes as $key => &$class) {
				if (strpos($class, 'current-') === 0) {
					$class = $this->baseClass.'__item--current';
				} elseif (strpos($class, 'ancestor') !== false) {
					$class = $this->baseClass.'__item--ancestor';
				} elseif ($class !== $this->baseClass.'__item') {
					unset($classes[$key]);
				}
			}
			$classes[] = $this->baseClass.'__item--' . $depth;

			$args = apply_filters( 'nav_menu_item_args', $args, $item, $depth );

			$class_names = join( ' ', apply_filters( 'nav_menu_css_class', array_filter( $classes ), $item, $args, $depth ) );
			$class_names = $class_names ? ' class="' . esc_attr( $class_names ) . '"' : '';

			$output .= '<li' . $class_names .'>';

			$atts = array();
			$atts['title']  = ! empty( $item->attr_title ) ? $item->attr_title : '';
			$atts['target'] = ! empty( $item->target )     ? $item->target     : '';
			$atts['rel']    = ! empty( $item->xfn )        ? $item->xfn        : '';
			$atts['href']   = ! empty( $item->url )        ? $item->url        : '';
			$atts['class']  = $this->baseClass.'__link';

			$atts = apply_filters( 'nav_menu_link_attributes', $atts, $item, $args, $depth );

			$attributes = '';
			foreach ( $atts as $attr => $value ) {
				if ( ! empty( $value ) ) {
					$value = ( 'href' === $attr ) ? esc_url( $value ) : esc_attr( $value );
					$attributes .= ' ' . $attr . '="' . $value . '"';
				}
			}

			$title = apply_filters( 'the_title', $item->title, $item->ID );
			$title = apply_filters( 'nav_menu_item_title', $title, $item, $args, $depth );

			$item_output = $args->before;
			$item_output .= '<a'. $attributes .'>';
			$item_output .= $args->link_before . $title . $args->link_after;
			$item_output .= '</a>';
			$item_output .= $args->after;
			$output .= apply_filters( 'walker_nav_menu_start_el', $item_output, $item, $depth, $args );
		}

		/**
		 * @param string $output
		 * @param object $item
		 * @param int $depth
		 * @param array $args
		 */
		public function end_el( &$output, $item, $depth = 0, $args = array() ) {
			if ($item->type == 'post_type_archive' && apply_filters('MBT/WalkerNavMenu/autoArchiveMenu', false, $item, $depth)) {
				$this->display_post_type_archive($item, $output, $depth+1, $args);
			} elseif ($item->type == 'taxonomy' && apply_filters('MBT/WalkerNavMenu/autoTaxonomyMenu', false, $item, $depth)) {
				$this->display_term_children($item, $output, $depth+1, $args);
			}
			$output .= "</li>\n";
		}

		/**
		 * @param $item
		 * @param $output
		 * @param int $depth
		 * @param array $args
		 */
		public function display_post_type_archive($item, &$output, $depth = 0, $args = array() ) {
			$query_args = array(
				'post_type' => $item->object,
				'posts_per_page' => -1,
				'post_parent' => 0,
			);
			$query_args = apply_filters( "MBT/WalkerNavMenu/PostTypeArchive/queryArgs/postType={$item->object}", $query_args);
			$loop = new WP_Query($query_args);
			if ($loop->have_posts()) {
				$this->start_lvl($output, $depth-1, $args);
				global $post;
				while($loop->have_posts()) {
					$loop->the_post();
					$item_output = '<li class="'.$this->baseClass.'__item '.$this->baseClass.'__item--'.$depth.'"><a class="'.$this->baseClass.'__link" title="'.esc_attr(get_the_title()).'" href="'.get_the_permalink().'">'.get_the_title().'</a>';
					$output .= apply_filters( 'walker_nav_menu_start_el', $item_output, $post, $depth, $args );
					$output .= '</li>';
				}
				wp_reset_postdata();
				$this->end_lvl($output, $depth-1, $args);
			}
		}

		/**
		 * @param $item
		 * @param $output
		 * @param int $depth
		 * @param array $args
		 */
		public function display_term_children($item, &$output, $depth = 0, $args = array() ) {
			$taxonomy = get_taxonomy($item->object);
			$query_args = array(
				'post_type' => $taxonomy->object_type,
				'posts_per_page' => -1,
				'post_parent' => 0,
				'tax_query' => array(
					array(
						'taxonomy' => $item->object,
						'field' => 'id',
						'terms' => $item->object_id
					)
				)
			);
			$query_args = apply_filters( "MBT/WalkerNavMenu/TermChildren/queryArgs/taxonomy={$item->object}", $query_args);
			$loop = new WP_Query($query_args);
			if ($loop->have_posts()) {
				$this->start_lvl($output, $depth-1, $args);
				global $post;
				while($loop->have_posts()) {
					$loop->the_post();
					$item_output = '<li class="'.$this->baseClass.'__item '.$this->baseClass.'__item--'.$depth.'"><a class="'.$this->baseClass.'__link" title="'.esc_attr(get_the_title()).'" href="'.get_the_permalink().'">'.get_the_title().'</a>';
					$output .= apply_filters( 'walker_nav_menu_start_el', $item_output, $post, $depth, $args );
					$output .= '</li>';
				}
				wp_reset_postdata();
				$this->end_lvl($output, $depth-1, $args);
			}
		}
	}
}

namespace {

	use MBT\BemWalkerNavMenu as WalkerNavMenu;

	if (!function_exists('bem_nav_menu')) {
		function bem_nav_menu($args) {
			$baseClass = 'menu';
			if (isset($args['base_class'])) {
				$baseClass = $args['base_class'];
				unset($args['base_class']);
			}
			$args = wp_parse_args($args, array(
				'menu_class' => $baseClass.'__list '.$baseClass.'__list--root',
				'container' => 'nav',
				'container_class' => $baseClass,
				'walker' => new WalkerNavMenu($baseClass),
			));
			wp_nav_menu($args);
		}
	}
}