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
use loophp\collection\Contract\Operation\Splitable;
use loophp\collection\Utils\CallbacksArrayReducer;

/**
 * @immutable
 *
 * @template TKey
 * @template T
 *
 * phpcs:disable Generic.Files.LineLength.TooLong
 */
final class Split implements Operation
{
    /**
     * @pure
     *
     * @return Closure((callable(T, TKey): bool)...): Closure(Iterator<TKey, T>): Iterator<int, list<T>>
     */
    public function __invoke(int $type = Splitable::BEFORE): Closure
    {
        return
            /**
             * @param callable(T, TKey): bool ...$callbacks
             *
             * @return Closure(Iterator<TKey, T>): Iterator<int, list<T>>
             */
            static fn (callable ...$callbacks): Closure =>
                /**
                 * @param Iterator<TKey, T> $iterator
                 *
                 * @return Iterator<int, list<T>>
                 */
                static function (Iterator $iterator) use ($type, $callbacks): Iterator {
                    $carry = [];

                    foreach ($iterator as $key => $current) {
                        $callbackReturn = CallbacksArrayReducer::or()($callbacks, $current, $key, $iterator);

                        if (Splitable::AFTER === $type) {
                            $carry[] = $current;
                        }

                        if (Splitable::REMOVE === $type && true === $callbackReturn) {
                            yield $carry;

                            $carry = [];

                            continue;
                        }

                        if (true === $callbackReturn && [] !== $carry) {
                            yield $carry;

                            $carry = [];
                        }

                        if (Splitable::AFTER !== $type) {
                            $carry[] = $current;
                        }
                    }

                    if ([] !== $carry) {
                        yield $carry;
                    }
                };
    }
}
