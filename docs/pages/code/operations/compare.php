<?php

/**
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace App;

use loophp\collection\Collection;
use stdClass;

use const INF;

include __DIR__ . '/../../../../vendor/autoload.php';

$callback = static fn (stdClass $left, stdClass $right): stdClass => $left->age > $right->age
    ? $left
    : $right;

$result = Collection::fromIterable([(object) ['id' => 2, 'age' => 5], (object) ['id' => 1, 'age' => 10]])
    ->compare($callback); // (object) ['id' => 1, 'age' => 10]

$result = Collection::empty()
    ->compare($callback, -INF); // -INF
