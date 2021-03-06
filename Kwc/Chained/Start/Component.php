<?php
class Kwc_Chained_Start_Component extends Kwc_Abstract
{
    public static function getSettings($param = null)
    {
        $ret = parent::getSettings($param);
        $ret['flags']['hasAllChainedByMaster'] = true;
        return $ret;
    }

    public static function validateSettings($settings, $componentClass)
    {
        parent::validateSettings($settings, $componentClass);
        if (!isset($settings['flags']['chainedType']) || !is_string($settings['flags']['chainedType'])) {
            throw new Kwf_Exception("Flag 'chainedType' not set for '$componentClass'.");
        }
    }

    public static function getAllChainedByMasterFromChainedStart($componentClass, $master, $chainedType, $parentDataSelect = array())
    {
        if (Kwc_Abstract::getFlag($componentClass, 'chainedType') != $chainedType) return array();
        $ret = array();
        foreach (Kwf_Component_Data_Root::getInstance()->getComponentsByClass($componentClass, $parentDataSelect) as $chainedStart) {
            //if additional subroots are above trl subroot (eg. domains)
            if ($sr = $chainedStart->parent->getSubroot()) {
                $masterSr = $master;
                while (Kwc_Abstract::getFlag($masterSr->componentClass, 'chainedType') != $chainedType) {
                    $masterSr = $masterSr->parent;
                    if (!$masterSr) continue 2;
                }
                if ($masterSr->parent && $sr->componentId != $masterSr->parent->getSubroot()->componentId) {
                    continue;
                }
            }
            $i = Kwc_Chained_Abstract_Component::getChainedByMasterAndType($master, $chainedStart, $chainedType, $parentDataSelect);
            if ($i) $ret[] = $i;
        }
        return $ret;
    }

    public function hasMasterTemplate()
    {
        $ret = (bool)Kwc_Abstract::getMasterTemplateFile($this->getData()->componentClass);
        if (!$ret) {
            $ret = $this->getData()->chained->getComponent()->hasMasterTemplate();
        }
        return $ret;
    }

    public function getMasterTemplateVars(Kwf_Component_Data $innerComponent, Kwf_Component_Renderer_Abstract $renderer)
    {
        $ret = $this->getData()->chained->getComponent()->getMasterTemplateVars($innerComponent, $renderer);
        $template = Kwc_Abstract::getMasterTemplateFile($this->getData()->componentClass);
        if ($template) $ret['template'] = $template;
        return $ret;
    }
}
