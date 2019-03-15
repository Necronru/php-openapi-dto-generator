<?php

declare(strict_types=1);

namespace Necronru\DtoGenerator;


use Nette\PhpGenerator\ClassType;

class ClassDefinition
{
    /**
     * @var ClassType
     */
    protected $class;

    /**
     * @var PropertyDefinition[]
     */
    protected $propertyDefinitions = [];

    public function __construct(ClassType $class)
    {
        $this->class = $class;
    }

    /**
     * @return ClassType
     */
    public function getClass(): ClassType
    {
        return $this->class;
    }

    public function setFinal(): void
    {
        $this->getClass()->setFinal();
    }

    public function addComment(string $comment): void
    {
        $this->getClass()->addComment($comment);
    }

    public function addPublicProperty($propertyName): PropertyDefinition
    {
        $property = $this->getClass()
            ->addProperty($propertyName)
            ->setVisibility('public')
        ;

        $this->propertyDefinitions[$propertyName] = new PropertyDefinition($this->getClass(), $property);

        return $this->propertyDefinitions[$propertyName];
    }

    public function addImplement(string $name): void
    {
        $this->getClass()->addImplement($name);
    }
}