<?php
class Kwc_Directories_Month_Directory_Generator extends Kwf_Component_Generator_Page_Table
{
    protected $_uniqueFilename = true;

    public function getChildData($parentData, $select = array())
    {
        $ret = array();
        if (!$parentData && ($p = $select->getPart(Kwf_Component_Select::WHERE_CHILD_OF))
                && !$this->_getModel()->hasColumn('component_id')) {
            $parentDatas = $p->getRecursiveChildComponents(array(
                'componentClass' => $this->_class
            ));
        } else {
            $parentDatas = array($parentData /* kann auch null sein*/);
        }
        foreach ($parentDatas as $parentData) {
            $select = $this->_formatSelect($parentData, $select);
            $rows = array();
            if ($select) {
                $rows = $this->_getModel()->fetchAll($select);
            }
            foreach ($rows as $row) {
                $currentPd = array($parentData);
                if (!$parentData) {
                    $currentPd = $this->_getParentDataByRow($row, $select);
                }
                foreach ($currentPd as $pd) {
                    $ret[] = $this->_createData($pd, $row, $select);
                }
            }
        }
        return $ret;
    }

    protected function _getParentDataByRow($row, $select)
    {
        $constraints = array();
        if ($select->hasPart(Kwf_Component_Select::WHERE_SUBROOT)) {
            $constraints['subroot'] = $select->getPart(Kwf_Component_Select::WHERE_SUBROOT);
        }
        if ($select->hasPart(Kwf_Component_Select::IGNORE_VISIBLE)) {
            $constraints['ignoreVisible'] = $select->getPart(Kwf_Component_Select::IGNORE_VISIBLE);
        }
        $news = Kwf_Component_Data_Root::getInstance()
            ->getComponentsByDbId($row->component_id, $constraints);
        $ret = array();
        foreach ($news as $new) {
            $ret = array_merge($ret, $new->getChildComponents(array('componentClass'=>$this->_class)));
        }
        return $ret;
    }

    protected function _formatSelectFilename(Kwf_Component_Select $select)
    {
        if ($select->hasPart(Kwf_Component_Select::WHERE_FILENAME)) {
            $filename = $select->getPart(Kwf_Component_Select::WHERE_FILENAME);
            if (!preg_match('#^([0-9]{4})_([0-9]{2})$#', $filename, $m)) return null;
            $dateColumn = Kwc_Abstract::getSetting($this->_class, 'dateColumn');
            $select->where("YEAR($dateColumn) = ?", $m[1]);
            $select->where("MONTH($dateColumn) = ?", $m[2]);
        }
        return $select;
    }

    protected function _formatSelectId(Kwf_Component_Select $select)
    {
        if ($select->hasPart(Kwf_Model_Select::WHERE_ID)) {
            $id = $select->getPart(Kwf_Model_Select::WHERE_ID);
            if (!preg_match('#^_([0-9]{4})([0-9]{2})$#', $id, $m)) return null;
            $dateColumn = Kwc_Abstract::getSetting($this->_class, 'dateColumn');
            $select->where("YEAR($dateColumn) = ?", $m[1]);
            $select->where("MONTH($dateColumn) = ?", $m[2]);
            $select->unsetPart(Kwf_Model_Select::WHERE_ID);
        }
        return $select;
    }

    protected function _getSelectGroup($dateColumn)
    {
        return array('YEAR('.$dateColumn.')', 'MONTH('.$dateColumn.')');
    }

    protected function _formatSelect($parentData, $select)
    {
        $ret = parent::_formatSelect($parentData, $select);
        if (!$ret) return $ret;
        $dateColumn = Kwc_Abstract::getSetting($this->_class, 'dateColumn');
        $ret->group($this->_getSelectGroup($dateColumn));
        $ret->order($dateColumn, 'DESC');
        if (!$parentData) {
            //hier können wir nicht so wie unten den detail generator verwenden da wir
            //die parent->componentClass nicht wissen
            //wenn also kein $parentData übergeben stimmt der rückgabewert uU nicht
            if ($this->_getModel()->hasColumn('component_id')) {
                $page = $select->getPart(Kwf_Component_Select::WHERE_CHILD_OF);
                if (!$page) {
                    return null;
                }
                $ret->where(new Kwf_Model_Select_Expr_Like('component_id', $page->dbId.'-%'));
            }
        } else {
            //den detail generator vom "haupt" directory holen und das select formatieren lassen
            //der kann korrekt where component_id einfügen oder andere wheres
            $c = $parentData->parent;
            if (is_instance_of($c->componentClass, 'Kwc_Directories_YearMonth_Component')) {
                $c = $c->parent;
            }
            $g = Kwf_Component_Generator_Abstract::getInstance($c->componentClass, 'detail');
            $ret->merge($g->_formatSelect($c, array()));
        }
        return $ret;
    }

    protected function _getNameFromRow($row)
    {
        $dateColumn = Kwc_Abstract::getSetting($this->_class, 'dateColumn');
        $months = Zend_Locale::getTranslationList('Month',
            Kwf_Registry::get('trl')->getTargetLanguage());
        $date = strtotime($row->$dateColumn);
        return $months[date('n', $date)].' '.date('Y', $date);
    }

    protected function _getFilenameFromRow($row)
    {
        $dateColumn = Kwc_Abstract::getSetting($this->_class, 'dateColumn');
        return date('Y_m', strtotime($row->$dateColumn));
    }
    protected function _getIdFromRow($row)
    {
        $dateColumn = Kwc_Abstract::getSetting($this->_class, 'dateColumn');
        return date('Ym', strtotime($row->$dateColumn));
    }
}
