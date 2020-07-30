<?php
/*
 * This file is part of the Osynapsy package.
 *
 * (c) Pietro Celeste <p.celeste@osynapsy.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Osynapsy\Html;

class Tag
{
    //Attribute repo
    private $attributes = [];
    //Content repo
    private $childs = [];

    public $ref = array();
    public $tagdep = 0;
    public $parent = null;

    /**
     * Constructor of tag
     *
     * @param type $tag to build
     * @param type $id identity of tag
     * @param type $class css class
     */
    public function __construct($tag = 'dummy', $id = null, $class = null)
    {
        $this->att(0, $tag);
        if (!empty($id)) {
            $this->att('id', $id);
        }
        if (!empty($class)) {
            $this->att('class', $class);
        }
    }

    /**
     * Check if inaccessible property is in attribute
     *
     * @param type $attribute
     * @return type
     */
    public function __get($attribute)
    {
        if ($attribute == 'tag') {
            return $this->attributes[0];
        }
        return array_key_exists($attribute, $this->attributes) ? $this->attributes[$attribute] : null;
    }

    /**
     *
     * @param type $attribute
     * @param type $value
     */
    public function __set($attribute, $value)
    {
        if (is_array($value)) {
            throw \Exception('Illegal content of value attribute' . print_r($value, true));
        }
        $this->attributes[$attribute] = $value;
    }

    /**
     * Add child content to childs repo
     *
     * @param $child
     * @return \Osynapsy\Html\tag|$this
     */
    public function add($child)
    {
        if ($child instanceof tag) {
            if ($child->id && array_key_exists($child->id,$this->ref)) {
                return $this->ref[$child->id];
            }
            $child->tagdep = abs($this->tagdep) + 1;
            $this->tagdep = abs($this->tagdep) * -1;
        }
        //Append child to childs repo
        $this->childs[] = $child;
        //If child isn't object return $this tag
        if (!is_object($child)) {
            return $this;
        }
        if ($child->id) {
            $this->ref[$child->id] =& $child;
        }
        $child->parent =& $this;
        return $child;
    }

    public function addClass($class)
    {
        return empty($class) ? $this : $this->att('class', $class, true);
    }

    /**
     * Add childs from array
     *
     * @param array $array
     * @return $this
     */
    public function addFromArray(array $array)
    {
        foreach ($array as $child) {
            $this->add($child);
        }
        return $this;
    }

    /**
     * Set attribute value of tag
     *
     * @param type $attribute
     * @param type $value
     * @param type $concat
     * @return $this
     */
    public function att($attribute, $value = '', $concat = false)
    {
        if (is_array($attribute)) {
            foreach ($attribute as $key => $value) {
                $this->attributes[$key] = $value;
            }
        } elseif ($concat && !empty($this->attributes[$attribute])) {
            $this->attributes[$attribute] .= ($concat === true ? ' ' : $concat) . $value;
        } else {
            $this->attributes[$attribute] = $value;
        }
        return $this;
    }

    /**
     * Build html tag e return string
     *
     * @return string
     */
    protected function build()
    {
        $tag = array_shift($this->attributes);
        $content = implode('', $this->childs);
        if (empty($tag) || $tag == 'dummy') {
            return $content;
        }
        $spaces = $this->tagdep != 0 ? PHP_EOL.str_repeat(" ",abs($this->tagdep)) : '';
        $strTag = $spaces.'<'.$tag;
        foreach ($this->attributes as $key => $value) {
            if (is_object($value) && !method_exists($value, '__toString')) {
                $strTag .= ' error="Attribute value is object ('.get_class($value).')"';
                continue;
            }
            $strTag .= ' '.$key.'="'.htmlspecialchars($value, ENT_QUOTES).'"';
            // la conversione del contentuto degli attributi viene fornita da Tag in modo
            // tale che non debba essere gestito dai suoi figli
            /*$strTag .= ' '.$key.'="'.$val.'"';*/
        }
        $strTag .= '>';

        if (!in_array($tag, ['input', 'img', 'link', 'meta'])) {
            $strTag .= $content . ($this->tagdep < 0 ? $spaces : '') ."</{$tag}>";
        }
        return $strTag;
    }

    /**
     * Static method for create a tag object
     *
     * @param string $tag
     * @param string $id
     * @return \Osynapsy\Html\tag
     */
    public static function create($tag, $id = null, $class = null)
    {
        return new Tag($tag, $id, $class);
    }

    /**
     * Get html string of tag
     *
     * @return type
     */
    public function get()
    {
        return $this->build();
    }

    public function getAttribute($attributeId)
    {
        return array_key_exists($attributeId, $this->attributes) ? $this->attributes[$attributeId] : null;
    }

    /**
     * Get $index child from repo
     *
     * @param int $index
     * @return boolean
     */
    public function child($index = 0)
    {
        if (is_null($index)) {
            return $this->childs;
        }
        if (array_key_exists($index, $this->childs)) {
            return $this->childs[$index];
        }
        return false;
    }

    /**
     * Check if tag content is empty
     *
     * @return boolean
     */
    public function isEmpty()
    {
        return count($this->childs) > 0 ? false : true;
    }

    /**
     * Magic method for rendering tag in html
     *
     * @return type
     */
    public function __toString()
    {
        try {
            return $this->get();
        } catch (\Exception $e) {
            //var_dump($str);
            trigger_error($e->getMessage());
            echo '<pre>';
            var_dump(debug_backtrace(10));
            echo '</pre>';
            return $this->id;
        }
    }
}
