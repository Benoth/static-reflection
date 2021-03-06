<?php

namespace Benoth\StaticReflection\Reflection\Parts;

use Benoth\StaticReflection\Reflection\ReflectionClass;
use Benoth\StaticReflection\Reflection\ReflectionConstant;

trait ConstantTrait
{
    /**
     * @type ReflectionConstant[]
     */
    protected $constants = [];

    /**
     * Gets the interfaces, without inherited ones.
     *
     * Must be implemented by classes using this trait
     *
     * @return ReflectionInterface[]
     */
    abstract public function getSelfInterfaces();

    /**
     * Gets the filename of the file in which the class has been defined.
     *
     * Must be implemented by classes using this trait.
     *
     * @return string
     */
    abstract public function getFileName();

    /**
     * Gets defined constants from an entity, including inherited ones.
     *
     * @return ReflectionConstant[]
     */
    public function getConstants()
    {
        $constants = [];

        foreach ($this->getSelfInterfaces() as $interfaceName => $interface) {
            foreach ($interface->getConstants() as $constantName => $constantValue) {
                $constants[$constantName] = $constantValue;
            }
        }

        if ($this instanceof ReflectionClass && ($this->getParentClass() instanceof ReflectionClass || $this->getParentClass() instanceof \ReflectionClass)) {
            foreach ($this->getParentClass()->getConstants() as $constantName => $constantValue) {
                $constants[$constantName] = $constantValue;
            }
        }

        $constants = array_merge($constants, $this->getSelfConstants());

        return $constants;
    }

    /**
     * Gets defined constants from an entity, without inherited ones.
     *
     * @return ReflectionConstant[]
     */
    public function getSelfConstants()
    {
        $constants = [];
        foreach ($this->constants as $constant) {
            $constants[$constant->getName()] = $constant->getValue();
        }

        return $constants;
    }

    /**
     * Gets defined constant.
     *
     * @param string $constantSearchedName Name of the constant
     *
     * @see http://php.net/manual/en/reflectionclass.getconstant.php#113642 Why returning false if the constant does no exists ?
     *      It could be the value of the constant... Should throw a \ReflectionException ?
     *
     * @return mixed Value of the constant, false if constant not found
     */
    public function getConstant($constantSearchedName)
    {
        foreach ($this->getConstants() as $constantName => $constantValue) {
            if ($constantName === $constantSearchedName) {
                return $constantValue;
            }
        }

        return false;
    }

    /**
     * Checks if constant is defined.
     *
     * @param string $constantSearchedName Name of the constant
     *
     * @return bool
     */
    public function hasConstant($constantSearchedName)
    {
        foreach ($this->getConstants() as $constantName => $constantValue) {
            if ($constantName === $constantSearchedName) {
                return true;
            }
        }

        return false;
    }

    /**
     * Add a constant to an entity.
     *
     * @param ReflectionConstant $constant
     */
    public function addConstant(ReflectionConstant $constant)
    {
        $this->constants[$constant->getShortName()] = $constant;
        $constant->setDeclaringClassLike($this);
        $constant->setFilename($this->getFileName());

        return $this;
    }
}
