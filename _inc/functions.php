<?php


function mwp_wiki_settings() {
	if ( !current_user_can( 'manage_options' ) )  {
		wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
	}

	?>

	<div class="wrap">
    <h2>My Wiki Posts</h2>
    <div id="mwp_response"></div>

    <?php  /*if(isset($_POST['wiki_posts'])){

		$cat = $_POST['cat'];
		$rewrite_posts = $_POST['rewrite_existing_posts'];

		mwp_update_posts($cat, $rewrite_posts);

	}*/

    ?>
	<form id="mwp_form"  method="post" action="<?php echo $_SERVER['REQUEST_URI']; ?>">

	<table class="form-table">
	<tbody>
     <tr>
        <th scope="row"><label for="wiki_category">Wiki Posts Category</label></th>
      <td>
          <?php

		    $args = array(
			  'show_option_all'    => '',
			  'show_option_none'   => 'Select category',
			  'orderby'            => 'name',
			  'order'              => 'ASC',
			  'show_count'         => 0,
			  'hide_empty'         => 1,
			  'child_of'           => 0,
			  'name'               => 'cat',
			  'hierarchical'       => 1
		  );

		    wp_dropdown_categories($args);

		  ?>
          <p class="description">Select a category whose content will be wikipedia posts</p>
      </td>

     </tr>
     <tr>
       <th scope="row"><label for="rewrite_existing_posts">Rewrite existing Posts?</label></th>
         <td>
           <fieldset><legend class="screen-reader-text"><span>Rewrite existing Posts?</span></legend>
           <label for="rewrite_existing_posts">
            <input name="rewrite_existing_posts" id="rewrite_existing_posts" value="1" type="checkbox">
            Rewrite existing posts</label>
          </fieldset>
         </td>
       </tr>
    </tbody>
	</table>
	<p class="submit">
	  <input type='submit' name="wiki_posts" value='Submit' class='button button-primary'>

	</p>

	</form>
    <div id="progressbar"><div class="progress-label">Running...</div></div>
    </div>

    <?php
}

function mwp_update_posts($cat_id, $rewrite_posts){

	global $posts,$wpdb;

	$no_of_updates = 0;
	$failed_wikis = array();
	$wiki_content = "";

	$rewrite_posts = ($rewrite_posts == 1)? $rewrite_posts : 0;

	$querystr =  "SELECT id, post_title, post_content
				  FROM {$wpdb->prefix}posts wpp
				  JOIN $wpdb->term_relationships tr ON wpp.ID = tr.object_id
				  JOIN $wpdb->term_taxonomy tt ON (tr.term_taxonomy_id = tt.term_taxonomy_id
					 AND (tt.term_id = $cat_id OR tt.parent=$cat_id)
					 AND tt.taxonomy = 'category')
				  WHERE post_status='publish'
				  AND post_type='post'";

	if ($wp_posts = $wpdb->get_results($querystr) ) {

	    $rows = $wpdb->num_rows;

	    foreach ($wp_posts as $post) {

			//only fetch wikis if post content is empty
			if(trim($post->post_content) == ''){

				$wiki_content = mwp_fetch_wikis($post->post_title);

				//If $wiki_content has something from the wikis..
				if(!empty($wiki_content)){

					//update wp post
					$updates = mwp_single_update($post->id, $wiki_content);

					//increment no of updates
					$no_of_updates = $no_of_updates + $updates;

				}else{//if $wiki_content  is empty dont wp_update, add the title to array of failed wikis


					$failed_wikis[] = $post->post_title;

				}


			}else{//post content is not empty..

				//check if $rewrite_posts is set, then update posts if set
				if($rewrite_posts == 1){


					$wiki_content = mwp_fetch_wikis($post->post_title);

					//If $wiki_content has something from the wikis update wp post
					if(!empty($wiki_content)){


					$no_of_updates = mwp_single_update($post->id, $wiki_content);

					//increment no of updates
					$no_of_updates++;

					}else{//if $post_content  is empty dont wp_update, add the title to array of failed wikis


						$failed_wikis[] = $post->post_title;

					}


				}else{

					//don't fetch wikis
				    continue;

				}

			}

		}

	}


	//User notices
	if($no_of_updates > 0 && empty($failed_wikis)){

	   $msg = __($no_of_updates.' post(s)  updated successfully.');

	   $notice = '<div class="updated fade" id="message"><p>' . $msg . '</p></div>';

	}

	if(!empty($failed_wikis)){

		 $failed_wikis = array_unique($failed_wikis);
		 $no_fails = count($failed_wikis);
		 $fails = implode(",", $failed_wikis);
	}

	if($no_of_updates > 0 && !empty($failed_wikis)){


		  $msg = __($no_of_updates.' post(s)  updated successfully. '.$no_fails.' post(s) could not be updated.');

		  $notice = '<div class="updated fade" id="message"><p>' . $msg . '</p><p><strong>Failed Post Titles: </strong>' . $fails . '</p></div>';




	}

	if($no_of_updates == 0 && !empty($failed_wikis)){

		 $msg = __('No posts were updated!');
		 $notice = '<div class="error fade" id="message"><p><strong>'.$msg.'</strong></p><p><strong>Failed Post Titles: </strong>' . $fails . '</p></div>';

	}

	if($no_of_updates == 0 && empty($failed_wikis)){

		 $msg = __('No posts were updated!');
		 $notice = '<div class="error fade" id="message"><p><strong>'.$msg.'</strong></p></div>';

	}



	return $notice;


}

function mwp_single_update($post_id, $wiki_content){


	  global $wpdb;
	  $updates = 0;


	  /*	Getting 'invalid post ID' error for whatever reason

	   $post_vars = array(
			'id'           => $post->id ,
			'post_content' => $post_content
		);


		$updated = wp_update_post($post_vars, true);
		$result .= $updated->get_error_message().',';

		*/


		$update_query = "UPDATE $wpdb->posts SET $wpdb->posts.post_content = '".$wpdb->escape($wiki_content)."' WHERE $wpdb->posts.id = '".$post_id."'";
		$updated = $wpdb->query($update_query);

		if($updated){//count updates

			$updates = 1;
		}

		return $updates;


}


//Fetches wiki posts
function mwp_fetch_wikis($wiki_page){

	// action=query: get parsed text
	// page=$wiki_page
	// format=json: in json format
	// prop=extracts: send the text content of the article

	$site_url = get_option('admin_email');
	$admin_email = get_option('siteurl');

	$user_agent = "my-wiki-posts2/1.0 (".$site_url."; ".$admin_email.")";

	$content = array();

	/* Incase of uppercased letters - wikipedia api appears to be case sensitive.
	   Titles with special characters like WOJCIECH SZCZĘSNY might not work here */
	$wiki_page = to_upper($wiki_page);

	//make title a valid input
	if(preg_match('/\s/',$wiki_page)){

		$wiki_page = str_replace(' ', '_', $wiki_page);
	}

	//$url = 'http://en.wikipedia.org/w/api.php?action=parse&page='.$wiki_page.'&format=json&prop=text&section=1';
	$url = 'http://en.wikipedia.org/w/api.php?action=query&prop=extracts&format=json&exchars=1000&continue=&titles='.$wiki_page;

	// $curl_headers = array(
	// 	'Accept: application/json',
	// 	'Content-Type: application/json'
	//
	// );

	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
	curl_setopt($ch, CURLOPT_HTTPHEADER, $curl_headers);
	curl_setopt ($ch, CURLOPT_USERAGENT, $user_agent);  // required by wikipedia.org server; use YOUR user agent with YOUR contact information. (otherwise your IP might get blocked)

	$result = curl_exec($ch);
	$error  = curl_error($ch);
	$code   = curl_getinfo($ch, CURLINFO_HTTP_CODE);
	curl_close($ch);

	$json = json_decode($result, TRUE);

	// echo "<pre> Result: ";
	// print_r($json);
	// echo "<pre>";
	// echo 'Error: '.$error;
	// echo 'Code: '.$code;

	$content_arr = $json['query']['pages'];

	if(!empty($content_arr)){

		$content = end($json['query']['pages']);
	}


	if(preg_match_all('!<p>(.+?)</p>!sim',$content['extract'],$matches,PREG_PATTERN_ORDER)){

		//fetch text from the 2nd section/<p>
		$post_body =  strip_tags($matches[0][1]);

	}

	return $post_body;


}


//Converts letter after dash(-) to Upper Case
function to_upper($input){

	  $input=ucwords(strtolower($input));
	  $arr=explode('-', $input);
	  $input=array();
	  foreach($arr as $v)
	  {
		  $input[]=ucfirst($v);
	  }
	  $output=implode('-', $input);
	  return $output;
}




//Ajax process
add_action('wp_ajax_mwp_ajax_update', 'mwp_ajax_update');
add_action('wp_ajax_nopriv_mwp_ajax_update', 'mwp_ajax_update');

function mwp_ajax_update(){

	    // Security check
        check_ajax_referer( 'ajax_post_validation', 'secure' );

		parse_str($_POST['postdata'], $data_arr);


	    $cat = $data_arr['cat'];
		$rewrite_posts = $data_arr['rewrite_existing_posts'];

		if($cat == -1){

			 $msg = __('Please select a category');
		     $notice = '<div class="error fade" id="message"><p><strong>'.$msg.'</strong></p></div>';

			 echo  $notice;
			 wp_die();

		}

		$response = mwp_update_posts($cat, $rewrite_posts);

		echo $response;

		wp_die();

}



//Add wiki link to updated posts category  'players'
add_filter('the_content', 'add_wiki_link');
function add_wiki_link($content){

	$category = get_the_category();
    $cat_parent_id = $category[0]->category_parent;

	if($cat_parent_id == 40){

		$title = get_the_title();
		$url_title = str_replace(' ', '_', $title);
		$wiki_link = "<a href='http://en.wikipedia.org/wiki/".$url_title."' target='_blank' title='Read more from Wikipedia'  class='more'>&raquo;  read more</a>";
	}else{

		$wiki_link = "";

	}

	return (is_single() || is_category(40)) ? $content.$wiki_link : $content;

	//return $content.$wiki_link;

}





?>
