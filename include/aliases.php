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

use Psr\Log\InvalidArgumentException;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

/*
 * This file is part of the Wp-App WordPress plugin.
 *
 * (ɔ) Frugan <dev@frugan.it>
 *
 * This source file is subject to the GNU GPLv3 or later license that is bundled
 * with this source code in the file LICENSE.
 */

// FIXED - Commented out because they interfere with other versions of Monolog installed (e.g. Acorn).
// These aliases bridge Mozart-prefixed PSR Log classes to standard PSR namespace.
// if (!interface_exists(LoggerInterface::class) && interface_exists(WpApp\Vendor\Psr\Log\LoggerInterface::class)) {
//     class_alias(WpApp\Vendor\Psr\Log\LoggerInterface::class, LoggerInterface::class);
//     class_alias(WpApp\Vendor\Psr\Log\LogLevel::class, LogLevel::class);
//     class_alias(WpApp\Vendor\Psr\Log\InvalidArgumentException::class, InvalidArgumentException::class);
// }
