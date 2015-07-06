<?php
/**
 *
 * This file is part of Aura for PHP.
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 *
 */
namespace Aura\Filter\Spec;

use Aura\Filter\Locator\Locator;
use Exception;

/**
 *
 * A generic rule specification.
 *
 * @package Aura.Filter
 *
 */
class Spec
{
    /**
     * Stop filtering on a field when a rule for that field fails.
     */
    const HARD_RULE = 'HARD_RULE';

    /**
     * Continue filtering on a field even when a rule for that field fails.
     */
    const SOFT_RULE = 'SOFT_RULE';

    /**
     * Stop filtering on all fields when a rule fails.
     */
    const STOP_RULE = 'STOP_RULE';

    /**
     *
     * The field name to be filtered.
     *
     * @var string
     *
     */
    protected $field;

    /**
     *
     * The rule name to be applied.
     *
     * @var string
     *
     */
    protected $rule;

    /**
     *
     * Arguments to pass to the rule.
     *
     * @var array
     *
     */
    protected $args = array();

    /**
     *
     * The message to use on failure.
     *
     * @var string
     *
     */
    protected $message;

    /**
     *
     * Allow the field to be blank?
     *
     * @var bool
     *
     */
    protected $allow_blank = false;

    /**
     *
     * The failure mode to use.
     *
     * @var string
     *
     */
    protected $failure_mode = self::HARD_RULE;

    /**
     *
     * The rule locator to use.
     *
     * @var Locator
     *
     */
    protected $locator;

    /**
     *
     * Constructor.
     *
     * @param Locator $locator The "sanitize" rules.
     *
     * @return self
     *
     */
    public function __construct(Locator $locator)
    {
        $this->locator = $locator;
    }

    /**
     *
     * Applies the rule specification to a subject.
     *
     * @param mixed $subject The filter subject.
     *
     * @return bool True on success, false on failure.
     *
     */
    public function __invoke($subject)
    {
        return $this->applyBlank($subject)
            || $this->applyRule($subject);
    }

    /**
     *
     * Sets the subject field name.
     *
     * @param string $field The subject field name.
     *
     * @return self
     *
     */
    public function field($field)
    {
        $this->field = $field;
        return $this;
    }

    /**
     *
     * Sets this specification as a "soft" rule.
     *
     * @param string $message The failure message.
     *
     * @return self
     *
     */
    public function asSoftRule($message = null)
    {
        return $this->setFailureMode(self::SOFT_RULE, $message);
    }

    /**
     *
     * Sets this specification as a "hard" rule.
     *
     * @param string $message The failure message.
     *
     * @return self
     *
     */
    public function asHardRule($message = null)
    {
        return $this->setFailureMode(self::HARD_RULE, $message);
    }

    /**
     *
     * Sets this specification as a "stop" rule.
     *
     * @param string $message The failure message.
     *
     * @return self
     *
     */
    public function asStopRule($message = null)
    {
        return $this->setFailureMode(self::STOP_RULE, $message);
    }

    /**
     *
     * Sets the failure mode for this rule specification.
     *
     * @param string $failure_mode The failure mode.
     *
     * @param string $message The failure message.
     *
     * @return self
     *
     */
    protected function setFailureMode($failure_mode, $message)
    {
        $this->failure_mode = $failure_mode;
        if ($message) {
            $this->setMessage($message);
        }
        return $this;
    }

    /**
     *
     * Sets the failure message for this rule specification.
     *
     * @param string $message The failure message.
     *
     * @return self
     *
     */
    public function setMessage($message)
    {
        $this->message = $message;
        return $this;
    }

    /**
     *
     * Returns the failure mode for this rule specification.
     *
     * @return string
     *
     */
    public function getFailureMode()
    {
        return $this->failure_mode;
    }

    public function isStopRule()
    {
        return $this->failure_mode === self::STOP_RULE;
    }

    public function isHardRule()
    {
        return $this->failure_mode === self::HARD_RULE;
    }

    public function isSoftRule()
    {
        return $this->failure_mode === self::SOFT_RULE;
    }

    /**
     *
     * Returns the field name for this rule specification.
     *
     * @return string
     *
     */
    public function getField()
    {
        return $this->field;
    }

    /**
     *
     * Returns the failure message for this rule specification.
     *
     * @return string
     *
     */
    public function getMessage()
    {
        if (! $this->message) {
            $this->message = $this->getDefaultMessage();
        }
        return $this->message;
    }

    /**
     *
     * Returns the arguments for this rule specification.
     *
     * @return array
     *
     */
    public function getArgs()
    {
        return $this->args;
    }

    /**
     *
     * Initializes this specification.
     *
     * @param array $args Arguments for the rule.
     *
     * @return self
     *
     */
    protected function init($args)
    {
        $this->args = $args;
        $this->rule = array_shift($this->args);
        return $this;
    }

    /**
     *
     * Returns the default failure message for this rule specification.
     *
     * @return string
     *
     */
    protected function getDefaultMessage()
    {
        $message = $this->rule;
        if ($this->args) {
            $message .= '(' . implode(', ', $this->args) . ')';
        }
        return $message;
    }

    /**
     *
     * Check if the field is allowed to be, and actually is, blank.
     *
     * @param mixed $subject The filter subject.
     *
     * @return bool
     *
     */
    protected function applyBlank($subject)
    {
        if (! $this->allow_blank) {
            return false;
        }

        // the field name
        $field = $this->field;

        // not set, or null, means it is blank
        if (! isset($subject->$field) || $subject->$field === null) {
            return true;
        }

        // non-strings are not blank: int, float, object, array, resource, etc
        if (! is_string($subject->$field)) {
            return false;
        }

        // strings that trim down to exactly nothing are blank
        return trim($subject->$field) === '';
    }

    /**
     *
     * Check if the subject field passes the rule specification.
     *
     * @param mixed $subject The filter subject.
     *
     * @return bool
     *
     */
    protected function applyRule($subject)
    {
        $rule = $this->locator->get($this->rule);
        $args = $this->args;
        array_unshift($args, $this->field);
        array_unshift($args, $subject);
        return call_user_func_array($rule, $args);
    }
}
