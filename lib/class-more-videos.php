<?php
class InvestOrDivestWidget
{

	public function __construct(){
		add_action( 'wp_enqueue_scripts', $this->get_style() );
	}



	public function generate_featured_videos($limit=4){
		$type = 'iod_video';
		$posts = get_posts(array(
			'post_type'   => $type,
			'post_status' => 'publish',
			'posts_per_page' => $limit,
			'orderby' => 'rand',
			'meta_key'   => '_is_featured',
			'meta_value' => 1
		)
	);
	if( !empty($posts)) {
		?>
		<div class="row grid-color">
			<?php
			foreach ($posts as $p) {
				$iod_video = json_decode(get_post_meta( $p->ID, '_iod_video',true))->embed->url;
				$vid_id = explode('=', $iod_video);
				$iod_video_thumbnail = 'http://img.youtube.com/vi/'.end($vid_id).'/mqdefault.jpg';
				?>
				<div class="col-md-3 col-sm-6 margin-bottom-10">
					<figure class="">
						<img class="img-responsive" src="<?=$iod_video_thumbnail?>" alt="" />
					</figure>
					<span class="section-content">
						<div class="text-left">
							<a href="<?=$p->guid?>" alt="<?=$p->post_title; ?>" class="title"><strong><?=$p->post_title;?></strong></a>
							<label><?=$p->post_content; ?></label>
						</div>
						<a href="<?=$p->guid?>"><button type="button" class="btn btn-warning btn-sm btn-custom yellow">WATCH NOW</button></a>
					</span>
				</div>
				<?php }?>
			</div>
			<?php
		}
		?>

		<?php
	}

	public function generate_side_widget($limit=8,$exclude = []){
		?>
		<div class="col-md-12 col-sm-12 cont-more-episodes">
			<h4>More Episodes</h4>
			<hr>
			<?php
			$type = 'iod_video';

			$posts = get_posts(array(
				'post_type'   => $type,
				'post_status' => 'publish',
				'posts_per_page' => $limit,
				'orderby' => 'rand',
				'exclude' => $exclude
			)
		);
		if( !empty($posts)) {
			foreach ($posts as $p) {
				$iod_video = json_decode(get_post_meta( $p->ID, '_iod_video',true))->embed->url;
				$vid_id = explode('=', $iod_video);
				$iod_video_thumbnail = 'http://img.youtube.com/vi/'.end($vid_id).'/mqdefault.jpg';
				?>
				<div class="margin-bottom-20 col-md-12 col-sm-12 col-xs-12">
					<div class="col-md-4 col-sm-3 col-xs-4" style="padding: 0;">
						<a href="<?=$p->guid?>">
							<img class="img-responsive episode-thumbnail" src="<?=$iod_video_thumbnail?>" alt="<?=$p->post_title; ?>" />
						</a>
					</div>
					<div class="col-md-8 col-sm-9 col-xs-8 cont-episode-details">
						<a href="<?=$p->guid?>" title="<?=$p->post_title; ?>" class="title"><strong><?=$p->post_title; ?></strong></a>
						<label><i class="fa fa-eye fa-fw"></i><?=self::count_postviews($p->ID,true)?> views</label>
						<label><i class="fa fa-comments fa-fw"></i><?=$p->comment_count?> comments</label>
					</div>
				</div>
				<?php
			}
		}else{
			?>
			<label class="text-center margin-bottom-20 size-14 block ">No more episodes yet</label>
			<?php
		}
		wp_reset_query();  // Restore global post data stomped by the_post().
		?>
	</div>
	<?php
}

public function get_style() {
	wp_enqueue_style( 'invest-or-divest-widget-style', INVEST_DIVEST_PLUGIN_URL . '/css/invest-or-divest-widget-style.min.css' );
}

// If $countonly is true, post views will not be updated.
public function count_postviews($post_ID,$countonly=false) {
	$count_metakey = 'iod_views_count';
	$count = get_post_meta($post_ID, $count_metakey, true);
	if($countonly){
		$count = $count==''?0:$count;
		return $count;
	}
	//If the the Post Custom Field value is empty.
	if($count == ''){
		$count = 0; // set the counter to zero.
		//Delete all custom fields with the specified key from the specified post.
		delete_post_meta($post_ID, $count_metakey);
		//Add a custom (meta) field (Name/value)to the specified post.
		add_post_meta($post_ID, $count_metakey, '1');
		return $count . ' View';
		//If the the Post Custom Field value is NOT empty.
	}else{
		$count++;
		//Update the value of an existing meta key (custom field) for the specified post.
		update_post_meta($post_ID, $count_metakey, $count);
		if($count == '1'){
			return $count . ' view';
		}
		else {
			return $count . ' views';
		}
	}
}
}
