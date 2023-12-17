<?php

/**
 * The home manager controller for BioManager.
 *
 */
class BioManagerHomeManagerController extends modExtraManagerController
{
    /** @var BioManager $BioManager */
    public $BioManager;


    /**
     *
     */
    public function initialize()
    {
        $this->BioManager = $this->modx->getService('BioManager', 'BioManager', MODX_CORE_PATH . 'components/biomanager/model/');
        parent::initialize();
    }


    /**
     * @return array
     */
    public function getLanguageTopics()
    {
        return ['biomanager:default'];
    }


    /**
     * @return bool
     */
    public function checkPermissions()
    {
        return true;
    }


    /**
     * @return null|string
     */
    public function getPageTitle()
    {
        return $this->modx->lexicon('biomanager');
    }


    /**
     * @return void
     */
    public function loadCustomCssJs()
    {
        $this->addCss($this->BioManager->config['cssUrl'] . 'mgr/main.css');
        $this->addJavascript($this->BioManager->config['jsUrl'] . 'mgr/biomanager.js');
        $this->addJavascript($this->BioManager->config['jsUrl'] . 'mgr/misc/utils.js');
        $this->addJavascript($this->BioManager->config['jsUrl'] . 'mgr/misc/combo.js');
        $this->addJavascript($this->BioManager->config['jsUrl'] . 'mgr/widgets/items.grid.js');
        $this->addJavascript($this->BioManager->config['jsUrl'] . 'mgr/widgets/items.windows.js');
        $this->addJavascript($this->BioManager->config['jsUrl'] . 'mgr/widgets/home.panel.js');
        $this->addJavascript($this->BioManager->config['jsUrl'] . 'mgr/sections/home.js');

        $this->addHtml('<script type="text/javascript">
        BioManager.config = ' . json_encode($this->BioManager->config) . ';
        BioManager.config.connector_url = "' . $this->BioManager->config['connectorUrl'] . '";
        Ext.onReady(function() {MODx.load({ xtype: "biomanager-page-home"});});
        </script>');
    }


    /**
     * @return string
     */
    public function getTemplateFile()
    {
        $this->content .= '<div id="biomanager-panel-home-div"></div>';

        return '';
    }
}