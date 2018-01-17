<?php

namespace ChrisKonnertz\RegEx;

use ChrisKonnertz\RegEx\Expressions\AbstractExpression;
use Closure;

/**
 * This is the RegEx base class. It is the API frontend of the RegEx library.
 * Call its add<Something>() methods to add partial expressions.
 * Finally call its toString() method to retrieve the complete regular expression as a string.
 */
class RegEx
{

    /**
     * The delimiter indicates the start and the end of a regular expression
     */
    const DELIMITER = '/';

    /**
     * Shortcut of the "insensitive" ("i") modifier.
     * If active, letters in the pattern match both upper and lower case letters.
     */
    const INSENSITIVE_MODIFIER_SHORTCUT = 'i';

    /**
     * Shortcut of the "multi line" ("m") modifier.
     * If active, treats the string being matched against as multiple lines.
     */
    const MULTI_LINE_MODIFIER_SHORTCUT = 'm';

    /**
     * Shortcut of the "single line" ("s") modifier.
     * If active, a dot metacharacter in the pattern matches all characters, including newlines.
     */
    const SINGLE_LINE_MODIFIER_SHORTCUT = 's';

    /**
     * Shortcut of the "extended" ("x") modifier.
     * If active, whitespace is permitted.
     * */
    const EXTENDED_MODIFIER_SHORTCUT = 'i';

    /**
     * Array with all available modifier shortcuts
     *
     * @see http://php.net/manual/en/reference.pcre.pattern.modifiers.php
     */
    const MODIFIER_SHORTCUTS = [
        self::INSENSITIVE_MODIFIER_SHORTCUT,
        self::MULTI_LINE_MODIFIER_SHORTCUT,
        self::SINGLE_LINE_MODIFIER_SHORTCUT,
        self::EXTENDED_MODIFIER_SHORTCUT
    ];

    /**
     * The current version number
     */
    const VERSION = '0.7.0';

    /**
     * The start of the regular expression (=prefix)
     *
     * @var string
     */
    protected $start = self::DELIMITER;

    /**
     * Array with all partial expressions
     *
     * @var AbstractExpression[]
     */
    protected $expressions = [];

    /**
     * The end of the regular expression (=suffix)
     *
     * @var string
     */
    protected $end = self::DELIMITER;

    /**
     * Array with the active modifier shortcuts.
     * Valid values are one of the values in this array: self::MODIFIER_SHORTCUTS
     *
     * @var string[]
     */
    protected $modifiers = [];
    
    /**
     * Add a partial expression to the overall regular expression and wrap it in an "and" expression.
     * This expression requires that all of it parts exist in the tested string.
     * TODO Add examples
     *
     * @param string|int|float|Closure|AbstractExpression $partialExpressions
     * @return self
     */
    public function addAnd(...$partialExpressions)
    {
        foreach ($partialExpressions as &$partialExpression) {
            if ($partialExpression instanceof Closure) {
                $partialExpression = $partialExpression($this);
            }
        }

        $wrapperExpression = new Expressions\AndEx(...$partialExpressions);
        $this->expressions[] = $wrapperExpression;

        return $this;
    }

    /**
     * Add at least two partial expressions to the overall regular expression and wrap it in an "or" expression.
     * This expression requires that one of it parts exists in the tested string.
     * TODO Add examples
     *
     * @param string|int|float|Closure|AbstractExpression $partialExpressions
     * @return self
     */
    public function addOr(...$partialExpressions)
    {
        foreach ($partialExpressions as &$partialExpression) {
            if ($partialExpression instanceof Closure) {
                $partialExpression = $partialExpression($this);
            }
        }

        $wrapperExpression = new Expressions\OrEx(...$partialExpressions);
        $this->expressions[] = $wrapperExpression;

        return $this;
    }

    /**
     * Add one ore more partial expressions to the overall regular expression and wrap them in an "optional" expression.
     * The parts of this expression may or may not exist in the tested string.
     * TODO Add examples
     *
     * @param string|int|float|Closure|AbstractExpression $partialExpressions
     * @return self
     */
    public function addOption(...$partialExpressions)
    {
        foreach ($partialExpressions as &$partialExpression) {
            if ($partialExpression instanceof Closure) {
                $partialExpression = $partialExpression($this);
            }
        }

        $wrapperExpression = new Expressions\OptionEx(...$partialExpressions);
        $this->expressions[] = $wrapperExpression;

        return $this;
    }

    /**
     * Add one ore more partial expressions to the overall regular expression and wrap them in a "capturing group" expression.
     * This expression will be added to the matches when the overall regular expression is tested.
     * If you add more than one part these parts are linked by "and".
     * TODO Add examples
     *
     * @param string|int|float|Closure|AbstractExpression $partialExpressions
     * @return self
     */
    public function addCapturingGroup(...$partialExpressions)
    {
        foreach ($partialExpressions as &$partialExpression) {
            if ($partialExpression instanceof Closure) {
                $partialExpression = $partialExpression($this);
            }
        }

        $wrapperExpression = new Expressions\CapturingGroupEx(...$partialExpressions);
        $this->expressions[] = $wrapperExpression;

        return $this;
    }

    /**
     * Add one ore more partial expressions to the overall regular expression and wrap them in a "raw" expression.
     * This expression will not quote its regular expression characters.
     * TODO Add examples
     *
     * @param string|int|float|Closure|AbstractExpression $partialExpressions
     * @return self
     */
    public function addRaw(...$partialExpressions)
    {
        foreach ($partialExpressions as &$partialExpression) {
            if ($partialExpression instanceof Closure) {
                $partialExpression = $partialExpression($this);
            }
        }

        $wrapperExpression = new Expressions\RawEx(...$partialExpressions);
        $this->expressions[] = $wrapperExpression;

        return $this;
    }

    /**
     * Activates or deactivates the "insensitive" ("i") modifier.
     * If active, letters in the pattern match both upper and lower case letters.
     *
     * @param bool $active
     * @return void
     */
    public function setInsensitiveModifier(bool $active = true)
    {
        $this->setModifier(self::INSENSITIVE_MODIFIER_SHORTCUT, $active);
    }

    /**
     * Activates or deactivates the "multi line" ("m") modifier.
     * If active, treats the string being matched against as multiple lines.
     *
     * @param bool $active
     * @return void
     */
    public function setMultiLineModifier(bool $active = true)
    {
        $this->setModifier(self::MULTI_LINE_MODIFIER_SHORTCUT, $active);
    }

    /**
     * Activates or deactivates the "single line" ("s") modifier.
     * If active, a dot metacharacter in the pattern matches all characters, including newlines.
     *
     * @param bool $active
     * @return void
     */
    public function setSingleLineModifier(bool $active = true)
    {
        $this->setModifier(self::SINGLE_LINE_MODIFIER_SHORTCUT, $active);
    }
    /**
     * Activates or deactivates the "extended" ("x") modifier.
     * If active, whitespace is permitted.
     *
     * @param bool $active
     * @return void
     */
    public function setExtendedModifier(bool $active = true)
    {
        $this->setModifier(self::EXTENDED_MODIFIER_SHORTCUT, $active);
    }

    /**
     * Activates or deactivates a modifier. I
     * The current state of the modifier does not matter, so for example you can
     * (pseudo-)deactivate a modifier before ever activating it.
     *
     * @see http://php.net/manual/en/reference.pcre.pattern.modifiers.php
     *
     * @param string $modifierShortcut The modifier shortcut, a single character -> self::MODIFIER_SHORTCUTS
     * @param bool   $active           Activate (true) or deactivate (false) the modifier
     * @return void
     */
    public function setModifier(string $modifierShortcut, bool $active = true)
    {
        if (! in_array($modifierShortcut, self::MODIFIER_SHORTCUTS)) {
            throw new \InvalidArgumentException(
                'Invalid modifier shortcut given, use one of these: '.implode(self::MODIFIER_SHORTCUTS, ', '));
        }

        $index = array_search($modifierShortcut, $this->modifiers);
        if ($index === false) {
            if ($active) {
                $this->modifiers[] = $modifierShortcut;
            }
        } else {
            if (! $active) {
                unset($this->modifiers[$index]);
            }
        }
    }

    /**
     * Tests a given subject (a string) against the regular expression.
     * Returns the matches.
     * Throws an exception when there occurs an error while testing.
     *
     * @param string $subject
     * @return array
     * @throws \Exception
     */
    public function test(string $subject) : array
    {
        $regEx = $this->toString();

        $matches = [];

        $result = preg_match($regEx, $subject, $matches);

        if ($result === false) {
            throw new \Exception('Error when executing PHP\'s preg_match() function');
        }

        return $matches;
    }

    /**
     * Call this method if you want to traverse it and all of it child expression,
     * no matter how deep they are nested in the tree. You only have to pass a closure,
     * you do not have to pass an argument for the level parameter.
     * The callback will have three arguments: The first is the child expression
     * (an object of type AbstractExpression or a string | int | float),
     * the second is the level of the that expression and the third tells you if
     * it has children.
     *
     * Example:
     *
     * $regEx->traverse(function(Closure $expression, int $level, bool $hasChildren)
     * {
     *     var_dump($expression, $level, $hasChildren);
     * });
     *
     * @param Closure $callback
     * @return void
     */
    public function traverse(Closure $callback)
    {
        foreach ($this->expressions as $expression) {
            $expression->traverse($callback);
        }
    }

    /**
     * Removes all partial expressions.
     *
     * @return self
     */
    public function clear()
    {
        $this->expressions = [];

        return $this;
    }

    /**
     * Returns the number of partial expressions
     *
     * @return int
     */
    public function getSize() : int
    {
        return sizeof($this->expressions);
    }

    /**
     * Getter for the partial expressions array
     *
     * @return AbstractExpression[]
     */
    public function getExpressions() : array
    {
        return $this->expressions;
    }

    /**
     * Getter for the "start" property
     *
     * @return string
     */
    public function getStart() : string
    {
        return $this->start;
    }

    /**
     * Setter for the "start" property.
     * This is a raw string.
     *
     * @param string $start
     */
    public function setStart(string $start)
    {
        $this->start = $start;
    }

    /**
     * Getter for the "end" property.
     * This is a raw string - it is not quoted.
     *
     * @return string
     */
    public function getEnd() : string
    {
        return $this->end;
    }

    /**
     * Setter for the "end" property
     *
     * @param string $end
     */
    public function setEnd(string $end)
    {
        $this->end = $end;
    }

    /**
     * Returns an array with the modifier shortcuts that are currently active
     *
     * @return string[]
     */
    public function getCurrentModifiers() : array
    {
        return $this->modifiers;
    }
    
    /**
     * Returns the concatenated partial regular expressions as a string
     * 
     * @return string
     */
    public function toString() : string
    {        
        $regEx = $this->start;
        
        foreach ($this->expressions as $expression) {
            $regEx .= $expression->toString();
        }

        $modifiers = implode('', $this->modifiers);
        
        return $regEx.$this->end.$modifiers;
    }
    
    /**
     * This PHP magic method returns the concatenated partial regular expression as a string
     *
     * @return string
     */
    public function __toString()
    {
        return $this->toString();
    }

}
