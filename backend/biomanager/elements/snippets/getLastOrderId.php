<?php
global $modx;
$source = file_get_contents('php://input');

if (!empty($source)) {

    $fetched = json_decode($source, true);
    $orderId = $_SESSION['shk_lastOrder']['id'];
    $out = array();

    switch ($fetched['action']) {

        case 'getWheel':
            $service = $modx->getService('BioManager', 'BioManager', MODX_CORE_PATH . 'components/biomanager/model/', $scriptProperties);

            // $this->modx->addPackage('BioManager', MODX_CORE_PATH . "components/biomanager/model/");

            $c = $modx->newQuery('Present');
            $c->sortby('id');

            $c->where(['active' => '1', 'koleso' => '1']);
            $c->limit(330);
            $items = $modx->getCollection('Present', $c);

            $i = 0;

            $resultArray = array();

            foreach ($items as $object) {

                $o = $object->toArray();
                $o['realid'] = $o['id'];
                $o['id'] = $i;
                $i++;
                $resultArray[] = $o;

            }

            $out = array(
                'UUID' => uniqid('o_' . $orderId . '_'),
                'sectors' => $resultArray
            );

            $_SESSION['biogumus']['wheel'] = $out;

            die(json_encode($out));

        case 'spinWheel':

            $wheel = $_SESSION['biogumus']['wheel'];

            $probabilities = array_map(function ($sector) {
                return $sector['propability'];
            }, $wheel['sectors']);

            if (array_sum($probabilities) != 100) {
                $mul = array_sum($probabilities) / 100;
                foreach ($probabilities as &$prob) {
                    $prob = $prob / $mul;
                }
            }

            $randomNumber = rand(1, 100);

            $cur = 0;

            $winner = 0;

            for ($i = 0; $i < count($probabilities); $i++) {
                $cur += $probabilities[$i];
                if ($randomNumber <= $cur) {
                    $winner = $i + 1;
                    break;
                }
            }

            $out = array(
                'sector' => $winner,
                'realid' => $wheel['sectors'][$winner - 1]['realid'],
                'UUID' => $wheel['UUID']
            );

            $modx->addPackage('shopkeeper3', MODX_CORE_PATH . "components/shopkeeper3/model/");
            $order = $modx->getObject('shk_order', array('id' => $orderId));


            $modx->log(MODX_LOG_LEVEL_ERROR, print_r($order->getMany('shk_purchases', array('order_id'=>$orderId)),1));

            die(json_encode($out));
    }

    die($out);

} else {

    $orderId = $_SESSION['shk_lastOrder']['id'];


    $message = "Ваш заказ оформлен!";
    $carousel = "";

    if ($orderId) {


        $modx->addPackage('shopkeeper3', MODX_CORE_PATH . "components/shopkeeper3/model/");
        $order = $modx->getObject('shk_order', array('id' => $orderId));

        if ($order->get('payment') == "AC" && $order->get('status') == 6) {

            $modx->regClientScript(MODX_ASSETS_URL . 'components/biomanager/js/main.85eaa64c.js');
            $modx->regClientCSS(MODX_ASSETS_URL . 'components/biomanager/css/main.7e53fd65.css');

            $message = "Ваш заказ оформлен и оплачен!";
            $carousel = "<h4>Выиграй бесплатный приз к заказу!</h4><p>Чтобы выиграть случайный приз, нажмите на кнопку \"крутить\". Он будет добавлен к вашему оплаченному заказу!</p><div id=\"BioWheelReactRoot\"></div>";
        }
        $fullname = "";

        foreach (json_decode($order->get('contacts'), 1) as $option) {
            if ($option['name'] == 'fullname') {
                $fullname = $option['value'];
            }
        }

        $out = array(
            'name' => $fullname,
            'order_id' => $order->get('id'),
            'payment' => $order->get('payment'),
            'message' => $message,
            'carousel' => $carousel
        );

    } else {
        $out = array(
            'message' => $message,
            'carousel' => $carousel
        );
    }


    $modx->toPlaceholders($out, 'success');
    return;
}