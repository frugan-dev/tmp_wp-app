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
use WpApp\Dependencies\Inpsyde\Wonolog\Channels;
use WpSpaghetti\WpLogger\Logger;

if (!defined('WPINC')) {
    exit;
}

return [
    [
        'callback' => static function (Container $container, string $user_login, WP_User $wpUser): void {
            $context = [
                'channel' => Channels::SECURITY,
                'user_id' => $wpUser->ID,
                'user_login' => $user_login,
                'user_email' => $wpUser->user_email,
                'user_roles' => $wpUser->roles,
            ];

            $container->get(Logger::class)->info(
                'User "{user_login}" (ID: {user_id}) logged in successfully',
                $context
            );
        },
        'accepted_args' => 2,
    ],
];
