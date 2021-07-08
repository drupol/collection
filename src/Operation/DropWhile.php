<?php

/**
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace loophp\collection\Operation;

use Closure;
use Generator;
use Iterator;
use loophp\collection\Utils\OrCallbackReducer;

/**
 * @immutable
 *
 * @template TKey
 * @template T
 *
 * phpcs:disable Generic.Files.LineLength.TooLong
 */
final class DropWhile extends AbstractOperation
{
    /**
     * @pure
     *
     * @return Closure(callable(T, TKey): bool ...): Closure (Iterator<TKey, T>): Generator<TKey, T>
     */
    public function __invoke(): Closure
    {
        return
            /**
             * @param callable(T, TKey):bool ...$callbacks
             *
             * @return Closure(Iterator<TKey, T>): Generator<TKey, T>
             */
            static fn (callable ...$callbacks): Closure =>
            /**
             * @param Iterator<TKey, T> $iterator
             *
             * @return Generator<TKey, T>
             */
            static function (Iterator $iterator) use ($callbacks): Generator {
                $result = true;

                foreach ($iterator as $key => $current) {
                    if (true === $result) {
                        $result = OrCallbackReducer::or()($callbacks, $current, $key, $iterator);
                    }

                    if (true === $result) {
                        continue;
                    }

                    yield $key => $current;
                }
            };
    }
}
