<?php
/**
 *
 * This file is part of the Aura project for PHP.
 *
 * @package Aura.Filter
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 *
 */
namespace Aura\Filter\Rule\Sanitize;

/**
 *
 * Validates that a value is within a given range.
 *
 * @package Aura.Filter
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 *
 */
class Between
{
    /**
     *
     * If the value is < min , will set the min value,
     * and if value is greater than max, set the max value
     *
     * @param mixed $min The minimum valid value.
     *
     * @param mixed $max The maximum valid value.
     *
     * @return bool True if the value was sanitized, false if not.
     *
     */
    public function __invoke($object, $field, $min, $max)
    {
        $value = $object->$field;
        if (! is_scalar($value)) {
            return false;
        }
        if ($value < $min) {
            $object->$field = $min;
        }
        if ($value > $max) {
            $object->$field = $max;
        }
        return true;
    }
}
