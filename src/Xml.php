<?php

namespace Actengage\LaravelMessageGears;

use Illuminate\Contracts\Support\Arrayable;
use SimpleXMLElement;

class Xml extends SimpleXMLElement {

    public function __toString()
    {
        return $this->toString();
    }

    public function toString() {
        if(!count($this->children())) {
            return null;
        }
        
        return trim(preg_replace('/(<\\?xml.+>\\n)(<root>(.+)<\/root>)?/', '$1$3', $this->asXML()));
    }

    public static function fromArray(Array $data, Xml $parent = null, $parentKey = null)
    {
        if(!$parent instanceof Xml) { 
            $parent = new static('<root />');
        }

        foreach($data as $key => $value) {
            $key = $parentKey ?: $key;

            if($value instanceof Arrayable) {
                static::fromArray($value->toArray(), $parent->addChild($key));
            }
            else if(is_array($value)) {
                if(static::isAssociative($value)) {
                    static::fromArray($value, $parent->addChild($key));
                }
                else {
                    static::fromArray($value, $parent, $key);
                }
            }
            else if(is_object($value)) {
                static::fromArray((array) $value, $parent->addChild($key));
            }
            else {
                if(is_bool($value) || is_null($value)) {
                    $value = json_encode($value);
                }

                $parent->addChild($key, $value);
            }
        }

        return $parent;
    }

    public static function isAssociative(array $array) {
        return count(array_filter(array_keys($array), 'is_string')) > 0;
    }

}