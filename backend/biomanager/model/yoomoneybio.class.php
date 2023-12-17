<?php

/**
 * YooMoneyBio for MODX Revo (biogumus.pro)
 *
 * Payment
 *
 * @author Gregory Rosenbaum
 * @package biomanager
 * @version 0.9.0
 */

require_once MODX_CORE_PATH . 'components/biomanager/model/ymlib/autoload.php';

use YooKassa\Client;
use YooKassa\Model\PaymentInterface;
use YooKassa\Model\PaymentStatus;
use YooKassa\Model\Notification\NotificationFactory;
use YooKassa\Model\NotificationEventType;

class YoomoneyBio
{
    const MODULE_VERSION = '0.9.0';

    private modX $modx;

    /** @var int Оплата через ЮMoney вообще не используется */
    const MODE_NONE = 0;

    /** @var int Оплата производится через ЮKassa */
    const MODE_KASSA = 1;

    /** @var int Оплата производится через ЮMoney */
    const MODE_MONEY = 2;

    /** @var int Какой способ оплаты используется, одна из констант MODE_XXX */
    private int $mode;
    private array $settings;

    private Client $client;

    public int $orderId;
    public string $orderTotal;
    public object $order;
    public int $userId;
    public string $phone;
    public string $email;

    public int $orderStatus;
    public PaymentStatus $paymentStatus;
    public object $ymPayment;

    public string $successUrl;
    public string $returnUrl;
    public string $failUrl;

    public string $comment;

    public string $shopid;
    public string $password;


    function __construct(modX &$modx, $settings = array())
    {
        $this->modx = &$modx;
        $this->mode = 1;
        $this->client = new Client();
        $this->client->setAuth($settings['shopid'], $settings['password']);
        $this->settings = $settings;
        $this->modx->addPackage('BioManager', MODX_CORE_PATH . "components/biomanager/model/");
        $this->modx->addPackage('shopkeeper3', MODX_CORE_PATH . "components/shopkeeper3/model/");
    }

    private function getValueFromContacts($order, $key)
    {
        $newval = null;

        foreach (json_decode($order->get('contacts'), 1) as $item) {
            if ($item['name'] === $key) {
                $newval = $item['value'];
                break;
            }
        }
        return $newval;
    }

    private function prepare($orderId): bool
    {

        if ($orderId == 0) return false;

        $this->orderId = $orderId;

        $order = $this->modx->getObject('shk_order', array('id' => $orderId));
        $this->order = $order;

        if (!$order) return false;

        $this->orderStatus = $order->get('status');
        // $cont = ($orderStatus != $this->settings['yookassa_order_status']);

        $ymPayment = $this->modx->getObject('YMPayment', array('orderid' => $orderId));

        if (!$ymPayment) {
            $ymPayment = $this->modx->newObject('YMPayment');
            $ymPayment->set('orderid', $orderId);
            $ymPayment->set('paymentid', "");
            //$ymPayment->save();
        }

        $this->ymPayment = $ymPayment;

        $this->phone = $this->getValueFromContacts($order, 'phone');
        $this->email = $this->getValueFromContacts($order, 'email');
        // $pay_method = $order->get('payment');

        $this->userId = $order->get('userid') ? $order->get('userid') : 0;
        $newPrice = $this->getValueFromContacts($order, 'newprice');
        $price = $newPrice ?: $order->get('price');
        $this->orderTotal = floatval(str_replace(array(',', ' '), array('.', ''), $price));
        $this->comment = $this->getValueFromContacts($order, 'message');

        $_host = str_replace(array('http://', 'https://'), '', $this->modx->config['site_url']);
        $host = 'https://' . $_host . 'assets/components/biomanager/payment_connector.php';

        $this->returnUrl = $host . '?return=1&order_id=' . $orderId;
        $this->successUrl = $host . '?success=1&order_id=' . $orderId;
        $this->failUrl = $host . '?fail=1&order_id=' . $orderId;

        return true;

    }

    private function generateRequest(): array
    {

        $clientIP = $_SERVER['REMOTE_ADDR'];
        if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $clientIP = $_SERVER['HTTP_X_FORWARDED_FOR'];
        }

        $customer = array(
            'full_name' => $this->getValueFromContacts($this->order, 'fullname'),
            'email' => $this->email,
            'phone' => $this->phone
        );

        return array(
            'amount' => array(
                'value' => $this->orderTotal,
                'currency' => 'RUB',
            ),
            'confirmation' => array(
                'type' => 'redirect',
                'locale' => 'ru_RU',
                'return_url' => $this->returnUrl,
            ),
            'capture' => true,
            'description' => 'Оплата заказа №' . $this->orderId,
            'client_ip' => $clientIP,
            'metadata' => array(
                'order_id' => $this->orderId,
                'module_name' => 'biogumus.pro - yookassa - modx'
            ),
            'merchant_customer_id' => $this->userId,
            'receipt' => array(
                'customer' => $customer,
                'items' => array()
            )
        );
    }

    public function checkPayment(int $orderId): bool
    {
        $this->prepare($orderId);

        if ($this->ymPayment->get('paymentid') == "") {
            return false;
        }

        $a = $this->getPaymentById($this->ymPayment->get('paymentid'));

        if ($a == null) return false;

        if ($a->getStatus() == PaymentStatus::SUCCEEDED) {
            return true;
        } else {
            return false;
        }
    }

    public function makePayment(int $orderId)
    {
        $this->prepare($orderId);

        if ($this->orderStatus == $this->settings['yookassa_order_status']) {
            $this->modx->sendRedirect($this->successUrl, array('responseCode' => 'HTTP/1.1 303 Redirect to Success page'));
            return;
        }

        if ($this->ymPayment->get('paymentid') != "") {
            $a = $this->getPaymentById($this->ymPayment->get('paymentid'));
            if ($a) {
                if ($a->getStatus() == PaymentStatus::SUCCEEDED) {
                    $this->modx->sendRedirect($this->successUrl, array('responseCode' => 'HTTP/1.1 303 Redirect to Success page'));
                    return;
                }
            }
        }

        try {
            $request = $this->generateRequest();
            $idempotenceKey = uniqid('', true);
            $response = $this->client->createPayment($request, $idempotenceKey);
            $confirmationUrl = $response->getConfirmation()->getConfirmationUrl();
            $this->ymPayment->set('paymentid', $response->getId());
            $this->ymPayment->save();
            $this->modx->sendRedirect($confirmationUrl, array('responseCode' => 'HTTP/1.1 303 Redirect to payment'));

        } catch (Exception $e) {
            //$response = $e;
            $this->modx->sendRedirect($this->failUrl, array('responseCode' => 'HTTP/1.1 303 Redirect to Fail page'));
            return;
        }
    }

    public function getAllPaid(): array
    {
        $cursor = null;
        $params = array(
            'limit' => 100,
            'status' => PaymentStatus::SUCCEEDED,
        );

        $out = array();

        try {
            do {
                $params['cursor'] = $cursor;
                $payments = $this->client->getPayments($params);
                foreach ($payments->getItems() as $payment) {
                    $orderNumber = $payment->getMetadata()['order_id'] ? $payment->getMetadata()['order_id'] : $payment->getMetadata()['orderNumber'];
                    $order = $this->modx->getObject('shk_order', array('id' => $orderNumber));
                    $order->set('status', $this->settings['yookassa_order_status']);
                    $order->save();

                    $out[] = array(
                        'date' => $payment->getCreatedAt()->format('Y-m-d H:i:s'),
                        'status' => $payment->getStatus(),
                        'orderNumber' => $orderNumber,
                        'id' => $payment->getId()
                    );
                }
            } while ($cursor = $payments->getNextCursor());
        } catch (\Exception $e) {
            $response = $e;
            var_dump($response);
        }
        return $out;
    }


    /**
     * @param string $paymentId
     * @return PaymentInterface|null
     */
    public function getPaymentById(string $paymentId): ?PaymentInterface
    {
        try {
            $payment = $this->client->getPaymentInfo($paymentId);
        } catch (Exception $e) {
            self::log('error', 'Failed to find payment ' . $paymentId);
            $payment = null;
        }
        return $payment;
    }

    /**
     * @param PaymentInterface $payment
     * @param bool $fetch
     * @return PaymentInterface|null
     */
    public function capturePayment(PaymentInterface $payment, bool $fetch = true): ?PaymentInterface
    {
        if ($fetch) {
            $payment = $this->getPaymentById($payment->getId());
            if ($payment === null) {
                return null;
            }
        }
        if ($payment->getStatus() === PaymentStatus::WAITING_FOR_CAPTURE) {
            try {
                $builder = \YooKassa\Request\Payments\Payment\CreateCaptureRequest::builder();
                $builder->setAmount($payment->getAmount());
                $request = $builder->build();
            } catch (Exception $e) {
                return null;
            }
            try {
                $response = $this->client->capturePayment($request, $payment->getId());
            } catch (\Exception $e) {
                return null;
            }
        } else {
            $response = $payment;
        }
        return $response;
    }

    /**
     * @return int
     */
    public function getMode(): int
    {
        return $this->mode;
    }

    /**
     * Устанавливает новый статус исполнения заказа
     * @param object $order Инстанс изменяемого заказа
     * @param string $status Новый статус заказа
     * @return void
     */
    public function updateOrderStatus(object $order, string $status)
    {
        if ($status > 0) {
            $order->set('status', $status);
            $order->save();
        }
    }

    /**
     * dd
     * @return void
     */
    public function updateOrderPaid()
    {

        $this->order->set('status', $this->settings['yookassa_order_status']);
        $this->order->save();

    }


    /**
     * @param $level
     * @param $message
     * @param array $context
     */
    public static function log($level, $message, array $context = array())
    {
        if (!empty($context) && (is_array($context) || $context instanceof Traversable)) {
            $search = array();
            $replace = array();
            foreach ($context as $key => $value) {
                $search[] = '{' . $key . '}';
                $replace[] = $value;
            }
            $message = str_replace($search, $replace, $message);
        }
        $path = MODX_CORE_PATH . 'components/biomanager/logs';
        if (!file_exists($path)) {
            mkdir($path);
        }
        $fileName = $path . '/module.log';
        $fd = fopen($fileName, 'a');
        flock($fd, LOCK_EX);
        fwrite($fd, date(DATE_ATOM) . ' [' . $level . '] - ' . $message . PHP_EOL);
        flock($fd, LOCK_UN);
        fclose($fd);
    }

    public function getNotificationFactory(): NotificationFactory
    {
        return new NotificationFactory();
    }

}
