<?php

namespace DAPClientBundle\Services;

class RecordService
{
    /**
     * @var array
     */
    public $searchSettings;

    /**
     * @var array
     */
    public $downloadSettings;
    
    public function __construct(array $searchSettings)
    {
        $this->searchSettings = $searchSettings;
    }

    public function isArrayEmpty($array)
    {
        $swResult = true;
        if (empty($array)) {
            return true;
        }
        foreach ($array as $child) {
            switch (gettype($child)) {
                case 'object':
                    $swResult = empty((array) $child)? true : false;
                    break;
                
                default:
                    $swResult = empty($child)? true : false;
                    break;
                }
                
            if (!$swResult) {
                break;
            }
        }
        
        return $swResult;
    }

    public function isObjectEmpty($obj)
    {
        return $this->isArrayEmpty((array) $obj);
    }

    public function getDisplayableAlsoKnowAs($title)
    {
        unset($title->displayTitle);
        if ($this->isObjectEmpty($title)) {
            return null;
        }
        
        $result = [];
        
        if (isset($title->uniformTitle->titleString) && !empty($title->uniformTitle->titleString)) {
            $result["uniformTitle"] = $title->uniformTitle->titleString;
        }
        if (isset($title->extendedTitle) && !empty($title->extendedTitle)) {
            $result["extendedTitle"] = $title->extendedTitle;
        }

        if (isset($title->alternateTitles) && !$this->isArrayEmpty($title->alternateTitles)) {
            $result["alternateTitles"] = "";
            foreach ($title->alternateTitles as $value) {
                if ($value->titleText == end($title->alternateTitles)->titleText) {
                    $result["alternateTitles"] .= (empty($value->titleText))? $value->titleText : $value->titleText;
                } else {
                    $result["alternateTitles"] .= (empty($value->titleText))? $value->titleText : $value->titleText.", ";
                }
            }
        }
        
        if (!empty($result)) {
            return (object) $result;
        } else {
            return null;
        }
    }

    public function getDisplayableGroupings($groupings)
    {
        if ($this->isArrayEmpty($groupings)) {
            return null;
        }

        $result = "";

        foreach ($groupings as $value) {
            if (!empty($value)) {
                $result .= ($value == end($groupings)) ? $value.", " : $value;
            }
        }
        return $result;
    }

    public function getRecordIcon($format)
    {
        $iconsList = $this->searchSettings['icons'];
        $icon = $iconsList["default_icon"];

        $format = strtolower($format);
        if (array_key_exists($format, $iconsList)) {
            $icon = $iconsList[$format];
        }
      
        return $icon;
    }
}
