<?php

namespace Symfony\Component\Serializer\Context;

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
class DefaultContext implements ContextInterface
{
    private $context;
    
    public function __construct(array $context)
    {
        $this->context = $context;
    }

    public function toArray(): array
    {
        return $this->context;
    }
}
