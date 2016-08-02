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
           $text = $instance['category_slug'];
           $limit = $instance['post_limit'];
        } else {
             $title = 'Latest News';
             $category = 'news';
             $limit = '5';
        }

       
       echo $before_widget;
       // Display the widget
       echo '<div class="widget-text wp_widget_plugin_box">';

       // Check if title is set
       if ( $title ) {
          echo '<h3 class="hidden-xs size-16 margin-bottom-20">'.strtoupper($title).'</h3>';
       }

       // Check if text is set
       if( $text ) {
          $_curcategory = $text;
          $_curlimit = 50;
          $_posts =  do_shortcode( '[CGP_GENERATE_POSTS limit="'.$_curlimit.'" category="'.$_curcategory.'"]' );
          $_posts = json_decode($_posts);
          $p_limit = $limit;
          foreach($_posts as $p){
            if($p->ID!=$_feedid){
              if($p_limit-->0){
                $_custom_url = esc_url(home_url( '/' ).'latest-news/'.$p->ID.'/'.CustomPageTemplate::seoUrl($p->post_title));
        ?>
          <div class="row tab-post"><!-- post -->
            <div class="col-md-3 col-sm-12 col-xs-12">
              <a href="<?=$_custom_url?>">
                <img src="<?=$p->featured_image?>" width="50" alt="">
              </a>
            </div>
            <div class="col-md-9 col-sm-9 col-xs-9">
              <a href="<?=$_custom_url?>" class="tab-post-link"><?=$p->post_title?></a>
              <small><?=date('F j, Y',strtotime($p->post_date))?></small>
            </div>
          </div><!-- /post -->
        <?php 
              }
            }
          }
       }

       echo '</div>';
       echo $after_widget;
    }

    // widget form creation
    public function form($instance) {
        // Check values
        if( $instance) {
             $title = esc_attr($instance['title']);
             $category = esc_attr($instance['category_slug']);
             $limit = esc_attr($instance['post_limit']);
        } else {
             $title = '';
             $category = '';
             $limit = '';
        }

        if(get_option('cgp_feed_slugs')){
          $_cslug = explode(',', get_option('cgp_feed_slugs'));
          if(!empty($_cslug))
            $_feedcategory = $_cslug;
          else
            $_feedcategory = ['latest-news','brokerage-firms'];
        }

        ?>
        <p>
        <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Widget Title', 'wp_custom_iod_featured_widget'); ?></label>
        <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" />
        </p>

        <p>
        <label for="<?php echo $this->get_field_id('category_slug'); ?>"><?php _e('Category/Slug:', 'wp_custom_iod_featured_widget'); ?></label>
        <select class='widefat' id="<?php echo $this->get_field_id('category_slug'); ?>" name="<?php echo $this->get_field_name('category_slug'); ?>">
                <?php
                foreach ($_feedcategory as  $fc) {
                  ?>
                  <option value="<?=$fc?>" <?=($category==$fc)?'selected':''?>><?=$fc?></option>
                  <?php
                }
                ?>
        </select>  
        </p>

        <p>
        <label for="<?php echo $this->get_field_id('post_limit'); ?>"><?php _e('Post Limit:', 'wp_custom_iod_featured_widget'); ?></label>
        <input class="widefat" id="<?php echo $this->get_field_id('post_limit'); ?>" name="<?php echo $this->get_field_name('post_limit'); ?>" type="text" value="<?php echo $limit; ?>" />
        </p>
        <?php
    }

    // update widget
    public function update($new_instance, $old_instance) {
        $instance = $old_instance;
        $instance['title'] = strip_tags($new_instance['title']);
        $instance['category_slug'] = strip_tags($new_instance['category_slug']);
        $instance['post_limit'] = strip_tags($new_instance['post_limit']);
        return $instance;
    }
}

