<?php

/**
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace spec\loophp\collection\Iterator;

use ArrayIterator;
use loophp\collection\Iterator\ArrayCacheIterator;
use PhpSpec\Exception\Example\MatcherException;
use PhpSpec\ObjectBehavior;

class ArrayCacheIteratorSpec extends ObjectBehavior
{
    public function it_can_cache_an_iterator_of_type_generator()
    {
        $generator = static function () {
            yield 'a';

            yield 'b';

            yield 'c';

            yield 'd';

            yield 'e';
        };

        $this->beConstructedWith($generator());

        $this
            ->valid()
            ->shouldReturn(true);

        if (5 !== iterator_count($this->getWrappedObject())) {
            throw new MatcherException('The count is invalid.');
        }

        $this
            ->shouldIterateAs(
                range('a', 'e')
            );
    }

    public function it_is_initializable()
    {
        $this->beConstructedWith(new ArrayIterator([]));

        $this->shouldHaveType(ArrayCacheIterator::class);
    }
}
