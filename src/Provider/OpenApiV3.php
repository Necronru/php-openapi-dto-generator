<?php

declare(strict_types=1);

namespace Necronru\DtoGenerator\Provider;

use Necronru\DtoGenerator\ClassDefinition;
use Necronru\DtoGenerator\PropertyDefinition;
use Nette\PhpGenerator\ClassType;

class OpenApiV3
{
    /**
     * @var array
     */
    protected static $simpleTypes = [
        'null',
        'int', 'bool', 'string', 'float',
        'mixed', 'void',
        'array', 'object',
        'resource',
    ];

    protected $version = null;

    /**
     * @var ClassType[]
     */
    protected $classes = [];

    /**
     * @var ClassDefinition[]
     */
    protected $definitions = [];

    /**
     * @var array
     */
    protected $spec;
    /**
     * @var string
     */
    protected $classPostfix;

    public function __construct($spec, string $classPostfix = '')
    {
        $this->spec = $spec;
        $this->classPostfix = $classPostfix;
    }

    /**
     * @return ClassDefinition[]
     */
    public function __invoke()
    {
        $spec = $this->spec;
        $this->version = $spec['info']['version'];

        foreach ($spec['components']['schemas'] as $className => $schema) {

            $definition = $this->getDefinition($className) ?? $this->createDefinition($className);

            foreach ($schema['properties'] as $propertyName => $propertySchema) {
                $propertyDefinition = $definition->addPublicProperty($propertyName);

                $this->addTitle($propertySchema, $propertyDefinition);
                $this->addDescription($propertySchema, $propertyDefinition);

                [$propertyType, $nullable] = $this->getPropertyType(
                    $definition->getClass(),
                    $propertySchema,
                    $propertyDefinition
                );

                $propertyDefinition->addComment(sprintf("@var %s\n", $this->convertToPHPType($propertyType, $nullable)));

                if (array_key_exists('required', $schema) && in_array($propertyName, (array)$schema['required'], true)) {
                    $this->addNotNullConstraint($propertyDefinition);
                }

                if (array_key_exists('enum', $propertySchema)) {
                    $this->addEnumConstraint($propertyDefinition, $propertySchema['enum']);
                }

                $this->addExternalDocs($propertySchema, $propertyDefinition);
            }
        }

        return $this->definitions;
    }

    private function registerDefinition(ClassDefinition $definition): void
    {
        $this->definitions[$definition->getClass()->getName()] = $definition;
    }

    private function getPropertyType(ClassType $class, array $propertySchema, PropertyDefinition $propertyDefinition): array
    {
        $propertyType = 'mixed';
        $nullable = false;

        if (array_key_exists('type', $propertySchema)) {
            $propertyType = $propertySchema['type'];
            $nullable = isset($propertySchema['nullable']) && (bool) $propertySchema['nullable'];
            $isCollection = false;

            if (array_key_exists('items', $propertySchema) && $propertySchema['type'] === 'array') {
                $isCollection = true;
                $types = [];

                foreach ($propertySchema['items'] as $item) {
                    $type = $item;

                    if (preg_match("/^(#\/components\/schemas\/)(.*)$/", $item)) {
                        $type = preg_replace('/^(#\/components\/schemas\/)(.*)$/', '$2', $item);
                        $propertyDefinition->addMutators($type);
                    }

                    $types[] = sprintf('%s[]', $this->convertToPHPType($type, $nullable));
                    $propertyDefinition->addValidConstraint();
                }

                $propertyType = implode('|', $types);
            }

            if (array_key_exists('discriminator', $propertySchema)) {

                $types = [];

                foreach ($propertySchema['oneOf'] as $item) {
                    $types[] = preg_replace('/^(#\/components\/schemas\/)(.*)$/', '$2', $item['$ref']);
                }

                $interface = $this->generateDiscriminatorInterface(
                    $class->getName() . ucfirst($propertyDefinition->getName()),
                    $propertySchema['discriminator']['propertyName'],
                    $propertySchema['discriminator']['mapping']
                );


                foreach ($types as $type) {
                    ($this->getDefinition($type) ?? $this->createDefinition($type))
                        ->addImplement($interface->getClass()->getName())
                    ;
                }

                $propertyDefinition->addSetter($interface->getClass()->getName());

                $propertyType = implode('|', array_merge([$interface->getClass()->getName()], $types));
            }

            // todo: move me
            if ($isCollection && !$nullable) {
                $propertyDefinition->setValue([]);
            }

//            if ($nullable && !$isCollection) {
//                 todo: set default value
//            }
        }


        if (array_key_exists('$ref', $propertySchema)) {
            $propertyType = preg_replace('/^(#\/components\/schemas\/)(.*)$/', '$2', $propertySchema['$ref']);
        }

        return [$propertyType, $nullable];
    }

    private function addTitle($propertyDefinition, PropertyDefinition $definition): void
    {
        if (array_key_exists('title', $propertyDefinition)) {
            $definition->addComment($propertyDefinition['title'] . "\n");
        }
    }

    private function addDescription($propertyDefinition, PropertyDefinition $property): void
    {
        if (array_key_exists('description', $propertyDefinition)) {
            $property->addComment($propertyDefinition['description'] . "\n");
        }
    }

    private function addExternalDocs($propertyDefinition, PropertyDefinition $property): void
    {
        if (isset($propertyDefinition['externalDocs']['url'])) {
            $property->addComment(sprintf("@link %s\n", $propertyDefinition['externalDocs']['url']));
        }
    }

    private function addNotNullConstraint(PropertyDefinition $property): void
    {
        $property->addComment("@Symfony\Component\Validator\Constraints\NotNull()");
    }

    private function addEnumConstraint(PropertyDefinition $definition, array $enum): void
    {
        $choices = implode(array_map(function($value) {
            return sprintf('"%s"', $value);
        }, $enum), ',');

        $definition->addComment(
            sprintf("@Symfony\Component\Validator\Constraints\Choice(choices={%s})", $choices)
        );
    }

    private function getDefinition(string $className): ?ClassDefinition
    {
        return $this->hasDefinition($className) ? $this->definitions[$className] : null;
    }

    private function hasDefinition(string $className): bool
    {
        return array_key_exists($className, $this->definitions);
    }

    private function createDefinition(string $className, bool $interface = false): ClassDefinition
    {
        $classPostfix = $interface ? $this->classPostfix.'Interface' : $this->classPostfix;

        $definition = new ClassDefinition(new ClassType(sprintf('%s%s', $className, $classPostfix)));

        if ($interface) {
            $definition->getClass()->setType(ClassType::TYPE_INTERFACE);
        }

        if (!$interface) {
            $definition->setFinal();
        }

        $definition->addComment("This is autogenerated object, please not't touch it.\n");

        if ($this->version) {
            $definition->addComment(sprintf("@version %s\n", $this->version));
        }

        $this->registerDefinition($definition);

        return $definition;
    }

    private function generateDiscriminatorInterface(string $interfaceName, string $discriminatorProperty, array $mapping): ClassDefinition
    {
        $definition = $this->createDefinition($interfaceName, true);

        foreach($mapping as $value => $ref) {
            $mapping[$value] = sprintf('    "%s" = %s::class', $value, preg_replace('/^(#\/components\/schemas\/)(.*)$/', '$2', $ref));
        }

        $definition->addComment(
            sprintf(
                "@Symfony\\Component\\Serializer\\Annotation\\DiscriminatorMap(typeProperty=\"%s\", mapping={\n%s\n})",
                $discriminatorProperty,
                implode(",\n", $mapping)
            )
        );

        return $definition;
    }

    private function convertToPHPType($type, bool $nullable): string
    {
        switch ($type) {
            case 'boolean':
                $type = 'bool';
                break;

            case 'integer':
                $type = 'int';
                break;

            case 'number':
                $type = 'float';
                break;
        }

        return !$nullable ? $type : $type . '|null';
    }
}