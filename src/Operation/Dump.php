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
use Symfony\Component\VarDumper\VarDumper;

/**
 * @immutable
 *
 * @template TKey
 * @template T
 *
 * phpcs:disable Generic.Files.LineLength.TooLong
 */
final class Dump implements Operation
{
    /**
     * @pure
     *
     * @return Closure(string): Closure(int): Closure(?Closure): Closure(Iterator<TKey, T>): Iterator<TKey, T>
     */
    public function __invoke(): Closure
    {
        return
            /**
             * @return Closure(int): Closure(?Closure): Closure(Iterator<TKey, T>): Iterator<TKey, T>
             */
            static fn (string $name = ''): Closure =>
                /**
                 * @return Closure(?Closure): Closure(Iterator<TKey, T>): Iterator<TKey, T>
                 */
                static fn (int $size = -1): Closure =>
                    /**
                     * @return Closure(Iterator<TKey, T>): Iterator<TKey, T>
                     */
                    static fn (?Closure $callback = null): Closure =>
                        /**
                         * @return Iterator<TKey, T>
                         */
                        static function (Iterator $iterator) use ($name, $size, $callback): Iterator {
                            $j = 0;

                            /** @var callable $debugFunction */
                            $debugFunction = class_exists(VarDumper::class) ? 'dump' : 'var_dump';

                            $callback ??=
                                /**
                                 * @param TKey $key
                                 * @param T $value
                                 *
                                 * @return mixed
                                 */
                                static fn (string $name, $key, $value) => $debugFunction(['name' => $name, 'key' => $key, 'value' => $value]);

                            foreach ($iterator as $key => $value) {
                                yield $key => $value;

                                if (-1 === $size) {
                                    continue;
                                }

                                if ($j++ < $size || 0 === $size) {
                                    $callback($name, $key, $value);

                                    continue;
                                }

                                $size = -1;
                            }
                        };
    }
}
