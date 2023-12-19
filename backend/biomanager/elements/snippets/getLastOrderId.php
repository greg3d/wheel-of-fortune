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

            $purchases = $order->getMany('shk_purchases', array('order_id' => $orderId));


            $winobj = $wheel['sectors'][$winner - 1];

            $purchase = $modx->newObject('shk_purchases', array(

                'p_id' => 10000 + $winobj['realid'],
                'order_id' => $orderId,
                'name' => "ПРИЗ: " . $winobj['name'],
                'price' => 0,
                'count' => 1,
                'data' => "",
                'class_name' => 'ShopContent',
                'package_name' => 'biomanager'

            ));
            $purchase->save();

            $contacts = json_decode($order->get('contacts'), 1);
            $contacts[] = array('name'=>'wheeldone', 'value'=>'1', 'label'=>"Прошел розыгрыш?");
            $order->set('contacts', json_encode($contacts));
            $order->save();

            //Параметры сниппета Shopkeeper3
            $snippet_properties = array();
            $response = $modx->runProcessor(
                'getsnippetproperties',
                array(),
                array('processors_path' => $modx->getOption('core_path') . 'components/shopkeeper3/processors/mgr/')
            );
            if (!$response->isError()) {
                $snippet_properties = $response->getObject();
            }

            require_once MODX_CORE_PATH . "components/shopkeeper3/model/shopkeeper.class.php";
            $shopCart = new Shopkeeper($modx, $snippet_properties);
            $orderOutputData = $shopCart->getOrderData($orderId);

            require_once MODX_CORE_PATH . 'components/biomanager/model/biomanager.class.php';
            $bm = new BioManager($modx, array());

            $np = $bm->getValueFromContacts($order, 'newprice');
            $op = $bm->getValueFromContacts($order, 'oldprice');

            $body = array(
                'orderID' => $orderId,
                'orderDate' => $order->get('sentdate'),
                'orderPriceDiffer' => $np == $op ? '0' : '1',
                'orderPrice' => $op,
                'orderNewPrice' => $np,
                'orderOldPrice' => $op,
                'orderOutputData' => $orderOutputData
            );

            $bm->sendTemplateMail($body, 'orderEmailReportPrize', "Обновлен заказ №" . $orderId . " Вы выиграли приз!", $order->get('email'));
            //$bm->sendTemplateMail($body, 'orderEmailReportPrize', "Выигрыш по заказу №" . $orderId . ": " . $winobj['name'] , 'bgumus@mail.ru');
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
        $fullname = "";
        $wheelDone = 0;
        $contacts = json_decode($order->get('contacts'), 1);

        foreach ($contacts as $option) {
            if ($option['name'] == 'fullname') {
                $fullname = $option['value'];
            }

            if ($option['name'] == 'wheeldone') {
                $wheelDone = $option['value'];
            }
        }

        if ($order->get('payment') == "AC" && $order->get('status') == 6 && $wheelDone != 1) {
            $modx->regClientScript(MODX_ASSETS_URL . 'components/biomanager/js/main.85eaa64c.js');
            $modx->regClientCSS(MODX_ASSETS_URL . 'components/biomanager/css/main.ac0f2982.css');
            $message = "Ваш заказ оформлен и оплачен!";
            $carousel = "<h4>Выиграй бесплатный приз к заказу!</h4><p>Чтобы выиграть случайный приз, нажмите на кнопку \"крутить\". Он будет добавлен к вашему оплаченному заказу!</p><div id=\"BioWheelReactRoot\"></div>";
        }

        $out = array(
            'name' => $fullname,
            'order_id' => $order->get('id'),
            'payment' => $order->get('payment'),
            'message' => $message,
            'carousel' => $carousel
        );
    }

    $modx->toPlaceholders($out, 'success');
    return;
}