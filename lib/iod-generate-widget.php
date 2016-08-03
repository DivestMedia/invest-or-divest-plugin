<?php

// Block direct requests
if ( !defined('ABSPATH') )
  die('-1');
  
// register widget
add_action( 'widgets_init', function(){
     register_widget( 'InvestOrDivestCustomWidget' );
}); 


class InvestOrDivestCustomWidget extends WP_Widget {

    // constructor
    public function __construct() {
         parent::WP_Widget(false, $name = __('Invest or Divest Featured Video', 'wp_custom_iod_featured_widget') );
    }

    // display widget
    public function widget($args, $instance) {
        extract( $args );
        
        if( $instance) {
             // these are the widget options
           $title = apply_filters('widget_title', $instance['title']);
        } else {
             $title = 'FEATURED VIDEO';
        }

       
       echo $before_widget;
       // Display the widget
       echo '<div class="widget-text wp_widget_plugin_box">';

       // Check if title is set
       if ( $title ) {
          echo '<h3 class="hidden-xs size-16 margin-bottom-20">'.strtoupper($title).'</h3>';
       }

       $type = 'iod_video';
        $posts = get_posts(array(
            'post_type'   => $type,
            'post_status' => 'publish',
            'posts_per_page' => 1,
            'orderby' => 'rand',
            'meta_key'   => '_is_featured',
            'meta_value' => 1
            )
        );
        if( !empty($posts)) {
        ?>
          <div class="row grid-color margin-bottom-20">
            <div class="col-md-12">
          <?php 
          foreach ($posts as $p) {
          ?>
           <div class="embed-responsive embed-responsive-16by9">
              <?php
                $iod_video = json_decode(get_post_meta( $p->ID, '_iod_video',true))->embed->url;
                echo wp_oembed_get($iod_video, '');
              ?>
                <div></div>
              </div>
          <?php }?>
          </div>
          </div>
          <hr>
        <?php
        }

       echo '</div>';
       echo $after_widget;
    }

    // widget form creation
    public function form($instance) {
        // Check values
        if( $instance) {
             $title = esc_attr($instance['title']);
             $limit = esc_attr($instance['post_limit']);
        } else {
             $title = '';
             $limit = '';
        }
        ?>
        <p>
        <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Widget Title', 'wp_custom_iod_featured_widget'); ?></label>
        <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" />
        </p>
        <?php
    }

    // update widget
    public function update($new_instance, $old_instance) {
        $instance = $old_instance;
        $instance['title'] = strip_tags($new_instance['title']);
        return $instance;
    }
}

