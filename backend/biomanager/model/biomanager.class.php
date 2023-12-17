<?php

class BioManager
{
    /** @var modX $modx */
    public $modx;

    /**
     * @param modX $modx
     * @param array $config
     */
    function __construct(modX &$modx, array $config = [])
    {
        $this->modx =& $modx;
        $corePath = MODX_CORE_PATH . 'components/biomanager/';
        $assetsUrl = MODX_ASSETS_URL . 'components/biomanager/';

        $this->config = array_merge([
            'corePath' => $corePath,
            'modelPath' => $corePath . 'model/',
            'processorsPath' => $corePath . 'processors/',

            'connectorUrl' => $assetsUrl . 'connector.php',
            'assetsUrl' => $assetsUrl,
            'cssUrl' => $assetsUrl . 'css/',
            'jsUrl' => $assetsUrl . 'js/',
        ], $config);

        $this->modx->addPackage('biomanager', $this->config['modelPath']);
        $this->modx->lexicon->load('biomanager:default');
    }
    
    public function getPrice($id) {
        $resource = $this->modx->getObject('ShopContent', array('id' => $id));
        $price = $resource->get('price'); // Основная цена
        $price_samara = $resource->get('price_action'); // Самарская цена
        $price_dv = $resource->get('price_dv'); // Сибирский фед округ
        $result_price = $price;

        $result_price = $modx->runSnippet('geo-price2', 
            array(
                'pr1' => $price_samara,
                'pr2' => $price,
                'pr3' => $price_dv
            )
        );
        
        $dim1 = $resource->get('dim1');
        $dim2 = $resource->get('dim2');
        $dim3 = $resource->get('dim3');

        $vol = $resource->get('inventory');
        $wgt = $resource->get('weight');
        $type = $resource->get('type');
        
        $opt = false;

        $rrr = array(
            'price' => $result_price,
            'weight' => $wgt,
            'volume' => $vol,
            'length' => $dim1,
            'width' => $dim2,
            'height' => $dim3
        );

        return $rrr;
        
    }
    

}