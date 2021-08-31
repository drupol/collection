<?php

/**
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace loophp\collection\Operation;

use Closure;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Collections\Expr\ClosureExpressionVisitor;
use Iterator;
use loophp\collection\Contract\Operation;
use loophp\collection\Contract\Operation\Sortable;

/**
 * @immutable
 *
 * @template TKey
 * @template T
 *
 * phpcs:disable Generic.Files.LineLength.TooLong
 */
final class Matching implements Operation
{
    /**
     * @pure
     *
     * @return Closure(Criteria): Closure(Iterator<TKey, T>): Iterator<TKey, T>
     */
    public function __invoke(): Closure
    {
        return
            /**
             * @return Closure(Iterator<TKey, T>): Iterator<TKey, T>
             */
            static function (Criteria $criteria): Closure {
                $expr = $criteria->getWhereExpression();
                $pipes = [];

                if (null !== $expr) {
                    $pipes[] = (new Filter())((new ClosureExpressionVisitor())->dispatch($expr));
                }

                $orderings = $criteria->getOrderings();

                if ([] !== $orderings) {
                    $next = null;

                    foreach (array_reverse($orderings) as $field => $ordering) {
                        $next = ClosureExpressionVisitor::sortByField($field, Criteria::DESC === $ordering ? -1 : 1, $next);
                    }

                    $pipes[] = (new Sort())(Sortable::BY_VALUES)($next);
                }

                $offset = $criteria->getFirstResult();
                $length = $criteria->getMaxResults();

                if (null !== $offset || null !== $length) {
                    $pipes[] = (new Limit())((int) $length)((int) $offset);
                }

                return Pipe::ofVariadic(...$pipes);
            };
    }
}
