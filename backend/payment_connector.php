<?php

use YooKassa\Model\PaymentStatus;

require_once dirname(__FILE__, 4) . '/config.core.php';
require_once MODX_CORE_PATH . 'config/' . MODX_CONFIG_KEY . '.inc.php';
require_once MODX_CORE_PATH . 'model/modx/modx.class.php';

$modx = new modX();
$modx->initialize('web');

// default settings
$settings = array(
    'debug_log' => 1,
    'description_template' => 'Оплата заказа №%id%',
    'fail_page_id' => 16,
    'method_ab' => 0,
    'method_cards' => 1,
    'method_sb' => 1,
    'method_ym' => 1,
    'mode' => 3, // юр лицо оплата на стороне кассы
    'password' => 'live_0zqgVDQWALpUilInJevXPuLwED0G8I0xQ3aORXHwvzM',
    'shopid' => '153256',
    'success_page_id' => 12,
    'tax_id' => 1,
    'yookassa_order_status' => 6,
    'yookassa_payment_mode' => 'full_prepayment',
    'yookassa_payment_subject' => 'commodity',
    'yookassa_send_check' => 0,
    'yookassa_send_second_receipt' => 0,
    'yookassa_send_second_receipt_status' => 0,
    'yookassa_shipping_payment_mode' => 'full_prepayment',
    'yookassa_shipping_payment_subject' => 'service'
);

$snippet = $modx->getObject('modSnippet', array('name' => 'BiogumusYooMoney'));
if ($snippet) {
    $settings = $snippet->getProperties();
} else {
    $snippet = $modx->getObject('modSnippet', array('name' => 'YooMoney'));
    if ($snippet) {
        $settings = $snippet->getProperties();
    }
}

// biogumus manager
require_once MODX_CORE_PATH . 'components/biomanager/model/yoomoneybio.class.php';

if (isset($_GET['fail']) && $_GET['fail'] == 1) {
    if ($res = $modx->getObject('modResource', $settings['fail_page_id'])) {
        $modx->sendRedirect($modx->makeUrl($settings['fail_page_id'] . '?order_id=' . $_GET['order_id'], '', '', 'full'));
    }
    exit;
} elseif (isset($_GET['success']) && $_GET['success'] == 1) {
    if ($res = $modx->getObject('modResource', $settings['success_page_id'])) {
        $modx->runSnippet('ClearCart');
        $modx->sendRedirect($modx->makeUrl($settings['success_page_id'] . '?order_id=' . $_GET['order_id'], '', '', 'full'));
    }
    exit;
} elseif (isset($_GET['getall']) && $_GET['getall'] == 1) {

    $ym = new YoomoneyBio($modx, $settings);
    $result = $ym->getAllPaid();
    print_r(json_encode($result));
    exit;

} elseif (isset($_GET['return']) && $_GET['return'] == 1) {
    $orderId = isset($_GET['order_id']) ? (int)$_GET['order_id'] : 0;
    if ($orderId !== 0) {
        $ym = new YoomoneyBio($modx, $settings);
        if ($ym->checkPayment($orderId)) {
            $ym->updateOrderPaid();
            if ($res = $modx->getObject('modResource', $settings['success_page_id'])) {
                $modx->runSnippet('ClearCart');
                $modx->sendRedirect($modx->makeUrl($settings['success_page_id'] . '?order_id=' . $orderId, '', '', 'full'));
            }
        } else {
            if ($res = $modx->getObject('modResource', $settings['fail_page_id'])) {
                $modx->sendRedirect($modx->makeUrl($settings['fail_page_id'], '', '', 'full'));
            }
        }
    }
    exit;

} elseif (isset($_GET['notification']) && $_GET['notification'] == 1) {


    $source = file_get_contents('php://input');
    $ym = new YoomoneyBio($modx, $settings);
    if (empty($source)) {
        $ym->log('notice', 'Call capture notification controller without body');
        header('HTTP/1.1 400 Empty notification object');
        return;
    }
    // $ym->log('info', 'Notification body: '.$source);
    $json = json_decode($source, true);
    if (empty($json)) {
        if (json_last_error() === JSON_ERROR_NONE) {
            $message = 'empty object in body';
        } else {
            $message = 'invalid object in body: ' . json_last_error_msg();
        }
        $ym->log('warning', 'Invalid parameters in capture notification controller - ' . $message);
        header('HTTP/1.1 400 Invalid notification object');
        return;
    }
    try {
        $notificationFactory = $ym->getNotificationFactory();
        $object = $notificationFactory->factory($json);
    } catch (\Exception $e) {
        $ym->log('error', 'Invalid notification object - ' . $e->getMessage());
        header('HTTP/1.1 500 Server error: ' . $e->getMessage());
        return;
    }
    $payment = $ym->getPaymentById($object->getObject()->getId());
    if ($payment === null) {
        $ym->log('error', 'Payment not found ');
        echo json_encode(array('success' => false, 'reason' => 'Payment not found'));
        exit();
    }
    $result = $ym->capturePayment($object->getObject());
    if (!$result) {
        header('HTTP/1.1 500 Server error 1');
        exit();
    }
    if ($result->getStatus() === PaymentStatus::SUCCEEDED) {
        try {
            $orderId = $object->getObject()->getMetadata()->offsetGet('order_id');
            $modx->addPackage('shopkeeper3', MODX_CORE_PATH . "components/shopkeeper3/model/");
            $order = $modx->getObject('shk_order', array('id' => $orderId));
            $ym->updateOrderStatus($order, $settings['yookassa_order_status']);
        } catch (Exception $e) {
            $ym->log('info', var_export($e, true));
        }
    } else {
        $ym->log('info', 'Failed');
    }

    echo json_encode(array('success' => ($result->getStatus() === PaymentStatus::SUCCEEDED)));
    exit();
}
