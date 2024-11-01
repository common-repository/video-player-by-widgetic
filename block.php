<?php
/**
 * UTILS FUNCTIONS
 */
$generateStrFn = function (
   int $length = 64,
   string $keyspace = "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"
): string
{
   if ($length < 1) {
       throw new \RangeException("Length must be a positive integer");
   }
   $pieces = [];
   $max = mb_strlen($keyspace, "8bit") - 1;
   for ($i = 0; $i < $length; ++$i) {
       $pieces[] = $keyspace[random_int(0, $max)];
   }
   return implode("", $pieces);
};

/**
 * Registers all block assets so that they can be enqueued 
 * through Gutenberg in the corresponding context.
 *
 * Passes translations to JavaScript.
 */

// define the block init function with custom name
$initFunction = function() use($blockWidgetId, $widgetMeta, $blockPath, $generateStrFn)
{
    // widgetic secret option
	$secret = $generateStrFn(32);  // secure enough
	$option = "widgetic/secret";
	add_option($option, $secret);

	// Widgetic SDK
	wp_enqueue_script("@widgetic/sdk", "https://widgetic.com/sdk/sdk.js");
	wp_localize_script("@widgetic/sdk", 
        "WP", 
        array(
            "endpoint" => esc_url_raw(rest_url()),
            "nonce" => wp_create_nonce("wp_rest")
	    )
    );


    // DASHBOARD SCRIPT FILES
	// register dashboard js
	wp_enqueue_script('@widgetic/dashboard-' . $blockWidgetId,
		plugin_dir_url(__FILE__) . "build-dashboard/dashboard.bundle.js",
		array(),
		true,
		true
	);
    // register dashboard custom style
	wp_register_style("widgetic-dashboard", 
        plugin_dir_url(__FILE__) . "build-dashboard/dashboard.bundle.css", false, "1.0.0", "all");
	wp_enqueue_style( "widgetic-dashboard");    



    // BLOCK SCRIPT FILES FOR WP-EDITOR
	// register block js script and assets file: load dependencies and version
	$asset_file = include(plugin_dir_path(__FILE__) . "build/index.asset.php");
    $blockJSName = $widgetMeta->slug . '-plugin-by-widgetic';
	wp_register_script($blockJSName, 
        plugins_url("build/index.js", __FILE__),
		$asset_file["dependencies"],
		$asset_file["version"]
	);
    // register widgetic plugins data
    wp_localize_script(
		$blockJSName,
		"wdgPlgsData",
		array("assetsURL" => plugins_url("assets/", __FILE__))
	);
	// register block style
    $blockCSSName = "widgetic-plugin-by-widgetic";
	wp_register_style($blockCSSName, 
        plugin_dir_url(__FILE__) . "build/index.css", false, "1.0.0", "all");
	wp_enqueue_style( $blockCSSName);    

	// register block style in wp-editor(not used yet)
	$blockInEditorCSSPath = "css/style.css";
    $blockInEditorCSSName = "widgetic-block";
	wp_register_style($blockInEditorCSSName, 
        plugins_url($blockInEditorCSSPath, __FILE__),
		array(),
		filemtime(plugin_dir_path(__FILE__) . $blockInEditorCSSPath)
	);

	// register blocks editor style in wp-editor(not used yet)
	$widgeticEditorStylePath = "css/editor.css";
    $widgeticEditorCSSName = "widgetic-editor";
	wp_register_style($widgeticEditorCSSName,
		plugins_url($widgeticEditorStylePath, __FILE__),
		array("wp-edit-blocks"),
		filemtime(plugin_dir_path(__FILE__) . $widgeticEditorStylePath)
	);
	

	// register block namespace in wp-editor
	register_block_type($blockPath, array(
		"style" =>         $blockInEditorCSSName,
		"editor_script" => $blockJSName,
		"editor_style" =>  $widgeticEditorCSSName,
	));
};

// add the function to the init action
add_action("init", $initFunction);
?>