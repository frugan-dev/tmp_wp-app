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
use WpSpaghetti\WpEnv\Environment;

if (!defined('WPINC')) {
    exit;
}

return static function (Container $container): void {
    // https://timelord.nl/wordpress/en/software/english-how-to-avoid-global-variables-in-php.html
    // http://wiki.c2.com/?SingletonsAreEvil
    // https://stackoverflow.com/a/138012/3929620
    // https://www.yegor256.com/2016/06/27/singletons-must-die.html
    if (!function_exists('wpapp_get_container_instance')) {
        function wpapp_get_container_instance($container = null)
        {
            static $containerInstance;

            if (null !== $container) {
                $containerInstance = $container;
            }

            return $containerInstance;
        }

        wpapp_get_container_instance($container);
    }

    if (!function_exists('wpapp_get_client_ip')) {
        // https://adam-p.ca/blog/2022/03/x-forwarded-for/
        // https://developers.cloudflare.com/support/troubleshooting/restoring-visitor-ips/restoring-original-visitor-ips/
        // https://developers.cloudflare.com/fundamentals/reference/http-request-headers/
        // https://snicco.io/blog/how-to-safely-get-the-ip-address-in-a-wordpress-plugin
        // https://snicco.io/vulnerability-disclosure/wordfence/dos-through-ip-spoofing-wordfence-7-6-2
        // https://stackoverflow.com/a/2031935/3929620
        // https://stackoverflow.com/a/58239702/3929620
        function wpapp_get_client_ip(): string
        {
            $ip = '';

            foreach ([
                'REMOTE_ADDR', // The only truly reliable one if there are no proxies

                'HTTP_CF_CONNECTING_IP', // Cloudflare
                'HTTP_X_REAL_IP', // Traefik, Nginx
                'HTTP_TRUE_CLIENT_IP', // Cloudflare, Akamai

                // Less reliable headers, easily spoofed
                'HTTP_X_FORWARDED_FOR',
                'HTTP_FORWARDED',
                'HTTP_FORWARDED_FOR',
                'HTTP_X_FORWARDED',
                'HTTP_CLIENT_IP',
                'HTTP_X_CLUSTER_CLIENT_IP',
            ] as $key) {
                if (!array_key_exists($key, $_SERVER)) {
                    continue;
                }

                // For headers with IP lists, we take the first non-private IP from the beginning
                // (X-Forwarded-For is in order client -> proxy1 -> proxy2)
                $ips = array_map('trim', explode(',', (string) $_SERVER[$key]));

                foreach ($ips as $ip) {
                    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                        return $ip;
                    }
                }
            }

            return $ip;
        }
    }

    if (!function_exists('wpapp_get_timezone_obj')) {
        // https://wordpress.stackexchange.com/a/198453/99214
        function wpapp_get_timezone_obj(): DateTimeZone
        {
            $tzstring = get_option('timezone_string');
            $offset = get_option('gmt_offset');

            // Manual offset...
            // @see http://us.php.net/manual/en/timezones.others.php
            // @see https://bugs.php.net/bug.php?id=45543
            // @see https://bugs.php.net/bug.php?id=45528
            // IANA timezone database that provides PHP's timezone support uses POSIX (i.e. reversed) style signs
            if (empty($tzstring) && 0 !== $offset && floor($offset) === $offset) {
                $offset_st = $offset > 0 ? '-'.$offset : '+'.absint($offset);
                $tzstring = 'Etc/GMT'.$offset_st;
            }

            // Issue with the timezone selected, set to 'UTC'
            if (empty($tzstring)) {
                $tzstring = 'UTC';
            }

            return new DateTimeZone($tzstring);
        }
    }

    if (!function_exists('wpapp_add_notice')) {
        function wpapp_add_notice(string $message, string $type = 'success'): void
        {
            if (function_exists('wc_add_notice')) {
                wc_add_notice($message, $type);
            } else {
                $container = wpapp_get_container_instance();

                if ($container->has('session')) {
                    $session = $container->get('session');

                    if ($session) {
                        $notices = $session->get('notices', []);
                        $notices[$type][] = $message;
                        $session->set('notices', $notices);

                        // You need to add the `wpapp_notices` cookie in W3TC > Page Cache > Advanced > Rejected cookies.
                        if (!is_admin() && !is_user_logged_in() && !headers_sent() && wpapp_is_plugin_active('w3-total-cache/w3-total-cache.php')) {
                            $w3tc_config = w3_instance('W3_Config');
                            if ($w3tc_config->get_boolean('pgcache.enabled')) {
                                setcookie('wpapp_notices', '1', ['expires' => 0, 'path' => COOKIEPATH, 'domain' => COOKIE_DOMAIN]);
                            }
                        }
                    }
                }
            }
        }
    }

    if (!function_exists('wpapp_get_notices')) {
        function wpapp_get_notices(string $type = '')
        {
            if (function_exists('wc_get_notices')) {
                $notices = wc_get_notices();
                wc_clear_notices();

                return $notices;
            }

            $notices = [];
            $container = wpapp_get_container_instance();

            if ($container->has('session')) {
                $session = $container->get('session');

                if ($session) {
                    $notices = $session->get('notices', []);
                    $session->remove('notices');
                }
            }

            return !empty($type) ? ($notices[$type] ?? []) : $notices;
        }
    }

    if (!function_exists('wpapp_has_notices')) {
        function wpapp_has_notices(string $type = ''): bool
        {
            if (function_exists('wc_notice_count')) {
                return (bool) wc_notice_count($type);
            }

            $container = wpapp_get_container_instance();
            if ($container->has('session')) {
                $session = $container->get('session');

                if ($session) {
                    $notices = $session->get('notices', []);

                    return !empty($type) ? !empty($notices[$type]) : !empty($notices);
                }
            }

            return false;
        }
    }

    // https://gist.github.com/bordoni/8731880
    if (!function_exists('wpapp_get_mail_content_type')) {
        function wpapp_get_mail_content_type(): string
        {
            // text/html, multipart/alternative, text/plain
            return class_exists('DOMDocument') ? 'text/html' : 'text/plain';
        }
    }

    if (!function_exists('wpapp_is_plugin_active')) {
        function wpapp_is_plugin_active(...$args): bool
        {
            // NOTE: defined in wp-admin/includes/plugin.php, so this is only available from within the admin pages,
            // and any references to this function must be hooked to admin_init or a later action.
            // If you want to use this function from within a template or a must-use plugin, you will need to manually require plugin.php
            if (!function_exists('is_plugin_active')) {
                require_once ABSPATH.'wp-admin/includes/plugin.php';
            }

            $plugin = $args[0] ?? '';

            if (is_plugin_active($plugin)) {
                return true;
            }

            // Plugins in the mu-plugins/ folder can’t be "activated," so the function `is_plugin_active()` will return false for those plugins.
            if (file_exists(WPMU_PLUGIN_DIR.'/'.$plugin)) {
                return true;
            }

            return false;
        }
    }

    if (!function_exists('wpapp_wp_safe_redirect')) {
        function wpapp_wp_safe_redirect()
        {
            if (!function_exists('wp_safe_redirect')) {
                require_once ABSPATH.'wp-includes/pluggable.php';
            }

            // Note: wp_safe_redirect() does not exit automatically, and should almost always be followed by a call to exit;
            return wp_safe_redirect(...func_get_args());
        }
    }

    if (!function_exists('wpapp_cache_busting')) {
        function wpapp_cache_busting(string $url): string
        {
            $parsed_url = parse_url($url);
            $path = $parsed_url['path'];

            $home_path = parse_url(home_url(), PHP_URL_PATH);
            $home_path = $home_path ?: '';

            $relative_path = ltrim(str_replace($home_path, '', $path), '/');

            $web_root = dirname(ABSPATH);
            $file_path = $web_root.'/'.$relative_path;

            if (!file_exists($file_path)) {
                return $url;
            }

            $info = pathinfo($file_path);
            $dir = dirname($path);
            $ext = empty($info['extension']) ? '' : '.'.$info['extension'];
            $name = basename($path, $ext);
            $version = filemtime($file_path);

            $new_path = sprintf('%s/%s.%s%s', $dir, $name, $version, $ext);

            return str_replace($path, $new_path, $url);
        }
    }

    if (!function_exists('wpapp_glob_case_insensitive_pattern')) {
        // https://stackoverflow.com/a/53797548/3929620
        function wpapp_glob_case_insensitive_pattern(array $extensions = []): string
        {
            $patterns = array_map(static fn ($ext): string => implode('', array_map(static fn (string $char): string => '['.strtoupper($char).strtolower($char).']', str_split($ext))), $extensions);

            return $patterns ? '*.{'.implode(',', $patterns).'}' : '*';
        }
    }

    if (!function_exists('wpapp_parse_shortcode_attributes')) {
        /**
         * Parse shortcode attributes string into array.
         * Enhanced version that handles more edge cases.
         */
        function wpapp_parse_shortcode_attributes(string $atts_string): array
        {
            if (empty($atts_string)) {
                return [];
            }

            $atts = [];

            // Handle quoted values (single or double quotes) and unquoted values
            $pattern = '/(\w+)\s*=\s*(?:(["\'])(.*?)\2|([^\s]+))/';

            if (preg_match_all($pattern, $atts_string, $matches, PREG_SET_ORDER)) {
                foreach ($matches as $match) {
                    $key = $match[1];
                    // Value is either quoted (match[3]) or unquoted (match[4])
                    $value = !empty($match[3]) ? $match[3] : $match[4];
                    $atts[$key] = $value;
                }
            }

            return $atts;
        }
    }

    if (!function_exists('wpapp_build_view_path')) {
        /**
         * Build view path from shortcode attributes.
         */
        function wpapp_build_view_path(array $atts): ?string
        {
            $parts = [];

            if (!empty($atts['controller'])) {
                $parts[] = sanitize_file_name($atts['controller']);
            }

            if (!empty($atts['view'])) {
                $parts[] = sanitize_file_name($atts['view']);
            }

            return $parts ? implode('/', $parts) : null;
        }
    }

    if (!function_exists('wpapp_columns_filter')) {
        function wpapp_columns_filter(array $columns): array
        {
            if (!empty(Environment::get('WP_ADMIN_IDS'))) {
                $ids = explode(',', (string) Environment::get('WP_ADMIN_IDS'));
                $ids = array_map('intval', $ids);
            }

            if (!current_user_can('administrator') || (!empty($ids) && !in_array(get_current_user_id(), $ids, true))) {
                unset($columns['author'], $columns['comments'], $columns['wpseo-score'], $columns['wpseo-title'], $columns['wpseo-metadesc'], $columns['wpseo-focuskw'], $columns['wpseo-links'], $columns['wpseo-linked']);

                // unset($columns['categories']);
                // unset($columns['tags']);
                // unset($columns['posts']);
            }

            return $columns;
        }
    }
};
