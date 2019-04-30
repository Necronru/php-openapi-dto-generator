<?php

declare(strict_types=1);

namespace Necronru\DtoGenerator;


use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\Property;
use Symfony\Component\Inflector\Inflector;

class PropertyDefinition
{
    /**
     * @var Property
     */
    protected $property;

    /**
     * @var ClassType
     */
    protected $classType;

    public function __construct(ClassType $classType, Property $property)
    {
        $this->property = $property;
        $this->classType = $classType;
    }

    public function addComment(string $comment): void
    {
        $this->property->addComment($comment);
    }

    public function getName(): string
    {
        return $this->property->getName();
    }

    public function setValue($value): void
    {
        $this->property->setValue($value);
    }

    public function addMutators(string $type): void
    {
        $propertyName = $this->getName();
        $singulars = (array) Inflector::singularize($propertyName);

        $adderBody = <<<ADDERBODY
if (!in_array(\${$singulars[0]}, \$this->{$propertyName}, true)) {
    \$this->{$propertyName} = \${$singulars[0]};
}
        
return \$this;
ADDERBODY;

        $removerBody = <<<ADDERBODY
if (false !== \$index = array_search(\${$singulars[0]}, \$this->{$propertyName}, true)) {
    unset(\$this->{$propertyName}[\$index]);
}
        
return \$this;
ADDERBODY;


        $this->classType->addMethod(sprintf('add%s', ucfirst($singulars[0])))
            ->addComment(sprintf("@var %s \$%s\n", $type, $singulars[0]))
            ->addComment(sprintf("@return %s\n", $this->classType->getName()))
            ->setReturnType($this->classType->getName())
            ->setBody($adderBody)
            ->addParameter($singulars[0])->setTypeHint($type);

        $this->classType->addMethod(sprintf('remove%s', ucfirst($singulars[0])))
            ->addComment(sprintf("@var %s \$%s\n", $type, $singulars[0]))
            ->addComment(sprintf("@return %s\n", $this->classType->getName()))
            ->setReturnType($this->classType->getName())
            ->setBody($removerBody)
            ->addParameter($singulars[0])->setTypeHint($type);
    }

    public function addValidConstraint(): void
    {
        $this->addComment("@Symfony\Component\Validator\Constraints\Valid()");
    }

    public function addSetter(string $type = null): void
    {
        $setterBody = <<<SETTERBODY
\$this->{$this->getName()} = \${$this->getName()};
        
return \$this;
SETTERBODY;

        $this->classType
            ->addMethod('set' . ucfirst($this->getName()))
            ->addComment(sprintf("@var %s \$%s\n", $type, $this->getName()))
            ->addComment(sprintf("@return %s\n", $this->classType->getName()))
            ->setReturnType($this->classType->getName())
            ->setBody($setterBody)
            ->addParameter($this->getName())->setTypeHint($type)
        ;
    }

    public function addGetter(string $type = null, bool $nullable = false): void
    {
        $getterBody = <<<SETTERBODY
return \$this->{$this->getName()};
SETTERBODY;

        $this->classType
            ->addMethod('get' . ucfirst($this->getName()))
            ->addComment(sprintf("@return %s\n", $type))
//            ->setReturnType($type) // todo
            ->setBody($getterBody)
        ;
    }
}