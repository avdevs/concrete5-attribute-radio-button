<?php

namespace Concrete\Package\AttributeRadioButton;

use Concrete\Core\Backup\ContentImporter;
use Package;

class Controller extends Package
{

    protected $pkgHandle = 'attribute_radio_button';
    protected $appVersionRequired = '5.7.2';
    protected $pkgVersion = '1.0.1';

    public function getPackageName()
    {
        return t('Radio button attribute');
    }

    public function getPackageDescription()
    {
        return t('Installs a Radio button attribute');
    }

    protected function installXmlContent()
    {
        $pkg = Package::getByHandle($this->pkgHandle);

        $ci = new ContentImporter();
        $ci->importContentFile($pkg->getPackagePath() . '/install.xml');
    }

    public function install()
    {
        $pkg = parent::install();

        $this->installXmlContent();
    }

    public function upgrade()
    {
        parent::upgrade();

        $this->installXmlContent();
    }

}
