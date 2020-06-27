<?php
/**
 * Plugin
 * 
 * @category BitVax_Widget
 * @package  BitVax
 * @author   BitVax <info@kutinko.com>
 * @license  MIT License, http://www.opensource.org/licenses/MIT
 * @link     http://url.com
 */
namespace BitVax\Widget\Model;

use Magento\Widget\Model\Widget as BaseWidget;

class Widget
{
    /**
     * FIX error in url from image chooser
     *
     * @param BaseWidget $subject
     * @param [type] $type
     * @param array $params
     * @param boolean $asIs
     * @return void
     */
    public function beforeGetWidgetDeclaration(BaseWidget $subject, $type, $params = [], $asIs = true)
    {
        // I rather do a check for a specific parameters
        if(key_exists("category_image", $params)) {

            $url = $params["category_image"];
            if(strpos($url,'/directive/___directive/') !== false) {

                $parts = explode('/', $url);
                $key   = array_search("___directive", $parts);
                if($key !== false) {

                    $url = $parts[$key+1];
                    $url = base64_decode(strtr($url, '-_,', '+/='));

                    $parts = explode('"', $url);
                    $key   = array_search("{{media url=", $parts);
                    $url   = $parts[$key+1];

                    $params["category_image"] = $url;
                }
            }
        }

        return array($type, $params, $asIs);
    }
}