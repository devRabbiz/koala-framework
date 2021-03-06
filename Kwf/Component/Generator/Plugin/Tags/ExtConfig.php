<?php
class Kwf_Component_Generator_Plugin_Tags_ExtConfig extends Kwf_Component_Abstract_ExtConfig_Abstract
{
    protected function _getConfig()
    {
        $config = $this->_getStandardConfig('kwf.assigngrid', null);
        $config['gridAssignedControllerUrl'] = $this->getControllerUrl();
        $config['gridDataControllerUrl'] = $this->getControllerUrl('Tags');
        return array('tags' => $config);
    }
}
