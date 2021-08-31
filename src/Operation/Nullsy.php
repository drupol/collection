<?php

/**
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace loophp\collection\Operation;

use Closure;
use Iterator;
use loophp\collection\Contract\Operation;

use function in_array;

/**
 * @immutable
 *
 * @template TKey
 * @template T
 *
 * phpcs:disable Generic.Files.LineLength.TooLong
 */
final class Nullsy implements Operation
{
    /**
     * @var list<null, array, int, bool, string>
     */
    public const VALUES = [null, [], 0, false, ''];

    /**
     * @pure
     *
     * @return Closure(Iterator<TKey, T>): Iterator<int, bool>
     */
    public function __invoke(): Closure
    {
        $matchWhenNot = static fn (): bool => false;
        $matcher =
            /**
             * @param T $value
             */
            static fn ($value): bool => in_array($value, self::VALUES, true);

        return Pipe::ofTyped2(
            (new MatchOne())($matchWhenNot)($matcher),
            (new Map())(
                /**
                 * @param T $value
                 */
                static fn ($value): bool => !$value
            ),
        );
    }
}
