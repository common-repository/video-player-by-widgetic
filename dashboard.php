<?php

// read block details from config file
$config = json_decode(file_get_contents(plugin_dir_path(__FILE__) . "./config/default.json"), false);
// read widget id
$blockWidgetId = $config->widgetId;
// read the widget meta file
$widgetMetaPath = "https://widgetic.com/api/v2/widgets/" . $blockWidgetId;
$widgetMeta = json_decode(file_get_contents($widgetMetaPath), false);
$widgetMeta->css = "";
$widgetMeta->js = "";
// block name
$blockName = $widgetMeta->name;
// read block slug
$blockPageStr = str_replace(" ", "_", strtolower($blockName));
// read block path
$blockPath = "widgetic/" . $widgetMeta->slug;

/**
 * DASHBOARD MENU & PAGE
 */
$add_menu_and_dashboard_page = function() use($blockName, $blockPageStr, $blockWidgetId)
{   
   // add top level menu page
   add_menu_page(
      $page_title = $blockName, // block page title
      $menu_title = $blockName, // block menu title
      $capability = "manage_options",
      $menu_slug = $blockPageStr, // block page slug
      $callback  = function() use($blockWidgetId) // callback that returns the root el
      {
         // check user capabilities
         if (!current_user_can("manage_options")) {
            return;
         }

         // return root el of the dashboard
         ?>
            <div id="dashboard-el-<?php echo esc_attr($blockWidgetId); ?>"></div>
         <?php
      },
      $icon_url = plugins_url("assets/icon.png", __FILE__) // block menu icon
   );
};

// add the function to the admin_menu action
add_action("admin_menu", $add_menu_and_dashboard_page);


/**
 * TOP LEVEL MENU:
 * callback functions
 */
$restApiFnName = function() use($blockPath)
{
   register_rest_route($blockPath, "/secret", array(
      "methods" => "GET",
      "callback" => function () {
         $option = "widgetic/secret";   // namespacing is important
         $result = get_option($option);
         if (empty($result)) {
            $result = random_str(16);
            set_option($option, $result);
         }
         return $result;
      },
      "permission_callback" => function () {
         return current_user_can("edit_others_posts");
      }
   ));

   register_rest_route($blockPath, "/current_user", array(
      "methods" => "GET",
      "callback" => function () {
         $current_user = wp_get_current_user(); // inbuilt function
         return $current_user;
      },
      "permission_callback" => function () {
         return current_user_can("edit_others_posts");
      }
   ));

   register_rest_route($blockPath, "/compositions", array(
      "methods" => "GET",
      "callback" => function () use($blockPath) {
         $posts = get_posts(array(
            "numberposts" => -1,
            "post_status" => "any, auto-draft"
         ));
      
         if (empty($posts)) { return null;}
      
         $results = array();
         for ($i = 0; $i < count($posts); $i++) {
            $post = $posts[$i];
            if (has_blocks($post->post_content)) {
               $blocks = parse_blocks($post->post_content);
               for ($j = 0; $j < count($blocks); $j++) {
                  $block = $blocks[$j];
                  if ($block["blockName"] === $blockPath) {
                     $block["post_url"] = $post->ID;
                     $block["date"] = $post->post_date;
                     $results = array_merge($results, array($block));
                  }
               }
            }
         }
      
         return $results;
      },
      "permission_callback" => function () {
         return current_user_can("edit_others_posts");
      }
   ));
};

// add the function to the rest_api_init action
add_action("rest_api_init", $restApiFnName);

?>