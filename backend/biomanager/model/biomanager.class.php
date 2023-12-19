<?php
/**
 * BioManager
 *
 * Shop, price, email and stuff for MODX Revo (biogumus.pro)
 *
 *
 * @author Gregory Rosenbaum <bitard3d@gmail.com>
 * @package biomanager
 * @version 0.9.0
 */

class BioManager
{
    /** @var modX $modx */
    public modX $modx;
    private array $config;

    /**
     * @param modX $modx
     * @param array $config
     */
    function __construct(modX &$modx, array $config = [])
    {

        $MODX_ASSETS_URL = dirname(MODX_CORE_PATH, 2) . '/assets/';

        $this->modx =& $modx;
        $corePath = MODX_CORE_PATH . 'components/biomanager/';
        $assetsUrl = (MODX_ASSETS_URL ? MODX_ASSETS_URL : $MODX_ASSETS_URL) . 'components/biomanager/';

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

    public function getValueFromContacts($order, $key)
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

    public function sendTemplateMail(array $chunkArray, string $chunkName, string $subject, string $to): bool
    {
        $mail_body = $this->modx->parseChunk($chunkName, $chunkArray);
        return $this->sendMail($subject, $mail_body, $to);
    }

    public function sendMail($subject, $body, $to): bool
    {
        if (empty($to)) return false;

        $email_from = !empty($this->modx->config['emailto']) ? $this->modx->config['emailto'] : $this->modx->config['emailsender'];

        $this->modx->getService('mail', 'mail.modPHPMailer');
        $this->modx->mail->set(modMail::MAIL_BODY, $body);
        $this->modx->mail->set(modMail::MAIL_FROM, $email_from);
        $this->modx->mail->set(modMail::MAIL_SENDER, $email_from);
        $this->modx->mail->set(modMail::MAIL_FROM_NAME, $this->modx->config['site_name']);
        $this->modx->mail->set(modMail::MAIL_SUBJECT, $subject);
        $this->modx->mail->address('to', $to);
        $this->modx->mail->setHTML(true);
        if (!$this->modx->mail->send()) {
            $this->modx->log(MODX_LOG_LEVEL_ERROR, 'An error occurred while trying to send the email: ' . $this->modx->mail->mailer->ErrorInfo);
        }
        $this->modx->mail->reset();

        return true;
    }

    public function getPrice($id): array
    {
        $resource = $this->modx->getObject('ShopContent', array('id' => $id));
        $price = $resource->get('price'); // Основная цена
        $price_samara = $resource->get('price_action'); // Самарская цена
        $price_dv = $resource->get('price_dv'); // Сибирский фед округ

        $result_price = $this->modx->runSnippet('geo-price2',
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

        return array(
            'price' => $result_price,
            'weight' => $wgt,
            'volume' => $vol,
            'length' => $dim1,
            'width' => $dim2,
            'height' => $dim3,
            'type' => $type
        );

    }


}