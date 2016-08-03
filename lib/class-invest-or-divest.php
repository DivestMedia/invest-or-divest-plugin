<?php if(!defined('INVEST_DIVEST_VERSION')) die('Fatal Error');
if(!class_exists('InvestOrDivest'))
{
    class InvestOrDivest
    {
        static $instance;
        public $option_fields = [
            'iod_video_review' => [
                '_iod_video'
            ]
        ];
        public function __CONSTRUCT(){
            add_action('init', [&$this, 'main_init']);
            add_action('admin_init', [&$this, 'admin_init']);
            add_filter( 'manage_edit-iod_video_columns', [&$this,'custom_iod_video_columns'] ) ;
            add_action( 'manage_posts_custom_column' , [&$this,'iod_video_columns_data'], 10, 2 );
            add_action( 'admin_head' , [&$this,'iod_video_columns_css'] );
        }

        public function iod_video_columns_css(){
            echo '
            <style>
                .column-is_featured{width:75px;}
            </style>
            ';
        }

        public function custom_iod_video_columns( $columns ) {
            $newcolumns = array(
                'is_featured' => __( 'Is Featured' )
            );
            $columns = array_slice($columns, 0, 5, true) + $newcolumns + array_slice($columns, 5, count($columns) - 1, true) ;
            return $columns;
        }

        public function iod_video_columns_data( $column, $post_id ) {
            switch ( $column ) {
            case 'is_featured':
                $isfeatured = json_decode(get_post_meta( $post_id, '_is_featured',true));
                $icon = empty($isfeatured)?'empty':'filled';
                echo '<a href="'.get_home_url().'/updatefeatured/'.$post_id.'" title="Set as featured video"><span class="dashicons dashicons-star-'.$icon.'"></span></a>';
                break;
            }
        }

        public function main_init(){
            $this->create_invest_or_divest_post_type();
            $this->create_iod_video_save_post();
        }
        public function admin_init(){
            $this->register_reviews_meta_boxes();
            add_action( 'admin_enqueue_scripts', [$this, 'enqueue_admin_styles'] );
            add_action( 'admin_enqueue_scripts', [$this, 'enqueue_admin_scripts'] );
        }
        public function create_invest_or_divest_post_type(){
            register_taxonomy(
            'iod_category',  //The name of the taxonomy. Name should be in slug form (must not contain capital letters or spaces).
            'iod_video',        //post type name
            array(
                'hierarchical' => true,
                'label' => 'IOD Category',  //Display name
                'labels' => 	[
                    'name'              => 'IOD Category',
                    'singular_name'     => 'IOD Category',
                    'search_items'      => 'Search Categories',
                    'all_items'         => 'All Categories',
                    'parent_item'       => 'Parent Category',
                    'parent_item_colon' => 'Parent Category:',
                    'edit_item'         => 'Edit Category',
                    'update_item'       => 'Update Category',
                    'add_new_item'      => 'Add New Category',
                    'new_item_name'     => 'New Category',
                    'menu_name'         => 'IOD Category',
                    ],
                    'public' => true,
                    'publicly_queryable' => true,
                    'exclude_from_search' => false,
                    'query_var' => true,
                    'show_ui'           => true,
                    'show_admin_column' => true,
                    'capabilities' => [
                        'manage_terms',
                        'edit_terms',
                        'delete_terms',
                        'assign_terms',
                        ],
                        'rewrite' =>[
                            'slug' => 'iod-category', // This controls the base slug that will display before each term
                            'with_front' => false // Don't display the category base before
                            ]
                        )
                    );
                    $registered = register_post_type( 'iod_video',[
                        'labels' => [
                            'name' => 'Invest or Divest Videos',
                            'singular_name' => 'Invest or Divest Video',
                            'add_new' => 'Add New IOD Video',
                            'add_new_item' => 'Add New IOD Video',
                            'edit_item' => 'Edit IODVideo',
                            'new_item' => 'Add New IOD Video',
                            'view_item' => 'View IOD Video',
                            'search_items' => 'Search IOD Video',
                            'not_found' => 'No iod video found',
                            'not_found_in_trash' => 'No iod video found in trash'
                        ],
                        'public' => true,
                        'capability_type' => 'post',
                        'has_archive' => true,
                        'menu_icon'           => 'dashicons-video-alt',
                        'rewrite' => [
                            'slug' => 'invest-or-divest',
                            'with_front' => false
                        ],
                        'supports' => [
                            'title',
                            'editor',
                            'thumbnail',
                            'excerpt',
                            // 'page-attributes',
                            'custom-fields',
                            'comments'
                        ],
                        'taxonomies' => ['post_tag','iod_category']
                    ]);
                    flush_rewrite_rules();
                }
                public function register_reviews_meta_boxes(){
                    add_action( 'add_meta_boxes', [&$this, 'create_reviews_meta_boxes']);
                }
                public function create_reviews_meta_boxes(){
                    add_meta_box( 'iod_video_review', 'Invest or Divest Video', [&$this, 'cb_game_video_review_metabox'], 'iod_video' , 'normal', 'high');
                }

                public function cb_game_video_review_metabox(){
                    global $post;
                    $gr_ov_data  = [];
                    foreach ($this->option_fields['iod_video_review'] as $field) {
                        $gr_ov_data[$field] = get_post_meta( $post->ID, $field, true );
                    }
                    wp_nonce_field( basename( __FILE__ ), '_invest_or_divest_video_metabox_nonce' );
                    include_once( INVEST_DIVEST_PLUGIN_DIR . 'templates/invest-or-divest-video-review-metabox.php' );
                }
                public function create_iod_video_save_post(){
                    add_action( 'save_post_iod_video', [ &$this , 'save_iod_video_metabox' ]);
                }
                public function save_iod_video_metabox(){
                    global $post;
                    if( !isset( $_POST['_invest_or_divest_video_metabox_nonce'] ) || !wp_verify_nonce( $_POST['_invest_or_divest_video_metabox_nonce'], basename( __FILE__ ) ) ){
                        return;
                    }
                    if( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ){
                        return;
                    }
                    if( ! current_user_can( 'edit_post', $post->id ) ){
                        return;
                    }
                    foreach ($this->option_fields['iod_video_review'] as $field) {
                        $this->save_meta_value($post->ID,$field,$_POST[$field]);
                    }
                }
                public function save_meta_value($id,$meta_id = '',$value = ''){
                    if(!empty($meta_id)){
                        if( isset( $value ) ){
                            update_post_meta( $id , $meta_id , $value );
                        }else{
                            delete_post_meta( $id , $meta_id  );
                        }
                    }
                }
                public function enqueue_admin_styles(){
                    global $post;
                    if($post->post_type != 'iod_video')
                    return;
                    $styles = [
                        'admin' => INVEST_DIVEST_PLUGIN_URL . '/css/admin',
                    ];
                    foreach ( $styles as $id => $path) {
                        wp_register_style( $id . '-css' , $path . '.css', false);
                        wp_enqueue_style( $id . '-css');
                    };
                    wp_enqueue_style('thickbox');
                }
                public function enqueue_admin_scripts($hook){
                    global $post;
                    if($post->post_type != 'iod_video')
                    return;
                    $scripts = [
                        'admin' => INVEST_DIVEST_PLUGIN_URL . 'js/admin',
                    ];
                    foreach ($scripts as $id => $path) {
                        wp_register_script( $id . '-js',  $path . '.js' ,['jquery']);
                        wp_enqueue_script( $id . '-js');
                    }
                    wp_enqueue_script('media-upload');
                    wp_enqueue_script('thickbox');
                    wp_enqueue_script('suggest');
                }
                public function activate(){ }
                public function deactivate(){ }
            }
        }
