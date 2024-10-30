<?php
/*
 * Plugin Name:       BP topics to WP REST API
 * Description:       Expose the buddypress topics to rest api
 * Version:           0.0.1
 * Author:            Designman
 * Author URI:        http://designman.io/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       expose-bp-topics
 * Domain Path:       /languages
 */

 //_bbp_forum_id
 //_bbp_topic_id
 //_bbp_author_ip
 //_bbp_last_reply_id
 //_bbp_last_active_id
 //_bbp_last_active_time
 //_bbp_reply_count
 //_bbp_reply_count_hidden
 //_bbp_voice_count

 function bpre_add_reply(WP_REST_Request $data){
   $forum = $data['forum'];
   $topic = $data['topic'];
   $author = $data['author'];
   $title = $data['title'];
   $content = $data['content'];
   $reply_data = bbp_parse_args(array(), array(
    'post_parent'    => $topic, // topic ID
 		'post_status'    =>  bbp_get_public_status_id(), //'published',
 		'post_type'      => bbp_get_reply_post_type(),
 		'post_author'    => bbp_get_current_user_id(),
 		'post_password'  => '',
 		'post_content'   => $content,
 		'post_title'     => $title,
 		'menu_order'     => 0,
 		'comment_status' => 'closed'
  ), 'insert_reply' );

  $reply_meta = bbp_parse_args( array(), array(
   'author_ip' => bbp_current_author_ip(),
   'forum_id'  => $forum,
   'topic_id'  => $topic,
), 'insert_reply_meta' );
   bbp_insert_reply($reply_data, $reply_meta);
 }
 add_action( 'rest_api_init', function () {
	register_rest_route( 'bpre/v1', '/new_reply/', array(
		'methods' => 'POST',
		'callback' => 'bpre_add_reply',
    'permission_callback' => function () {
      //capabilities here: https://codex.bbpress.org/getting-started/before-installing/bbpress-user-roles-and-capabilities/
      // WP capabilities here: https://codex.wordpress.org/Roles_and_Capabilities
			return current_user_can( 'publish_topics' );
		}
	) );
} );

function bpre_add_topic_args()
{
    global $wp_post_types;

    $wp_post_types['forum']->show_in_rest = true;
    $wp_post_types['forum']->rest_base = 'forum';
    // $wp_post_types['forum']->hierarchical = true;
    $wp_post_types['forum']->rest_controller_class = 'WP_REST_Posts_Controller';

    $wp_post_types['topic']->show_in_rest = true;
    $wp_post_types['topic']->rest_base = 'topic';
    // $wp_post_types['topic']->hierarchical = true;
    $wp_post_types['topic']->rest_controller_class = 'WP_REST_Posts_Controller';

    $wp_post_types['reply']->show_in_rest = true;
    // $wp_post_types['reply']->hierarchical = true;
    $wp_post_types['reply']->rest_base = 'reply';
    $wp_post_types['reply']->rest_controller_class = 'WP_REST_Posts_Controller';
}
add_action('init', 'bpre_add_topic_args', 30);

//unprotect bbp meta
add_filter('is_protected_meta', function ($protected, $meta_key) {
   if ('_bbp_forum_id' == $meta_key || '_bbp_topic_id' == $meta_key || '_bbp_author_ip' == $meta_key && defined('REST_REQUEST') && REST_REQUEST) {
       $protected = false;
   }

   return $protected;
}, 10, 2);

//allow updating bbp meta
function bpre_register_post_parent()
{
    register_rest_field('topic',
   'parent',
   array(
      'get_callback' => 'bpre_get_topic_meta',
      'update_callback' => 'bpre_update_topic_meta',
      'schema' => null,
   )
);
    register_rest_field('reply',
   'parent',
   array(
      'get_callback' => 'bpre_get_topic_meta',
      'update_callback' => 'bpre_update_topic_meta',
      'schema' => null,
   )
);
}
// add_action('rest_api_init', 'bpre_register_post_parent');

function bpre_register_bbp_forum_id()
{
    register_rest_field('topic',
   '_bbp_forum_id',
   array(
      'get_callback' => 'bpre_get_topic_meta',
      'update_callback' => 'bpre_update_topic_meta',
      'schema' => null,
   )
);
    register_rest_field('reply',
   '_bbp_forum_id',
   array(
      'get_callback' => 'bpre_get_topic_meta',
      'update_callback' => 'bpre_update_topic_meta',
      'schema' => null,
   )
);
}
add_action('rest_api_init', 'bpre_register_bbp_forum_id');

function bpre_register_bbp_topic_id()
{
    register_rest_field('topic',
   '_bbp_topic_id',
   array(
      'get_callback' => 'bpre_get_topic_meta',
      'update_callback' => 'bpre_update_topic_meta',
      'schema' => null,
   )
);
    register_rest_field('reply',
   '_bbp_topic_id',
   array(
      'get_callback' => 'bpre_get_topic_meta',
      'update_callback' => 'bpre_update_topic_meta',
      'schema' => null,
   )
);
}
add_action('rest_api_init', 'bpre_register_bbp_topic_id');

function bpre_register_bbp_author_ip()
{
    register_rest_field('topic',
   '_bbp_author_ip',
   array(
      'get_callback' => 'bpre_get_topic_meta',
      'update_callback' => 'bpre_update_topic_meta',
      'schema' => null,
   )
);
    register_rest_field('reply',
   '_bbp_author_ip',
   array(
      'get_callback' => 'bpre_get_topic_meta',
      'update_callback' => 'bpre_update_topic_meta',
      'schema' => null,
   )
);
}
add_action('rest_api_init', 'bpre_register_bbp_author_ip');
function bpre_register_bbp_last_reply_id()
{
    register_rest_field('topic',
   '_bbp_last_reply_id',
   array(
      'get_callback' => 'bpre_get_topic_meta',
      'update_callback' => 'bpre_update_topic_meta',
      'schema' => null,
   )
);
}
add_action('rest_api_init', 'bpre_register_bbp_last_reply_id');

function bpre_register_bbp_last_active_id()
{
    register_rest_field('topic',
   '_bbp_last_active_id',
   array(
      'get_callback' => 'bpre_get_topic_meta',
      'update_callback' => 'bpre_update_topic_meta',
      'schema' => null,
   )
);
}
add_action('rest_api_init', 'bpre_register_bbp_last_active_id');

function bpre_register_bbp_last_active_time()
{
    register_rest_field('topic',
   '_bbp_last_active_time',
   array(
      'get_callback' => 'bpre_get_topic_meta',
      'update_callback' => 'bpre_update_topic_meta',
      'schema' => null,
   )
);
}
add_action('rest_api_init', 'bpre_register_bbp_last_active_time');

function bpre_register_bbp_reply_count()
{
    register_rest_field('topic',
   '_bbp_reply_count',
   array(
      'get_callback' => 'bpre_get_topic_meta',
      'update_callback' => 'bpre_update_topic_meta',
      'schema' => null,
   )
);
}
add_action('rest_api_init', 'bpre_register_bbp_reply_count');

function bpre_register_bbp_reply_count_hidden()
{
    register_rest_field('topic',
   '_bbp_reply_count_hidden',
   array(
      'get_callback' => 'bpre_get_topic_meta',
      'update_callback' => 'bpre_update_topic_meta',
      'schema' => null,
   )
);
}
add_action('rest_api_init', 'bpre_register_bbp_reply_count_hidden');
function bpre_register_bbp_voice_count()
{
    register_rest_field('topic',
   '_bbp_voice_count',
   array(
      'get_callback' => 'bpre_get_topic_meta',
      'update_callback' => 'bpre_update_topic_meta',
      'schema' => null,
   )
);
}
add_action('rest_api_init', 'bpre_register_bbp_voice_count');

// function bpre_register_post_parent()
// {
//     register_rest_field('topic',
//    'post_parent',
//    array(
//       'get_callback' => 'bpre_get_topic_meta',
//       'update_callback' => 'bpre_update_topic_meta',
//       'schema' => null,
//    )
// );
// }
// add_action('rest_api_init', 'bpre_register_post_parent');

function bpre_get_topic_meta($object, $field_name, $request)
{
    return get_post_meta($object[ 'id' ], $field_name, true);
}
function bpre_update_topic_meta($value, $object, $field_name)
{
    if (!$value || !is_string($value)) {
        return;
    }
    return update_post_meta($object->ID, $field_name, strip_tags($value));
}
