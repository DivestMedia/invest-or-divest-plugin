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


        // $attachment = [
        //     'Video Title',
        //     'Video URL',
        //     'New Video Uploaded on Market MasterClass.com'
        //     '#36a64f',
        //     'New Video Uploaded on Market MasterClass.com',
        //     []
        // ]
        public function slackbotsend($msg = '',$attachment = []){
            $loop = \React\EventLoop\Factory::create();
            $client = new \Slack\ApiClient($loop);
            $client->setToken('xoxb-96296393831-vDBfYkWD0jBNn2jqNv9TJyge');

            if(!empty($attachment)){
                $attachment = new \Slack\Message\Attachment($attachment);
            }

            $client->getChannelById('C2T5E9MU1')->then(function (\Slack\Channel $channel) use ($client,$msg,$attachment) {
                // $client->send('Hello Ralph from '.gethostname(), $channel);
                // $text = $_GET['chat-message'];
                if(empty($attachment)){
                    $message = $client->getMessageBuilder()
                    ->setText($msg)
                    ->setChannel($channel)
                    ->create();
                }else{
                    // new Attachment( string $title, string $text, string $fallback = null, $color = null, $pretext = null, array $fields = [] )
                    $message = $client->getMessageBuilder()
                    ->addAttachment($attachment)
                    ->setChannel($channel)
                    ->create();
                }

                $client->apiCall('chat.postMessage', [
                    'text' => $message->getText(),
                    'channel' => $message->data['channel'],
                    'username' => 'marketmasterclassbot',
                    'as_user' => false ,
                    'icon_url' => 'https://avatars.slack-edge.com/2016-10-26/96235543043_445846a143687f3c5cc4_48.png'
                ]);
            });
            $loop->run();
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
            add_action( 'admin_enqueue_scripts', [&$this, 'enqueue_admin_styles'] );
            add_action( 'admin_enqueue_scripts', [&$this, 'enqueue_admin_scripts'] );
            add_action('post_edit_form_tag', function(){
                echo ' enctype="multipart/form-data"';
            });
        }
        public function create_invest_or_divest_post_type(){
            register_taxonomy(
            'iod_category',  //The name of the taxonomy. Name should be in slug form (must not contain capital letters or spaces).
            'iod_video',        //post type name
            array(
                'hierarchical' => true,
                'label' => 'Video Category',  //Display name
                'labels' => 	[
                    'name'              => 'Video Category',
                    'singular_name'     => 'Video Category',
                    'search_items'      => 'Search Video Categories',
                    'all_items'         => 'All Video Categories',
                    'parent_item'       => 'Parent Video Category',
                    'parent_item_colon' => 'Parent Video Category:',
                    'edit_item'         => 'Edit Video Category',
                    'update_item'       => 'Update Video Category',
                    'add_new_item'      => 'Add New Video Category',
                    'new_item_name'     => 'New Video Category',
                    'menu_name'         => 'Video Category',
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
                            'slug' => 'video', // This controls the base slug that will display before each term
                            'with_front' => false // Don't display the category base before
                            ]
                        )
                    );
                    $registered = register_post_type( 'iod_video',[
                        'labels' => [
                            'name' => 'Videos',
                            'singular_name' => 'Video',
                            'add_new' => 'Add New Video',
                            'add_new_item' => 'Add New Video',
                            'edit_item' => 'Edit Video',
                            'new_item' => 'Add New Video',
                            'view_item' => 'View Video',
                            'search_items' => 'Search Video',
                            'not_found' => 'No Video found',
                            'not_found_in_trash' => 'No Video found in trash'
                        ],
                        'public' => true,
                        'capability_type' => 'post',
                        'has_archive' => true,
                        'menu_icon'           => 'dashicons-video-alt',
                        'rewrite' => [
                            'slug' => 'videos',
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
                    add_meta_box( 'iod_video_review', 'Video', [&$this, 'cb_game_video_review_metabox'], 'iod_video' , 'normal', 'high');
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

                        if($field=='_iod_video'){
                            $iod_video = json_decode(stripslashes_deep($_POST['_iod_video']))->embed->url;
                            $ytpattern = '/^.*(?:(?:youtu\.be\/|v\/|vi\/|u\/\w\/|embed\/)|(?:(?:watch)?\?v(?:i)?=|\&v(?:i)?=))([^#\&\?]*).*/';
                            $videoid = '';

                            $title = $post->post_title;

                            if(preg_match($ytpattern,$iod_video,$vid_id)){
                                $videoid = end($vid_id);
                                $iod_video_thumbnail = 'http://img.youtube.com/vi/'.$videoid.'/mqdefault.jpg';
                                $this->grab_thumbnail($iod_video_thumbnail,$post->ID);

                                update_post_meta($post->ID, 'video-image-full', $this->grab_thumbnail('http://img.youtube.com/vi/'.$videoid.'/0.jpg',$post->ID,false));
                                update_post_meta($post->ID, 'video-image-default', $this->grab_thumbnail('http://img.youtube.com/vi/'.$videoid.'/default.jpg',$post->ID,false));
                                update_post_meta($post->ID, 'video-image-hqdefault', $this->grab_thumbnail('http://img.youtube.com/vi/'.$videoid.'/hqdefault.jpg',$post->ID,false));
                                update_post_meta($post->ID, 'video-image-mqdefault', $this->grab_thumbnail('http://img.youtube.com/vi/'.$videoid.'/mqdefault.jpg',$post->ID,false));
                                update_post_meta($post->ID, 'video-image-sddefault', $this->grab_thumbnail('http://img.youtube.com/vi/'.$videoid.'/sddefault.jpg',$post->ID,false));
                                update_post_meta($post->ID, 'video-image-maxresdefault', $this->grab_thumbnail('http://img.youtube.com/vi/'.$videoid.'/maxresdefault.jpg',$post->ID,false));
                                update_post_meta($post->ID, 'video-image-thumbs-1', $this->grab_thumbnail('http://img.youtube.com/vi/'.$videoid.'/1.jpg',$post->ID,false));
                                update_post_meta($post->ID, 'video-image-thumbs-2', $this->grab_thumbnail('http://img.youtube.com/vi/'.$videoid.'/2.jpg',$post->ID,false));
                                update_post_meta($post->ID, 'video-image-thumbs-3', $this->grab_thumbnail('http://img.youtube.com/vi/'.$videoid.'/3.jpg',$post->ID,false));

                                // Get Video Details
                                $apikey = "AIzaSyATs9R_IBTjzMUEM8xGAmRn5PAb3DUrPYs" ;
                                $videoData = $this->file_get_contents_curl("https://www.googleapis.com/youtube/v3/videos?part=contentDetails&id=$videoid&key=$apikey");
                                $videoData =json_decode($videoData, true);
                                if(is_array($videoData)){
                                    update_post_meta($post->ID, 'video-details', json_encode($videoData));
                                    $VidDuration = $videoData['items'][0]['contentDetails']['duration'];
                                    preg_match_all('/(\d+)/',$VidDuration,$parts);
                                    $hours = (strlen(floor($parts[0][0]/60))<1)?floor($parts[0][0]/60):'0'.floor($parts[0][0]/60);
                                    $minutes = $parts[0][0]%60; (strlen($parts[0][0]%60)<1)?$parts[0][0]%60:'0'.$parts[0][0]%60;
                                    $seconds = $parts[0][1];(strlen($parts[0][1])<1)?$parts[0][1]:'0'.$parts[0][1];
                                    update_post_meta($post->ID, 'video-duration', sprintf('%02d:%02d:%02d',$hours,$minutes,$seconds));

                                }

                                $videoStatistics = $this->file_get_contents_curl("https://www.googleapis.com/youtube/v3/videos?part=statistics&id=$videoid&key=$apikey");
                                $videoStatistics =json_decode($videoStatistics, true);
                                if(is_array($videoStatistics)){
                                    update_post_meta($post->ID, 'video-statistics',json_encode($videoStatistics));
                                    update_post_meta($post->ID, 'view-count',$videoStatistics['items'][0]['statistics']['viewCount']);
                                    update_post_meta($post->ID, 'like-count',$videoStatistics['items'][0]['statistics']['likeCount']);
                                    update_post_meta($post->ID, 'dislike-count',$videoStatistics['items'][0]['statistics']['dislikeCount']);
                                    update_post_meta($post->ID, 'favorite-count',$videoStatistics['items'][0]['statistics']['favoriteCount']);
                                    update_post_meta($post->ID, 'comment-count',$videoStatistics['items'][0]['statistics']['commentCount']);
                                }


                                // Get Video Published Date
                                $videoSnippet = $this->file_get_contents_curl("https://www.googleapis.com/youtube/v3/videos?part=snippet&id=$videoid&key=$apikey");
                                $videoSnippet =json_decode($videoSnippet, true);


                                if(is_array($videoSnippet)){
                                    update_post_meta($post->ID, 'video-snippet', json_encode($videoSnippet));
                                    $VidDate = date("Y-m-d H:i:s",strtotime($videoSnippet['items'][0]['snippet']['publishedAt']));

                                    if ( ! wp_is_post_revision( $post->ID ) ){

                                        remove_action( 'save_post_iod_video', [ &$this , 'save_iod_video_metabox' ] );
                                        wp_update_post([
                                            'ID' => $post->ID,
                                            'post_date' => $VidDate,
                                        ]);
                                        add_action( 'save_post_iod_video', [ &$this , 'save_iod_video_metabox' ] );

                                    }

                                    $title = $videoSnippet['items'][0]['snippet']['localized']['title'];
                                }
                            }

                            if(empty($title)){
                                $title = $iod_video;
                            }


                            if($iod_video){

                                // Send Update to Slack
                                // $this->slackbotsend('New Video Updated', [
                                //     $title,
                                //     "View on Youtube : " . $iod_video . " ",
                                //     "New Video Uploaded on Market MasterClass.com - ".$iod_video." ",
                                //     '#36a64f',
                                //     'New Video Uploaded on Market MasterClass.com',
                                //     null
                                // ]);

                                $category = 'Uncategorized';

                                $cat = get_the_terms($post->ID,'iod_category');
                                if(!empty($cat[0])){
                                    $category  = $cat[0]->name;
                                }

                                $this->slackbotsend(implode("\n",[
                                    "New Video Uploaded on ".get_bloginfo('name'),
                                    "Category : " . $category,
                                    "By: ". wp_get_current_user()->display_name,
                                    "<" . site_url('videos') . "|Check on Website>",
                                    "<" . $iod_video . "|Watch on Youtube>",
                                    ]));

                            }


                        }

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
                    if(!$post) return;
                    if($post->post_type != 'iod_video')
                    return;
                    $styles = [
                        'admin' => INVEST_DIVEST_PLUGIN_URL . '/css/admin',
                    ];
                    foreach ( $styles as $id => $path) {
                        wp_register_style( 'iod-' . $id . '-css' , $path . '.css', false);
                        wp_enqueue_style( 'iod-' . $id . '-css');
                    };
                    wp_enqueue_style('thickbox');
                }
                public function enqueue_admin_scripts($hook){
                    global $post;
                    if(!$post) return;
                    if($post->post_type != 'iod_video')
                    return;
                    $scripts = [
                        'admin' => INVEST_DIVEST_PLUGIN_URL . 'js/admin',
                    ];
                    foreach ($scripts as $id => $path) {
                        wp_register_script( 'iod-' . $id . '-js',  $path . '.js' ,['jquery']);
                        wp_enqueue_script( 'iod-' . $id . '-js');
                    }
                    wp_enqueue_script('media-upload');
                    wp_enqueue_script('thickbox');
                    wp_enqueue_script('suggest');
                }
                public static function activate(){


                }
                public static function deactivate(){ }

                public function file_get_contents_curl($url) {
                    $ch = curl_init();

                    curl_setopt($ch, CURLOPT_AUTOREFERER, TRUE);
                    curl_setopt($ch, CURLOPT_HEADER, 0);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                    curl_setopt($ch, CURLOPT_URL, $url);
                    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);

                    $data = curl_exec($ch);
                    curl_close($ch);

                    return $data;
                }

                public function grab_thumbnail( $image_url, $post_id , $thumbnail = true ){
                    $upload_dir = wp_upload_dir();

                    $opts = [
                        'http' => [
                            'method'  => 'GET',
                            'user_agent '  => "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_11_5) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/51.0.2704.103 Safari/537.36",
                            'header' => [
                                'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8
                                '
                            ]
                        ]
                    ];

                    $context  = stream_context_create($opts);

                    // $image_data = file_get_contents($image_url,false,$context);
                    $image_data = $this->file_get_contents_curl($image_url);
                    if(!is_array(getimagesize($image_url))){
                        return false;
                    }

                    $filename = basename($image_url);

                    // Remove Query Strings
                    $querypos = strpos($filename, '?');
                    if($querypos!==FALSE){
                        $filename = substr($filename,0,$querypos);
                    }

                    if(empty($filename)):
                        $filename = get_post($post_id)->post_name;
                    endif;


                    $filename = $post_id . '-' . $filename;

                    $attached = get_posts([
                        'post_type' => 'attachment',
                        'name' => sanitize_file_name($filename),
                        'posts_per_page' => 1,
                        'post_status' => 'inherit',
                    ]);
                    if(count($attached)==1){
                        if($thumbnail){
                            return true;
                        }else{
                            return $attached[0]->ID;
                        }
                    }


                    if(wp_mkdir_p($upload_dir['path']))     $file = $upload_dir['path'] . '/' . $filename;
                    else                                    $file = $upload_dir['basedir'] . '/' . $filename;
                    file_put_contents($file, $image_data);

                    $wp_filetype = wp_check_filetype($filename, null );
                    $attachment = array(
                        'post_mime_type' => $wp_filetype['type'],
                        'post_title' => sanitize_file_name($filename),
                        'post_content' => '',
                        'post_status' => 'inherit'
                    );

                    $attach_id = wp_insert_attachment( $attachment, $file, $post_id );
                    require_once(ABSPATH . 'wp-admin/includes/image.php');
                    $attach_data = wp_generate_attachment_metadata( $attach_id, $file );
                    $res1= wp_update_attachment_metadata( $attach_id, $attach_data );
                    if($thumbnail){
                        $res2= set_post_thumbnail( $post_id, $attach_id );
                    }else{
                        return $attach_id;
                    }
                }

            }
        }
