<?php
/**
 * Copyright © Webscale. All rights reserved.
 * See LICENSE for license details.
 */

namespace Webscale\CacheExclusions\Block\Adminhtml\Form\Field;

use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;
use Magento\Framework\DataObject;

class QueryForSearch extends AbstractFieldArray
{
    /**
     * Prepare to render
     */
    protected function _prepareToRender()
    {
        $this->addColumn('parameter', ['label' => __('Parameter'), 'class' => 'required-entry']);
        $this->_addAfter = false;
        $this->_addButtonLabel = __('Add Parameter');
    }

    /**
     * Prepare existing row data object
     *
     * @param DataObject $row
     * @throws \Exception
     */
    protected function _prepareArrayRow(DataObject $row)
    {
        $options = [];

        $parameter = $row->getParameter();
        if ($parameter !== null) {
            $options['option_' . $this->calcOptionHash($parameter)] = 'selected="selected"';
        }

        $row->setData('option_extra_attrs', $options);
    }

    /**
     * Calculate option hash
     *
     * @param string $optionValue
     * @return string
     */
    private function calcOptionHash($optionValue)
    {
        return sprintf('%u', crc32($optionValue));
    }
}
