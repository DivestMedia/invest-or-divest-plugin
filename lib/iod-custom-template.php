<?php

  class IODCustomTemplate{
    public function __construct(){
      self::custom_template_init();
    }

    public function custom_template_init(){
      add_filter( 'rewrite_rules_array',[$this,'rewriteRules'] );
      add_filter( 'template_include', [ $this, 'template_include' ],1,1 );
      add_filter( 'query_vars', [ $this, 'prefix_register_query_var' ] );
    }

    public function prefix_register_query_var($vars){
      $vars[] = 'iodID';
      $vars[] = 'iodtp';
      return $vars;
    }

    public function rewriteRules($rules){
      $newrules = self::rewrite();
      return $newrules + $rules;
    }

    public function rewrite(){
      $newrules = array();
      $newrules['updatefeatured/(.*)'] = 'index.php?iodtp=updatefeatured&iodID=$matches[1]';
      return $newrules;
    }

    public function removeRules($rules){
      $newrules = self::rewrite();
      foreach ($newrules as $rule => $rewrite) {
            unset($rules[$rule]);
        }
      return $rules;
    }

    public function template_include($template){
      $_iodID = sanitize_text_field(get_query_var( 'iodID' ));
      $_iodtp = sanitize_text_field(get_query_var( 'iodtp' ));
      if(!strcasecmp($_iodtp, 'updatefeatured')&&is_user_logged_in()&&current_user_can( 'manage_options' )){
        $meta_key = '_is_featured';
        $isfeatured = json_decode(get_post_meta( $_iodID, $meta_key,true));
        if(empty($isfeatured)){
          delete_post_meta($_iodID, $meta_key);
          add_post_meta($_iodID, $meta_key, '1');
        }else{
          delete_post_meta($_iodID, $meta_key);
        }
        wp_redirect($_SERVER['HTTP_REFERER']);
        die();
      }else{
        return $template;
      }
    }

  }
