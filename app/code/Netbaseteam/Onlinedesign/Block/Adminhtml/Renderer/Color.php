<?php
namespace Netbaseteam\Onlinedesign\Block\Adminhtml\Renderer;

class Color extends \Magento\Config\Block\System\Config\Form\Field {

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param Registry $coreRegistry
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context, array $data = []
    ) {
        parent::__construct($context, $data);
    }

    protected function _getElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element) {
        $html = $element->getElementHtml();
        $value = $element->getData('value');
        $html .= '<script type="text/javascript">
            require(["jquery","jquery/colorpicker/js/colorpicker"], function ($) {
                $(document).ready(function () {
                    var $el = $("#color_main_hex");
                    $el.css("display", "block !important");
                    $el.css("backgroundColor", $el.val());
                    $el.attr("autocomplete","off");
                    
                    // Attach the color picker
                    $el.ColorPicker({
                        color: "'. $value .'",
                        onChange: function (hsb, hex, rgb) {
                            $el.css("backgroundColor", "#" + hex).val("#" + hex);
                            $("#color_main_color_name").val("#" + hex);
                        }
                    });
                });
            });
            </script>';
        return $html;
    }

}