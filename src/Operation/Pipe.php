<?php

/**
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace loophp\collection\Operation;

use Closure;
use Iterator;

/**
 * @immutable
 *
 * @template TKey
 * @template T
 *
 * phpcs:disable Generic.Files.LineLength.TooLong
 */
final class Pipe extends AbstractOperation
{
    /**
     * @pure
     *
     * @return Closure(callable(Iterator<TKey, T>): Iterator<TKey, T> ...): Closure(Iterator<TKey, T>): Iterator<TKey, T>
     */
    public function __invoke(): Closure
    {
        return
            /**
             * @param callable(Iterator<TKey, T>): Iterator<TKey, T> ...$operations
             *
             * @return Closure(Iterator<TKey, T>): Iterator<TKey, T>
             */
            static fn (callable ...$operations): Closure => array_reduce(
                $operations,
                /**
                 * @param callable(Iterator<TKey, T>): Iterator<TKey, T> $f
                 * @param callable(Iterator<TKey, T>): Iterator<TKey, T> $g
                 *
                 * @return Closure(Iterator<TKey, T>): Iterator<TKey, T>
                 */
                static fn (callable $f, callable $g): Closure =>
                    /**
                     * @param Iterator<TKey, T> $iterator
                     *
                     * @return Iterator<TKey, T>
                     */
                    static fn (Iterator $iterator): Iterator => $g($f($iterator)),
                static fn (Iterator $iterator): Iterator => $iterator
            );
    }

    /**
     * @pure
     *
     * @template Ik
     * @template Iv
     * @template Jk
     * @template Jv
     * @template Kk
     * @template Kv
     *
     * @param callable(Iterator<Ik, Iv>): Iterator<Jk, Jv> $op1
     * @param callable(Iterator<Jk, Jv>): Iterator<Kk, Kv> $op2
     *
     * @return Closure(Iterator<Ik, Iv>): Iterator<Kk, Kv>
     */
    public static function ofTyped2(callable $op1, callable $op2): Closure
    {
        return
            /**
             * @param Iterator<Ik, Iv> $iterator
             *
             * @return Iterator<Kk, Kv>
             */
            static fn (Iterator $iterator): Iterator => $op2($op1($iterator));
    }

    /**
     * @pure
     *
     * @template Ik
     * @template Iv
     * @template Jk
     * @template Jv
     * @template Kk
     * @template Kv
     * @template Lk
     * @template Lv
     *
     * @param callable(Iterator<Ik, Iv>): Iterator<Jk, Jv> $op1
     * @param callable(Iterator<Jk, Jv>): Iterator<Kk, Kv> $op2
     * @param callable(Iterator<Kk, Kv>): Iterator<Lk, Lv> $op3
     *
     * @return Closure(Iterator<Ik, Iv>): Iterator<Lk, Lv>
     */
    public static function ofTyped3(callable $op1, callable $op2, callable $op3): Closure
    {
        return
            /**
             * @param Iterator<Ik, Iv> $iterator
             *
             * @return Iterator<Lk, Lv>
             */
            static fn (Iterator $iterator): Iterator => $op3($op2($op1($iterator)));
    }

    /**
     * @pure
     *
     * @template Ik
     * @template Iv
     * @template Jk
     * @template Jv
     * @template Kk
     * @template Kv
     * @template Lk
     * @template Lv
     * @template Mk
     * @template Mv
     *
     * @param callable(Iterator<Ik, Iv>): Iterator<Jk, Jv> $op1
     * @param callable(Iterator<Jk, Jv>): Iterator<Kk, Kv> $op2
     * @param callable(Iterator<Kk, Kv>): Iterator<Lk, Lv> $op3
     * @param callable(Iterator<Lk, Lv>): Iterator<Mk, Mv> $op4
     *
     * @return Closure(Iterator<Ik, Iv>): Iterator<Mk, Mv>
     */
    public static function ofTyped4(callable $op1, callable $op2, callable $op3, callable $op4): Closure
    {
        return
            /**
             * @param Iterator<Ik, Iv> $iterator
             *
             * @return Iterator<Mk, Mv>
             */
            static fn (Iterator $iterator): Iterator => $op4($op3($op2($op1($iterator))));
    }

    /**
     * @pure
     *
     * @template Ik
     * @template Iv
     * @template Jk
     * @template Jv
     * @template Kk
     * @template Kv
     * @template Lk
     * @template Lv
     * @template Mk
     * @template Mv
     * @template Nk
     * @template Nv
     *
     * @param callable(Iterator<Ik, Iv>): Iterator<Jk, Jv> $op1
     * @param callable(Iterator<Jk, Jv>): Iterator<Kk, Kv> $op2
     * @param callable(Iterator<Kk, Kv>): Iterator<Lk, Lv> $op3
     * @param callable(Iterator<Lk, Lv>): Iterator<Mk, Mv> $op4
     * @param callable(Iterator<Mk, Mv>): Iterator<Nk, Nv> $op5
     *
     * @return Closure(Iterator<Ik, Iv>): Iterator<Nk, Nv>
     */
    public static function ofTyped5(callable $op1, callable $op2, callable $op3, callable $op4, callable $op5): Closure
    {
        return
            /**
             * @param Iterator<Ik, Iv> $iterator
             *
             * @return Iterator<Nk, Nv>
             */
            static fn (Iterator $iterator): Iterator => $op5($op4($op3($op2($op1($iterator)))));
    }
}
