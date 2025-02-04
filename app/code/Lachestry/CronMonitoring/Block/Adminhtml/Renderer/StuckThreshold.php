<?php
declare(strict_types=1);

namespace Lachestry\CronMonitoring\Block\Adminhtml\Renderer;

use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;
use Magento\Framework\DataObject;

class StuckThreshold extends AbstractFieldArray
{
    const GROUP_NAME_COLUMN  = 'group_name';
    const TIME_BEFORE_COLUMN = 'time_before';

    protected GroupNamesSelect|null $groupNamesSelect = null;

    protected function _prepareToRender()
    {
        $this->addColumn(
            self::GROUP_NAME_COLUMN,
            [
                'label'    => __('Group Name'),
                'renderer' => $this->getGroupNamesSelect(),
            ]
        );

        $this->addColumn(
            self::TIME_BEFORE_COLUMN,
            [
                'label' => __('Сron Stuck Threshold'),
                'class' => 'validate-not-negative-number'
            ]
        );

        $this->_addAfter = false;
        $this->_addButtonLabel = __('Add');
    }

    protected function _prepareArrayRow(DataObject $row): void
    {
        $options = [];

        $groupName = $row->getData(self::GROUP_NAME_COLUMN);
        if ($groupName) {
            $optionHash = $this->getGroupNamesSelect()->calcOptionHash(reset($groupName));
            $options['option_' . $optionHash] = 'selected="selected"';
        }

        $row->setData('option_extra_attrs', $options);
    }

    protected function getGroupNamesSelect(): GroupNamesSelect|null
    {
        if (!$this->groupNamesSelect) {
            $this->groupNamesSelect = $this->getLayout()->createBlock(
                GroupNamesSelect::class,
                '',
                ['data' => ['is_render_to_js_template' => true]]
            );
        }

        return $this->groupNamesSelect;
    }
}
