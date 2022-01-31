<?php

/**
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace loophp\collection\Operation;

use Closure;
use Generator;
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
final class Split extends AbstractOperation
{
    /**
     * @pure
     *
     * @return Closure(int): Closure((callable(T, TKey): bool)...): Closure(iterable<TKey, T>): Generator<int, list<T>>
     */
    public function __invoke(): Closure
    {
        return
            /**
             * @return Closure((callable(T, TKey): bool)...): Closure(iterable<TKey, T>): Generator<int, list<T>>
             */
            static fn (int $type = Splitable::BEFORE): Closure =>
                /**
                 * @param callable(T, TKey): bool ...$callbacks
                 *
                 * @return Closure(iterable<TKey, T>): Generator<int, list<T>>
                 */
                static fn (callable ...$callbacks): Closure =>
                    /**
                     * @param iterable<TKey, T> $iterable
                     *
                     * @return Generator<int, list<T>>
                     */
                    static function (iterable $iterable) use ($type, $callbacks): Generator {
                        $carry = [];

                        foreach ($iterable as $key => $current) {
                            $callbackReturn = CallbacksArrayReducer::or()($callbacks, $current, $key, $iterable);

                            if (Splitable::AFTER === $type) {
                                $carry[] = $current;
                            }

                            if ($callbackReturn && (Splitable::REMOVE === $type)) {
                                yield $carry;

                                $carry = [];

                                continue;
                            }

                            if ($callbackReturn && ([] !== $carry)) {
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
