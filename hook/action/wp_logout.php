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
use WpApp\Vendor\Inpsyde\Wonolog\Channels;
use WpSpaghetti\WpLogger\Logger;

if (!defined('WPINC')) {
    exit;
}

return [
    [
        'callback' => static function (Container $container, int $user_id): void {
            $user = get_user_by('ID', $user_id);

            if (!$user) {
                $container->get(Logger::class)->warning(
                    'Logout attempt for non-existent user ID: {user_id}',
                    [
                        'channel' => Channels::SECURITY,
                        'user_id' => $user_id,
                    ]
                );

                return;
            }

            $context = [
                'channel' => Channels::SECURITY,
                'user_id' => $user->ID,
                'user_login' => $user->user_login,
                'user_email' => $user->user_email,
                'user_roles' => $user->roles,
            ];

            $container->get(Logger::class)->info(
                'User "{user_login}" (ID: {user_id}) logged out',
                $context
            );
        },
    ],
];
