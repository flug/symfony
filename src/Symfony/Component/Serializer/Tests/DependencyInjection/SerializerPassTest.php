<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Serializer\Tests\DependencyInjection;

use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\Serializer\Context\ContextInterface;
use Symfony\Component\Serializer\Context\DefaultContext;
use Symfony\Component\Serializer\DependencyInjection\SerializerPass;
use Symfony\Component\Serializer\Normalizer\JsonSerializableNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

/**
 * Tests for the SerializerPass class.
 *
 * @author Javier Lopez <f12loalf@gmail.com>
 */
class SerializerPassTest extends TestCase
{
    public function testThrowExceptionWhenNoNormalizers()
    {
        $this->expectException('RuntimeException');
        $this->expectExceptionMessage('You must tag at least one service as "serializer.normalizer" to use the "serializer" service');
        $container = new ContainerBuilder();
        $container->register('serializer');

        $serializerPass = new SerializerPass();
        $serializerPass->process($container);
    }

    public function testThrowExceptionWhenNoEncoders()
    {
        $this->expectException('RuntimeException');
        $this->expectExceptionMessage('You must tag at least one service as "serializer.encoder" to use the "serializer" service');
        $container = new ContainerBuilder();
        $container->register('serializer')
            ->addArgument([])
            ->addArgument([]);
        $container->register('normalizer')->addTag('serializer.normalizer');

        $serializerPass = new SerializerPass();
        $serializerPass->process($container);
    }

    public function testServicesAreOrderedAccordingToPriority()
    {
        $container = new ContainerBuilder();

        $definition = $container->register('serializer')->setArguments([null, null]);
        $container->register('n2')->addTag('serializer.normalizer', ['priority' => 100])->addTag('serializer.encoder', ['priority' => 100]);
        $container->register('n1')->addTag('serializer.normalizer', ['priority' => 200])->addTag('serializer.encoder', ['priority' => 200]);
        $container->register('n3')->addTag('serializer.normalizer')->addTag('serializer.encoder');

        $serializerPass = new SerializerPass();
        $serializerPass->process($container);

        $expected = [
            new Reference('n1'),
            new Reference('n2'),
            new Reference('n3'),
        ];
        $this->assertEquals($expected, $definition->getArgument(0));
        $this->assertEquals($expected, $definition->getArgument(1));
    }
    public function testServiceHasDefaultContextInterface()
    {
        $container = new ContainerBuilder();
        $container->register('serializer_context', DefaultContext::class)->addArgument(['enable_max_depth' => true])->addTag(ContextInterface::class);
        $definition = $container->register('serializer')->setClass(ObjectNormalizer::class)->setArguments([null, null, null, null, null, null, $container->get('serializer_context')])->addTag('serializer.normalizer')->addTag('serializer.encoder');
        $definition->setAutowired(true);
        $serializerPass = new SerializerPass();
        $serializerPass->process($container);
        $this->assertInstanceOf(ContextInterface::class, $definition->getArgument(6));
    }
    public function testServiceHasDefaultNonContextInterface()
    {
        $container = new ContainerBuilder();
        $container->register('serializer_context', DefaultContext::class)->addArgument(['enable_max_depth' => true])->addTag(ContextInterface::class);
        $definition = $container->register('serializer')->setClass(JsonSerializableNormalizer::class)->setArguments([null, null,  ['enable_max_depth' => true] ])->addTag('serializer.normalizer')->addTag('serializer.encoder');
        $definition->setAutowired(true);
        $serializerPass = new SerializerPass();
        $serializerPass->process($container);
        
        $this->assertEquals(['enable_max_depth' => true], $definition->getArgument(2));
    }
}
