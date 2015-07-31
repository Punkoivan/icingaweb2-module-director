<?php

namespace Icinga\Module\Director\Forms;

use Icinga\Module\Director\Web\Form\DirectorObjectForm;
use Icinga\Web\Hook;

class DirectorDatafieldForm extends DirectorObjectForm
{
    protected $objectName = 'Data field';

    public function setup()
    {
        $this->addHtmlHint(
            $this->translate('Data fields allow you to customize input controls your custom variables.')
        );

        $this->addElement('text', 'varname', array(
            'label'       => $this->translate('Field name'),
            'description' => $this->translate('The unique name of the field'),
            'required'    => true,
        ));

        $this->addElement('text', 'caption', array(
            'label'       => $this->translate('Caption'),
            'required'    => true,
            'description' => $this->translate('The caption which should be displayed')
        ));

        $this->addElement('textarea', 'description', array(
            'label'       => $this->translate('Description'),
            'description' => $this->translate('A description about the field'),
            'rows'        => '3',
        ));

        $this->addElement('select', 'datatype', array(
            'label'         => $this->translate('Data type'),
            'description'   => $this->translate('Field type'),
            'required'      => true,
            'multiOptions'  => $this->enumDataTypes(),
            'class'         => 'autosubmit',
        ));


        if ($class = $this->getSentValue('datatype')) {
            if ($class && array_key_exists($class, $this->enumDataTypes())) {
                $this->addSettings($class);
            }
        } elseif ($class = $this->object()->datatype) {
            $this->addSettings($class);
        }

        $this->addSettings();
        foreach ($this->object()->getSettings() as $key => $val) {
            if ($el = $this->getElement($key)) {
                $el->setValue($val);
            }
        }
    }

    protected function addSettings($class = null)
    {
        if ($class === null) {
            if ($class = $this->getValue('datatype')) {
                $class::addSettingsFormFields($this);
            }
        } else {
            $class::addSettingsFormFields($this);
        }
    }

    protected function clearOutdatedSettings()
    {
        $names = array();
        $object = $this->object();
        $global = array('varname', 'description', 'caption', 'datatype');

        foreach ($this->getElements() as $el) {
            if ($el->getIgnore()) continue;
            $name = $el->getName();
            if (in_array($name, $global)) continue;
            $names[$name] = $name;
        }


        foreach ($object->getSettings() as $setting => $value) {
            if (! array_key_exists($setting, $names)) {
                unset($object->$setting);
            }
        }
    }

    public function onSuccess()
    {
        $this->clearOutdatedSettings();

        if ($class = $this->getValue('datatype')) {
            if (array_key_exists($class, $this->enumDataTypes())) {
                $this->addHidden('format', $class::getFormat());
            }
        }

        parent::onSuccess();
    }

    protected function enumDataTypes()
    {
        $hooks = Hook::all('Director\\DataType');
        $enum = array(null => '- please choose -');
        foreach ($hooks as $hook) {
            $enum[get_class($hook)] = $hook->getName();
        }

        return $enum;
    }
}
