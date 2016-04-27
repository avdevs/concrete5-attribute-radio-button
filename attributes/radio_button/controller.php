<?php

namespace Concrete\Package\AttributeRadioButton\Attribute\RadioButton;

use Concrete\Core\Search\ItemList\Database\AttributedItemList;
use Core;
use Database;
use Concrete\Core\Attribute\Controller as AttributeTypeController;

class Controller extends AttributeTypeController
{
    private $akRadioButtonOptionDisplayOrder;

    protected $searchIndexFieldDefinition = array(
        'type' => 'string',
        'options' => array('default' => null, 'notnull' => false)
    );

    public function type_form()
    {
        $this->set('form', Core::make('helper/form'));
        $this->load();
        //$akRadioButtonValues = $this->getRadioButtonValuesFromPost();
        //$this->set('akRadioButtonValues', $akRadioButtonValues);

        if ($this->isPost()) {
            $akRadioButtonValues = $this->getRadioButtonValuesFromPost();
            $this->set('akRadioButtonValues', $akRadioButtonValues);
        } elseif (isset($this->attributeKey)) {
            $options = $this->getOptions();
            $this->set('akRadioButtonValues', $options);
        } else {
            $this->set('akRadioButtonValues', array());
        }
    }

    protected function load()
    {
        $ak = $this->getAttributeKey();
        if (!is_object($ak)) {
            return false;
        }

        $db = Database::get();
        $row = $db->GetRow('select akRadioButtonOptionDisplayOrder from atRadioButtonSettings where akID = ?',
            array($ak->getAttributeKeyID()));
        $this->akRadioButtonOptionDisplayOrder = $row ? $row['akRadioButtonOptionDisplayOrder'] : null;

        $this->set('akRadioButtonOptionDisplayOrder', $this->akRadioButtonOptionDisplayOrder);
    }

    public function duplicateKey($newAK)
    {
        $this->load();
        $db = Database::get();
        $db->Execute('insert into atRadioButtonSettings (akID,  akRadioButtonOptionDisplayOrder) values (?, ?)',
            array(
                $newAK->getAttributeKeyID(),
                $this->akRadioButtonOptionDisplayOrder
            ));
        $r = $db->Execute('select value, displayOrder from atRadioButtonOptions where akID = ?',
            $this->getAttributeKey()->getAttributeKeyID());
        while ($row = $r->FetchRow()) {
            $db->Execute('insert into atRadioButtonOptions (akID, value, displayOrder) values (?, ?, ?)',
                array(
                    $newAK->getAttributeKeyID(),
                    $row['value'],
                    $row['displayOrder'],
                ));
        }
    }

    public function exportKey($akey)
    {
        $this->load();
        $db = Database::get();
        $type = $akey->addChild('type');
        $type->addAttribute('display-order', $this->akRadioButtonOptionDisplayOrder);
        $r = $db->Execute('select value, displayOrder from atRadioButtonOptions where akID = ? order by displayOrder asc',
            $this->getAttributeKey()->getAttributeKeyID());
        $options = $type->addChild('options');
        while ($row = $r->FetchRow()) {
            $opt = $options->addChild('option');
            $opt->addAttribute('value', $row['value']);
        }

        return $akey;
    }

    public function exportValue(\SimpleXMLElement $akn)
    {
        $list = $this->getRadioButtonSelectedOptions();
        if ($list->count() > 0) {
            $av = $akn->addChild('value');
            foreach ($list as $l) {
                $av->addChild('option', (string)$l);
            }
        }
    }

    public function importValue(\SimpleXMLElement $akv)
    {
        if (isset($akv->value)) {
            $vals = array();
            foreach ($akv->value->children() as $ch) {
                $vals[] = (string)$ch;
            }

            return $vals;
        }
    }

    public function setOptionDisplayOrder($order)
    {
        $db = Database::get();
        $this->akRadioButtonOptionDisplayOrder = $order;
        $db->Replace('atRadioButtonSettings', array(
            'akID' => $this->attributeKey->getAttributeKeyID(),
            'akRadioButtonOptionDisplayOrder' => $order
        ), array('akID'), true);
    }

    public function setOptions($options)
    {
        foreach ($options as $option) {
            Option::add($this->attributeKey, $option, 0);
        }
    }

    public function importKey($akey)
    {
        if (isset($akey->type)) {
            $akRadioButtonOptionDisplayOrder = $akey->type['display-order'];
            $db = Database::get();
            $db->Replace('atRadioButtonSettings', array(
                'akID' => $this->attributeKey->getAttributeKeyID(),
                'akRadioButtonOptionDisplayOrder' => $akRadioButtonOptionDisplayOrder,
            ), array('akID'), true);

            if (isset($akey->type->options)) {
                foreach ($akey->type->options->children() as $option) {
                    Option::add($this->attributeKey, $option['value'], $option['is-end-user-added']);
                }
            }
        }
    }

    private function getRadioButtonValuesFromPost()
    {
        $options = new OptionList();
        $displayOrder = 0;

        foreach ($_POST as $key => $value) {
            if (!strstr($key, 'akRadioButtonValue_') || $value == 'TEMPLATE') {
                continue;
            }
            $opt = false;
            // strip off the prefix to get the ID
            $id = substr($key, 19);

            // now we determine from the post whether this is a new option
            // or an existing. New ones have this value from in the akRadioButtonValueNewOption_ post field
            if ($_POST['akRadioButtonValueNewOption_' . $id] == $id) {
                $opt = new Option(0, $value, $displayOrder);
                $opt->tempID = $id;
            } elseif ($_POST['akRadioButtonValueExistingOption_' . $id] == $id) {
                $opt = new Option($id, $value, $displayOrder);
            }

            if (is_object($opt)) {
                $options->add($opt);
                $displayOrder++;
            }
        }

        return $options;
    }

    public function form()
    {
        $this->load();
        $options = $this->getRadioButtonSelectedOptions();
        $selectedRadioButtonOptions = array();
        $selectedRadioButtonOptionValues = array();
        foreach ($options as $opt) {
            $selectedRadioButtonOptions[] = $opt->getRadioButtonAttributeOptionID();
            $selectedRadioButtonOptionValues[$opt->getRadioButtonAttributeOptionID()] = $opt->getRadioButtonAttributeOptionValue();
        }
        $this->set('selectedRadioButtonOptionValues', $selectedRadioButtonOptionValues);
        $this->set('selectedRadioButtonOptions', $selectedRadioButtonOptions);
    }

    public function search()
    {
        $this->load();
        $selectedRadioButtonOptions = $this->request('atRadioButtonOptionID');
        if (!is_array($selectedRadioButtonOptions)) {
            $selectedRadioButtonOptions = array();
        }
        $this->set('selectedRadioButtonOptions', $selectedRadioButtonOptions);
    }

    public function deleteValue()
    {
        $db = Database::get();
        $db->Execute('delete from atRadioButtonOptionsSelected where avID = ?', array($this->getAttributeValueID()));
    }

    public function deleteKey()
    {
        $db = Database::get();
        $db->Execute('delete from atRadioButtonSettings where akID = ?', array($this->attributeKey->getAttributeKeyID()));
        $r = $db->Execute('select ID from atRadioButtonOptions where akID = ?',
            array($this->attributeKey->getAttributeKeyID()));
        while ($row = $r->FetchRow()) {
            $db->Execute('delete from atRadioButtonOptionsSelected where atRadioButtonOptionID = ?', array($row['ID']));
        }
        $db->Execute('delete from atRadioButtonOptions where akID = ?', array($this->attributeKey->getAttributeKeyID()));
    }

    public function saveForm($data)
    {
        $this->load();
        // radio list. Only one option possible. No new options.
        $option = Option::getByID($data['atRadioButtonOptionValue']);
        if (is_object($option)) {
            $this->saveValue($option);
        } else {
            $this->saveValue(null);
        }

    }

    // Sets radio options for a particular attribute
    // If the $value == string, then 1 item is selected
    // if array, then multiple, but only if the attribute in question is a radio multiple
    // Note, items CANNOT be added to the pool (even if the attribute allows it) through this process.
    // Items should now be added to the database if they don't exist already & if the allow checkbox is checked under the attribute settings
    // Code from this bug - http://www.concrete5.org/index.php?cID=595692
    public function saveValue($value)
    {
        $db = Database::get();
        $this->load();
        $options = array();

        if ($value != null) {

            if (is_array($value)) {
                $value = $value[0];
            }

            if ($value instanceof Option) {
                $opt = $value;
            } else {
                $opt = Option::getByValue($value, $this->attributeKey);
            }

            if (is_object($opt)) {
                $options[] = $opt;
            }

        }

        $db->Execute('delete from atRadioButtonOptionsSelected where avID = ?', array($this->getAttributeValueID()));
        if (count($options) > 0) {
            foreach ($options as $opt) {
                $db->Execute('insert into atRadioButtonOptionsSelected (avID, atRadioButtonOptionID) values (?, ?)',
                    array($this->getAttributeValueID(), $opt->getRadioButtonAttributeOptionID()));
            }
        }
    }

    public function getDisplayValue()
    {
        $list = $this->getRadioButtonSelectedOptions();
        $html = '';
        foreach ($list as $l) {
            $html .= $l->getRadioButtonAttributeOptionDisplayValue() . '<br/>';
        }

        return $html;
    }

    public function getDisplaySanitizedValue()
    {
        return $this->getDisplayValue();
    }

    public function validateValue()
    {
        return is_object($value = $this->getValue()) && ((string)$value != '');
    }

    public function validateForm($p)
    {
        $this->load();
        $options = $this->request('atRadioButtonOptionValue');
        return $options != '';
    }

    public function searchForm($list)
    {
        $options = $this->request('atRadioButtonOptionID');
        $db = Database::get();
        $tbl = $this->attributeKey->getIndexedSearchTable();
        if (!is_array($options)) {
            return $list;
        }
        $optionQuery = array();
        foreach ($options as $id) {
            if ($id > 0) {
                $opt = Option::getByID($id);
                if (is_object($opt)) {
                    $optionQuery[] = $opt->getRadioButtonAttributeOptionValue(false);
                }
            }
        }
        if (count($optionQuery) == 0) {
            return false;
        }

        $i = 0;
        $multiString = '';
        foreach ($optionQuery as $val) {
            $val = $db->quote('%||' . $val . '||%');
            $multiString .= 'REPLACE(ak_' . $this->attributeKey->getAttributeKeyHandle() . ', "\n", "||") like ' . $val . ' ';
            if (($i + 1) < count($optionQuery)) {
                $multiString .= 'OR ';
            }
            $i++;
        }
        $list->filter(false, '(' . $multiString . ')');

        return $list;
    }

    public function getValue()
    {
        $list = $this->getRadioButtonSelectedOptions();

        return $list;
    }

    public function getSearchIndexValue()
    {
        $str = "\n";
        $list = $this->getRadioButtonSelectedOptions();
        foreach ($list as $l) {
            $l = (is_object($l) && method_exists($l, '__toString')) ? $l->__toString() : $l;
            $str .= $l . "\n";
        }
        // remove line break for empty list
        if ($str == "\n") {
            return '';
        }

        return $str;
    }

    public function getRadioButtonSelectedOptions()
    {
        if (!isset($this->akRadioButtonOptionDisplayOrder)) {
            $this->load();
        }
        $db = Database::get();
        $sortByDisplayName = false;
        switch ($this->akRadioButtonOptionDisplayOrder) {
            case 'popularity_desc':
                $options = $db->GetAll("select ID, value, displayOrder, (select count(s2.atRadioButtonOptionID) from atRadioButtonOptionsSelected s2 where s2.atRadioButtonOptionID = ID) as total from atRadioButtonOptionsSelected inner join atRadioButtonOptions on atRadioButtonOptionsSelected.atRadioButtonOptionID = atRadioButtonOptions.ID where avID = ? order by total desc, value asc",
                    array($this->getAttributeValueID()));
                break;
            case 'alpha_asc':
                $options = $db->GetAll("select ID, value, displayOrder from atRadioButtonOptionsSelected inner join atRadioButtonOptions on atRadioButtonOptionsSelected.atRadioButtonOptionID = atRadioButtonOptions.ID where avID = ?",
                    array($this->getAttributeValueID()));
                $sortByDisplayName = true;
                break;
            default:
                $options = $db->GetAll("select ID, value, displayOrder from atRadioButtonOptionsSelected inner join atRadioButtonOptions on atRadioButtonOptionsSelected.atRadioButtonOptionID = atRadioButtonOptions.ID where avID = ? order by displayOrder asc",
                    array($this->getAttributeValueID()));
                break;
        }
        $db = Database::get();
        $list = new OptionList();
        foreach ($options as $row) {
            $opt = new Option($row['ID'], $row['value'], $row['displayOrder']);
            $list->add($opt);
        }
        if ($sortByDisplayName) {
            $list->sortByDisplayName();
        }

        return $list;
    }

    /**
     * Used by radio. Automatically takes a value request and converts it into tag/text key value pairs.
     * New options are just text/tag, whereas existing ones are RadioButtonAttributeOption:ID/text
     */
    public function action_load_autocomplete_selected_value()
    {
        $r = \Request::getInstance();
        $value = $r->query->get('value');
        $values = explode(',', $value);
        $response = array();
        foreach ($values as $value) {
            $value = trim($value);
            $o = new \stdClass;
            if (strpos($value, 'RadioButtonAttributeOption:') === 0) {
                $optionID = substr($value, 22);
                $option = Option::getByID($optionID);
                if (is_object($option)) {
                    $o->id = $value;
                    $o->text = $option->getRadioButtonAttributeOptionValue();
                }
            } else {
                $o->id = $value;
                $o->text = $value;
            }

            $response[] = $o;
        }

        print json_encode($response);
        \Core::shutdown();
    }

    public function action_load_autocomplete_values()
    {
        $this->load();
        $values = array();
        // now, if the current instance of the attribute key allows us to do autocomplete, we return all the values
        if ($this->akRadioButtonAllowOtherValues) {
            $options = $this->getOptions($_GET['q'] . '%');
            foreach ($options as $opt) {
                $o = new \stdClass;
                $o->id = 'RadioButtonAttributeOption:' . $opt->getRadioButtonAttributeOptionID();
                $o->text = $opt->getRadioButtonAttributeOptionValue(false);
                $values[] = $o;
            }
        }
        print json_encode($values);
    }

    public function getOptionUsageArray($parentPage = false, $limit = 9999)
    {
        $db = Database::get();
        $q = "select atRadioButtonOptions.value, atRadioButtonOptionID, count(atRadioButtonOptionID) as total from Pages inner join CollectionVersions on (Pages.cID = CollectionVersions.cID and CollectionVersions.cvIsApproved = 1) inner join CollectionAttributeValues on (CollectionVersions.cID = CollectionAttributeValues.cID and CollectionVersions.cvID = CollectionAttributeValues.cvID) inner join atRadioButtonOptionsSelected on (atRadioButtonOptionsSelected.avID = CollectionAttributeValues.avID) inner join atRadioButtonOptions on atRadioButtonOptionsSelected.atRadioButtonOptionID = atRadioButtonOptions.ID where Pages.cIsActive = 1 and CollectionAttributeValues.akID = ? ";
        $v = array($this->attributeKey->getAttributeKeyID());
        if (is_object($parentPage)) {
            $v[] = $parentPage->getCollectionID();
            $q .= "and cParentID = ?";
        }
        $q .= " group by atRadioButtonOptionID order by total desc limit " . $limit;
        $r = $db->Execute($q, $v);
        $list = new OptionList();
        $i = 0;
        while ($row = $r->FetchRow()) {
            $opt = new Option($row['atRadioButtonOptionID'], $row['value'], $i, $row['total']);
            $list->add($opt);
            $i++;
        }

        return $list;
    }

    public function filterByAttribute(AttributedItemList $list, $value, $comparison = '=')
    {
        if ($value instanceof Option) {
            $option = $value;
        } else {
            $option = Option::getByValue($value);
        }
        if (is_object($option)) {
            $column = 'ak_' . $this->attributeKey->getAttributeKeyHandle();
            $qb = $list->getQueryObject();
            $qb->andWhere(
	            $qb->expr()->like($column, ':optionValue_' . $this->attributeKey->getAttributeKeyID())
            );
	        $qb->setParameter('optionValue_' . $this->attributeKey->getAttributeKeyID(), "%\n" . $option->getRadioButtonAttributeOptionValue(false) . "\n%");
        }
    }

    /**
     * Returns a list of available options optionally filtered by an sql $like statement ex: startswith%.
     *
     * @param string $like
     *
     * @return RadioButtonAttributeTypeOptionList
     */
    public function getOptions($like = null)
    {
        if (!isset($this->akRadioButtonOptionDisplayOrder)) {
            $this->load();
        }
        $db = Database::get();
        switch ($this->akRadioButtonOptionDisplayOrder) {
            case 'popularity_desc':
                if (isset($like) && strlen($like)) {
                    $r = $db->Execute('select ID, value, displayOrder, count(atRadioButtonOptionsSelected.atRadioButtonOptionID) as total
						from atRadioButtonOptions left join atRadioButtonOptionsSelected on (atRadioButtonOptions.ID = atRadioButtonOptionsSelected.atRadioButtonOptionID)
						where akID = ? AND atRadioButtonOptions.value LIKE ? group by ID order by total desc, value asc',
                        array($this->attributeKey->getAttributeKeyID(), $like));
                } else {
                    $r = $db->Execute('select ID, value, displayOrder, count(atRadioButtonOptionsSelected.atRadioButtonOptionID) as total
						from atRadioButtonOptions left join atRadioButtonOptionsSelected on (atRadioButtonOptions.ID = atRadioButtonOptionsSelected.atRadioButtonOptionID)
						where akID = ? group by ID order by total desc, value asc',
                        array($this->attributeKey->getAttributeKeyID()));
                }
                break;
            case 'alpha_asc':
                if (isset($like) && strlen($like)) {
                    $r = $db->Execute('select ID, value, displayOrder from atRadioButtonOptions where akID = ? AND atRadioButtonOptions.value LIKE ? order by value asc',
                        array($this->attributeKey->getAttributeKeyID(), $like));
                } else {
                    $r = $db->Execute('select ID, value, displayOrder from atRadioButtonOptions where akID = ? order by value asc',
                        array($this->attributeKey->getAttributeKeyID()));
                }
                break;
            default:
                if (isset($like) && strlen($like)) {
                    $r = $db->Execute('select ID, value, displayOrder from atRadioButtonOptions where akID = ? AND atRadioButtonOptions.value LIKE ? order by displayOrder asc',
                        array($this->attributeKey->getAttributeKeyID(), $like));
                } else {
                    $r = $db->Execute('select ID, value, displayOrder from atRadioButtonOptions where akID = ? order by displayOrder asc',
                        array($this->attributeKey->getAttributeKeyID()));
                }
                break;
        }
        $options = new OptionList();
        while ($row = $r->FetchRow()) {
            $opt = new Option($row['ID'], $row['value'], $row['displayOrder']);
            $options->add($opt);
        }

        return $options;
    }

    public function saveKey($data)
    {
        $ak = $this->getAttributeKey();

        $db = Database::get();

        $initialOptionSet = $this->getOptions();
        $selectedRadioButtonPostValues = $this->getRadioButtonValuesFromPost();

        if (isset($data['akRadioButtonOptionDisplayOrder']) && in_array($data['akRadioButtonOptionDisplayOrder'],
                array('display_asc', 'alpha_asc', 'popularity_desc'))
        ) {
            $akRadioButtonOptionDisplayOrder = $data['akRadioButtonOptionDisplayOrder'];
        } else {
            $akRadioButtonOptionDisplayOrder = 'display_asc';
        }

        // now we have a collection attribute key object above.
        $db->Replace('atRadioButtonSettings', array(
            'akID' => $ak->getAttributeKeyID(),
            'akRadioButtonOptionDisplayOrder' => $akRadioButtonOptionDisplayOrder,
        ), array('akID'), true);

        // Now we add the options
        $newOptionSet = new OptionList();
        $displayOrder = 0;
        foreach ($selectedRadioButtonPostValues as $option) {
            $opt = $option->saveOrCreate($ak);
            if ($akRadioButtonOptionDisplayOrder == 'display_asc') {
                $opt->setDisplayOrder($displayOrder);
            }
            $newOptionSet->add($opt);
            $displayOrder++;
        }

        // Now we remove all options that appear in the
        // old values list but not in the new
        foreach ($initialOptionSet as $iopt) {
            if (!$newOptionSet->contains($iopt)) {
                $iopt->delete();
            }
        }
    }

    /**
     * Convenience methods to retrieve a radio button attribute key's settings.
     */

    public function getOptionDisplayOrder()
    {
        if (is_null($this->akRadioButtonOptionDisplayOrder)) {
            $this->load();
        }

        return $this->akRadioButtonOptionDisplayOrder;
    }
}
