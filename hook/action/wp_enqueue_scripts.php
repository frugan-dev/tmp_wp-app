<?php

declare(strict_types=1);

/*
 * This file is part of the Wp-App WordPress plugin.
 *
 * (ɔ) Frugan <dev@frugan.it>
 *
 * This source file is subject to the GNU GPLv3 or later license that is bundled
 * with this source code in the file LICENSE.
 */

use DI\Container;
use WpSpaghetti\WpVite\Vite;

if (!defined('WPINC')) {
    exit;
}

return [
    [
        'callback' => static function (Container $container): void {
            // Only run on frontend for singular posts/pages
            if (is_admin() || !is_singular()) {
                return;
            }

            global $post;

            if (!$post || empty($post->post_content)) {
                return;
            }

            // Find all shortcodes matching our plugin name with more robust regex
            $shortcode_name = preg_quote(WPAPP_NAME, '/');
            $shortcode_pattern = '/\['.$shortcode_name.'\b([^\]]*)\]/';

            if (!preg_match_all($shortcode_pattern, $post->post_content, $matches, PREG_SET_ORDER)) {
                return;
            }

            Vite::init(
                WPAPP_PATH,
                WPAPP_URL,
                $container->get('plugin_data')['Version'] ?? '0.0.0',
                WPAPP_NAME
            );

            $processed_paths = []; // Avoid loading duplicate CSS files

            foreach ($matches as $match) {
                // Parse shortcode attributes
                $atts_string = trim($match[1]);
                $shortcode_atts = wpapp_parse_shortcode_attributes($atts_string);
                $path = wpapp_build_view_path($shortcode_atts);
                if (!$path) {
                    continue;
                }

                if (in_array($path, $processed_paths, true)) {
                    continue;
                }

                // Enqueue CSS - Vite::enqueueStyle now handles existence check automatically
                $handle = 'wpapp-'.str_replace('/', '-', $path);
                Vite::enqueueStyle($handle, $path); // Note: removed "css/" prefix as it's handled internally
                $processed_paths[] = $path;
            }

            // Add Vite dev scripts for hot reload
            Vite::devScripts();
        },
    ],
];
