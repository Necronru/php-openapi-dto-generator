<?php


namespace Necronru\DtoGenerator\Tests\Provider;


use Necronru\DtoGenerator\ClassDefinition;
use Necronru\DtoGenerator\Provider\OpenApiV3;
use PHPUnit\Framework\TestCase;

class OpenApiV3Test extends TestCase
{

    public function testProviderGenerateProperty(): void
    {
        $spec = [
            'components' => [
                'schemas' => [
                    'Test' => [
                        'required' => [
                            'id',
                            'type',
                        ],
                        'properties' => [
                            'id' => [
                                'type' => 'integer',
                                'title' => 'ID',
                                'nullable' => true,
                                'default' => 1,
                                'readOnly' => true,
                                'example' => '125'
                            ],

                            'type' => [
                                'type' => 'string',
                                'deprecated' => true
                            ],

                            'boolean' => [
                                'type' => 'boolean',
                                'writeOnly' => true
                            ],

                            'number' => [
                                'type' => 'number',
                                'readOnly' => true,
                                'writeOnly' => true
                            ]
                        ],
                    ],
                ],
            ],
        ];

        $provider = new OpenApiV3($spec);
        $classes = $provider();

        $this->assertArrayHasKey('Test', $classes);
        $this->assertInstanceOf(ClassDefinition::class, $classes['Test']);

        $definition = $classes['Test'];

        $this->assertEquals('Test', $definition->getClass()->getName());

        $properties = $definition->getClass()->getProperties();

        $this->assertArrayHasKey('id', $properties);
        $this->assertRegExp('/ID/', $properties['id']->getComment());
        $this->assertRegExp('/\@var int\|null/', $properties['id']->getComment());
        $this->assertEquals(1, $properties['id']->getValue());

        $this->assertArrayHasKey('type', $properties);
        $this->assertRegExp('/\@var string/', $properties['type']->getComment());
        $this->assertRegExp('/\@deprecated/', $properties['type']->getComment());

        $this->assertArrayHasKey('boolean', $properties);
        $this->assertRegExp('/\@var bool/', $properties['boolean']->getComment());

        $this->assertArrayHasKey('number', $properties);
        $this->assertRegExp('/\@var float/', $properties['number']->getComment());

        echo $definition->getClass();
    }

}