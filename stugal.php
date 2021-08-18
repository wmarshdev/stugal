<?php

/*
 
Plugin Name: Stugal
 
Plugin URI: https://github.com/wmarshdev/stugal
 
Description: Custom gallery shortcode plugin for Stu-style purposes.
 
Version: 1.0
 
Author: Wayne Marsh
 
License: MIT License
 
Text Domain: wmarshdev
 
*/

/*
 * hooks
 */
function stugal_styles()
{
  wp_enqueue_style('movies',  plugin_dir_url(__FILE__) . 'style.css');
}
add_action('wp_enqueue_scripts', 'stugal_styles');

function stugal_register_shortcode()
{
  add_shortcode('stugal', 'stugal_shortcode');
}
add_action('init', 'stugal_register_shortcode');

/*
 * shortcode implementation
 */

function stugal_shortcode($atts = array())
{
  extract(shortcode_atts(array(
    'images' => ""
  ), $atts));

  $images = explode(",", $images);

  $out = '<div class="stugal-container">';

  foreach ($images as $imageStr) {
    $imageId = intval($imageStr);

    $media = wp_get_attachment_image_src($imageId, 'thumbnail');

    if ($media === false) {
      $out .= <<<END
      <div class="error">
        BAD IMAGE ID '{$imageId}'
      </div>
      END;
    } else {
      $mediaFull = wp_get_attachment_image_src($imageId, 'full');
      if ($mediaFull === false) {
        die("unexpected: couldn't get media url");
      }

      $alt = get_post_meta($imageId, '_wp_attachment_image_alt', true);
      $altAttr = '';
      if ($alt !== false && $alt !== "") {
        $altSafe = htmlspecialchars($alt);
        $altAttr = "aria-label=\"{$altSafe}\"";
      }

      $imageHtml = <<<END
      <div>
        <a href="{$mediaFull[0]}" target="_blank">
          <span class="image-container" style="background-image: url('{$media[0]}')" role="img" {$altAttr}/>
        </a>
      </div>
      END;

      $caption = get_post($imageId, "OBJECT", "display")->post_content;
      $captionHtml = '';
      if ("" != $caption) {
        $captionHtml = '<div class="caption">' . $caption . '</div>';
      }
      $out .= "<div>{$imageHtml}{$captionHtml}</div>";
    }
  }

  $out .= '</div>';

  return $out;
}
