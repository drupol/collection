<?php

/**
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

include __DIR__ . '/../../../vendor/autoload.php';

use loophp\collection\Collection;

// The Collatz conjecture (https://en.wikipedia.org/wiki/Collatz_conjecture)
$collatz = static fn (int $value): array => 0 === $value % 2
    ? [$value / 2]
    : [$value * 3 + 1];

$collection = Collection::unfold($collatz, 10)
    ->unwrap()
    ->until(static fn (int $number): bool => 1 === $number);

print_r($c->all()); // [25, 76, 38, 19, 58, 29, 88, 44, 22, 11, 34, 17, 52, 26, 13, 40, 20, 10, 5, 16, 8, 4, 2, 1]
