<?php
// include_once INVEST_DIVEST_PLUGIN_DIR . 'bulkimport.php';
$upload_link = esc_url( get_upload_iframe_src( 'video', $post->ID ) );
?>
<div class="video-review-container">
  <div class="embed-code-wrapper">

  </div>
  <input type="hidden" name="_iod_video" value="<?=esc_attr($gr_ov_data['_iod_video'])?>">
</div>

<p class="hide-if-no-js">
    <a class="upload-custom-img button" href='<?php echo $upload_link ?>'>Add Video</a>
</p>
