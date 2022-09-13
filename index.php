<?php
/*
Plugin Name: Awebon Quiz
plugin url:http://www.awebon.com/
version:1.0
author:Karthikeyan Balasubramanian
author url:http://karthik.awebon.com/
Description:  Create question and answer for quiz
*/

global $awebon_quiz_db_version;
$awebon_quiz_db_version = '1.0';

function awebon_quiz_install() {
  global $wpdb;
  global $awebon_quiz_db_version;

  $table_name = $wpdb->prefix . 'quiz_answer';
  
  $charset_collate = $wpdb->get_charset_collate();

  $sql = "CREATE TABLE IF NOT EXISTS $table_name (
    id int(100) NOT NULL AUTO_INCREMENT,
    question_id int(100) NOT NULL,
    answer_text varchar(1000) NOT NULL,
    ans_number int(100) NOT NULL,
    ans_image varchar(250) NOT NULL,
    ans_description varchar(250) NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY id (id)
  ) $charset_collate;";

  require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
  dbDelta( $sql );

  add_option( 'awebon_quiz_db_version', $awebon_quiz_db_version );
}

register_activation_hook( __FILE__, 'awebon_quiz_install' );


add_action( 'init', 'quiz' );
function quiz() {
  register_post_type( 'quiz',
    array(
      'labels' => array(
        'name' => __( 'Quizs ' ),
        'singular_name' => __( 'Quiz ' ),
        'add_new'            => __( 'Add Quiz' ),
        'add_new_item'       => __( 'Add New Quiz ')
      ),
      'public' => true,
      'supports' => array('title','editor','thumbnail'),
      'register_meta_box_cb' => 'add_answer_metaboxes'
    )
  );
}
// Add the Events Meta Boxes
function add_answer_metaboxes() {
  add_meta_box('wpt_question_answer', 'Answers', 'add_answers_section', 'quiz', 'normal', 'default');
  add_meta_box('wpt_layout_image', 'Add layout image', 'add_layout_image', 'quiz', 'normal', 'default');
}
function add_answers_section() {
  global $post;
  // Noncename needed to verify where the data originated
  echo '<input type="hidden" name="quizmeta_noncename" id="quizmeta_noncename" value="' . 
  wp_create_nonce( plugin_basename(__FILE__) ) . '" />';
  global $wpdb;
  // Echo out the field
  add_thickbox();
  $row = $wpdb->get_results( "SELECT * FROM wp_quiz_answer WHERE question_id=".$post->ID);
 /*echo '<span  style="text-decoration:underline; cursor:pointer; " id="add"> Add More </span>';*/
 echo '<a href="#TB_inline?width=600&height=550&inlineId=my-content-id" class="thickbox page-title-action">Add Answer</a>';
 if(!empty($row)){
  foreach ( $row as $row ) 
    { 
  echo '<div id="addquiz'.$row->id.'">';
  echo '<label>Name</label><input type="text" name="answer['.$row->id.'][text]"  value="'.$row->answer_text.'" class="widefat" />';
  echo '<p><input type="hidden" name="answer['.$row->id.'][update_id]" id="update_id" value="' . 
  $row->id. '" />
    <label for="meta-image" class="prfx-row-title">Path</label><br>
    <input type="text" readonly name="answer['.$row->id.'][meta-image]" id="meta-image-1" value="'.$row->ans_image.'" /><br><label>Animal image</label><br>
    <input type="button" rowcount="1" id="meta-image-button" name="meta-image-button" class="button meta-image-button" value="" />
</p>';
  echo  '<label>Description</label><br><textarea rows="4" id="img_description" name="answer['.$row->id.'][img_description]"  cols="50">'. $row->ans_description.'</textarea><a href="javascript:void(0);" onclick="removeRow('.$row->id.');">Delete</a>';
    echo '</div>';
    }
  }else{
    echo '<div id="addquiz">';
  echo '<label>Animal name</label><input type="text"  name="answer[1][text]" id="answer[1][text]" value="" class="widefat" />';
  echo '<p><input type="hidden" name="answer[1][update_id]"  id="update_id" value="" />
    <label for="meta-image" class="prfx-row-title">Path</label><br>
  <div id="show-ans-image-1"></div>
    <input type="hidden" name="answer[1][meta-image]" id="meta-image-1" value="" /><br><label>Animal image</label><br>
    <input type="button" rowcount="1" id="meta-image-button" name="meta-image-button" class="button meta-image-button" value="" />
</p>';
  echo  '<label>Description</label><br><textarea rows="4" id="img_description" name="answer[1][img_description]"  cols="50"></textarea>';
   echo '</div>';   
  }
  echo '<div id="addedRows"></div>';
  }



echo '<div id="my-content-id" style="display:none;">';
     echo '<div id="addquiz">
              <form name="post" action="post.php" method="post" id="post" autocomplete="off">
                  <h2>Add Answer</h2>
                  <label style="font-weight:bold">Name :</label>
                  <p><input type="text"  name="answer[1][text]" id="answer[1][text]" value="" class="widefat" /></p><br>
                  <input type="hidden" name="answer[1][update_id]"  id="update_id" value="" />
                <div id="show-ans-image-1"></div>
                  <input type="hidden" name="answer[1][meta-image]" id="meta-image-1" value="" />
                  <p><label style="font-weight:bold">Upload Image :</label>
                  <input type="button" rowcount="1" id="meta-image-button" name="meta-image-button" class="button meta-image-button" value="Select Your" /></p>
                <div class="custom-img-container"></div>
              <br>
              <label style="font-weight:bold">Description :</label>
              <p><textarea rows="4" id="img_description" name="answer[1][img_description]"  cols="50"></textarea><p>
              <p><input name="save" type="submit" class="button button-primary button-large" id="publish" value="Save"></p>';
      echo '</form>
          </div>';   
echo '</div>';



// Save the Metabox Data
add_action('save_post', 'wpt_save_answer', 1, 2); // save the custom fields
function wpt_save_answer($post_id, $post) {
  // verify this came from the our screen and with proper authorization,
  // because save_post can be triggered at other times
  if ( !wp_verify_nonce( $_POST['quizmeta_noncename'], plugin_basename(__FILE__) )) {
  return $post->ID;
  }
  // Is the user allowed to edit the post or page?
  if ( !current_user_can( 'edit_post', $post->ID ))
    return $post->ID;
  // OK, we're authenticated: we need to find and save the data
  // We'll put it into an array to make it easier to loop though.
  $events_meta['answer_text'] = $_POST['answer_text'];
  global $wpdb;
    $rowmax = $wpdb->get_row( "SELECT * FROM ".$wpdb->prefix."quiz_answer WHERE question_id=".$post->ID." order by ans_number DESC LIMIT 1");
    $ans_number=$rowmax->ans_number;
     if(!empty($ans_number))
     {
        $ans_number++;
        $ans_number=$ans_number;
     }else{
        $ans_number=1;
     }
    $post_answer=$_POST['answer'];
    foreach($post_answer as $answer){
      if(empty($answer['update_id'])){
           $wpdb->insert($wpdb->prefix.'quiz_answer',array('question_id' =>$post->ID,'answer_text' => $answer['text'],'ans_image'=>$answer['meta-image'],'ans_description'=>$answer['img_description'],'ans_number'=>$ans_number),array('%d','%s','%s','%s','%d'));
          $ans_number++;
     }else{            
            $wpdb->update($wpdb->prefix.'quiz_answer',array('question_id' =>$post->ID,'answer_text' => $answer['text'],'ans_image'=>$answer['meta-image'],'ans_description'=>$answer['img_description']),array('id' =>$answer['update_id']));
       }
    }
     // Checks for input and sanitizes/saves if needed


    if( isset( $_POST[ 'layout-image' ] ) ) {
        update_post_meta( $post_id, 'layout-text', sanitize_text_field( $_POST[ 'layout-image' ] ) );
      
    }

}
/**
 * Loads the image management javascript
 */
function prfx_image_enqueue() {
    global $typenow;
    if( $typenow == 'quiz' ) {
        wp_enqueue_media();
        // Registers and enqueues the required javascript.
       wp_register_script( 'meta-box-image', plugin_dir_url( __FILE__ ) .'js/meta-box-image.js', array( 'jquery' ) );
        wp_localize_script( 'meta-box-image', 'meta_image',
            array(
                'title' => __( 'Choose or Upload an Image', 'prfx-textdomain' ),
                'button' => __( 'Use this image', 'prfx-textdomain' ),
            )
        );
        wp_enqueue_script( 'meta-box-image' );
    }
}
add_action( 'admin_enqueue_scripts', 'prfx_image_enqueue' );
add_action( 'wp_ajax_answer_remove', 'answer_remove' );
function answer_remove() {
    // The $_REQUEST contains all the data sent via ajax
    if ( isset($_REQUEST) ) {
       $ans_row = $_POST['rowid'];
       global $wpdb;
       $result=$wpdb->delete($wpdb->prefix.'quiz_answer', array( 'id' => $ans_row ) );
       if($result==true)
       {
        echo "successfully deleted ".$ans_row;
       }else
       {
        echo "no record deleted ".$ans_row;
       }
    }
    // Always die in functions echoing ajax content
   die();
}
function show_user_answer_function( $user_fb_id,$user_id,$fb_image) {
  global $wpdb;

  $find_id = "SELECT COUNT(*) FROM  wp_quiz_answer WHERE question_id=".$_POST['game_id'];
  $total_row=$wpdb->get_var ($wpdb->prepare ($find_id,$user_id));

  
  // select answer rows
  $number =$user_fb_id;
  $db_row=$total_row;
  // this count of answer table 
        $row_total=strlen($db_row);
        $conn=$number;
        $number  = array_map('intval', str_split($number));
        $len=count($number);
        while($len>=$row_total && $conn>$db_row) {
          $number  = array_sum($number);
          //echo "<br>";
          $conn = $number;
         // echo "<br>";
            $number  = array_map('intval', str_split($number));
            $len=count($number);
           } 
           
           //echo "<br>";
           $ans_number = implode('', $number);
            $find_id = "SELECT ans_image,ans_description,answer_text FROM  wp_quiz_answer WHERE question_id=".$_POST['game_id']." AND ans_number=".$ans_number;
            $ans_row=$wpdb->get_row($wpdb->prepare ($find_id,$user_id));
            if(empty($ans_row->ans_image))
            {
              echo $ans_number=1;
              
              while($ans_number<=$db_row)
              {
                $find_id = "SELECT ans_image,ans_description,answer_text FROM  wp_quiz_answer WHERE question_id=".$_POST['game_id']." AND ans_number=".$ans_number;
                $ans_row=$wpdb->get_row($wpdb->prepare ($find_id,$user_id));
                  if(!empty($ans_row))
                  {
                    break;
                  }
                $ans_number++;
              }
            }
            $ans_image=$ans_row->ans_image;
            $ans_desc=$ans_row->ans_description;
            $layout_image = get_post_meta($_POST['game_id'],'layout-text', true );

            //$feature_image = wp_get_attachment_image_src( get_post_thumbnail_id($_POST['game_id']), 'single-post-thumbnail' );
             
            
            $dest = @imagecreatefromjpeg($layout_image);
            $src = @imagecreatefromjpeg($fb_image);
            $lion = @imagecreatefromjpeg($ans_image);
            $dtext = $ans_desc;//@imagecreatefrompng('character-text.png');

            $lines = explode('|', wordwrap($dtext,29, '|'));
            $y = 95;
            $im = imagecreatetruecolor(400, 30);
            $font_color = imagecolorallocate($im, 0, 0, 0);
            $font_colorhead = imagecolorallocate($im, 255, 255, 255);
            $font =plugin_dir_path( __FILE__ ).'arial.ttf';
            $laniname = $ans_row->answer_text;
            //imagealphablending($dtext, false);
            //imagesavealpha($dtext, true);
            imagecopymerge($dest, $src, 38, 89, 0, 0, 115, 115, 100);
            imagecopymerge($dest, $lion, 393, 58, 0, 0, 174, 174, 100);
            imagettftext($dest, 25, 0, 268, 45, $font_colorhead, $font, $laniname);
            $cor = imagecolorallocate($dest, 0, 0, 0);

  foreach ($lines as $line) 
  {
      imagettftext($dest, 12, 0, 172, $y, $font_color, $font, $line);

    // Increment Y so the next line is below the previous line
      $y += 23;
  }
  //imagestring($dest,5,250,60,urldecode($dtext),$cor); 
  //imagecopymerge($dest, $dtext, 170, 10, 0, 0, 208, 225, 100);
  //header('Content-Type: image/jpeg');

  $random_file_name = rand(100000, 999999);
  imagejpeg($dest,plugin_dir_path( __FILE__ ).'user_quiz_photo/'.$random_file_name.$user_id.'.jpg');
  imagedestroy($dest);
  imagedestroy($src);
  $_SESSION['quiz_user_file_name']=$random_file_name.$user_id;

}
add_action( 'show_user_answer', 'show_user_answer_function', 10,3 );

add_action('init', 'myStartSession', 1);
function myStartSession() {
    if(!session_id()) {
        session_start();
    }
}


function add_layout_image(){

  global $post;

echo "<b>Select layout image<b>";
echo '<p>  <label for="meta-image" class="prfx-row-title">Path</label>
    <input type="text" readonly name="layout-image" id="layou-image-1"  value="'.get_post_meta($post->ID,'layout-text', true ).'" /><br><label>Layout image</label><br>
    <input type="button" rowcount="1" id="layout-image-button" name="layout-image-button" class="button layout-image-button" value="" /> </p>';

}

function myplugin_activate() {
  add_filter( 'rewrite_rules_array','my_insert_rewrite_rules' );
  add_filter( 'query_vars','my_insert_query_vars' );
  add_action( 'wp_loaded','my_flush_rules' );
}

register_activation_hook( __FILE__, 'myplugin_activate' );

// flush_rules() if our rules are not yet included
function my_flush_rules(){
  $rules = get_option( 'rewrite_rules' );

  if ( ! isset( $rules['(project)/(\d*)$'] ) ) {
    global $wp_rewrite;
      $wp_rewrite->flush_rules();
  }
}

// Adding a new rule
function my_insert_rewrite_rules( $rules )
{
  $newrules = array();
  $newrules['(project)/(\d*)$'] = 'index.php?pagename=$matches[1]&id=$matches[2]';
  return $newrules + $rules;
}

// Adding the id var so that WP recognizes it
function my_insert_query_vars( $vars )
{
    array_push($vars, 'id');
    return $vars;
}