<?php

namespace MercadoPago\Woocommerce\Gateways;

use MercadoPago\Woocommerce\Helpers\Form;
use MercadoPago\Woocommerce\Helpers\Numbers;
use MercadoPago\Woocommerce\Transactions\CustomTransaction;
use MercadoPago\Woocommerce\Transactions\WalletButtonTransaction;

if (!defined('ABSPATH')) {
    exit;
}

class CustomGateway extends AbstractGateway
{
    /**
     * @const
     */
    public const ID = 'woo-mercado-pago-custom';

    /**
     * @const
     */
    public const CHECKOUT_NAME = 'checkout-custom';

    /**
     * @const
     */
    public const WEBHOOK_API_NAME = 'WC_WooMercadoPago_Custom_Gateway';

    /**
     * @const
     */
    public const LOG_SOURCE = 'MercadoPago_CustomGateway';

    /**
     * CustomGateway constructor
     */
    public function __construct()
    {
        parent::__construct();

        $this->adminTranslations = $this->mercadopago->adminTranslations->customGatewaySettings;
        $this->storeTranslations = $this->mercadopago->storeTranslations->customCheckout;

        $this->id    = self::ID;
        $this->icon  = $this->mercadopago->gateway->getGatewayIcon('icon-blue-card');
        $this->title = $this->mercadopago->store->getGatewayTitle($this, $this->adminTranslations['gateway_title']);

        $this->init_settings();
        $this->init_form_fields();
        $this->payment_scripts($this->id);

        $this->description        = $this->adminTranslations['gateway_description'];
        $this->method_title       = $this->adminTranslations['gateway_method_title'];
        $this->method_description = $this->adminTranslations['gateway_method_description'];
        $this->discount           = $this->getActionableValue('gateway_discount', 0);
        $this->commission         = $this->getActionableValue('commission', 0);

        $this->mercadopago->gateway->registerUpdateOptions($this);
        $this->mercadopago->gateway->registerGatewayTitle($this);
        $this->mercadopago->gateway->registerThankYouPage($this->id, [$this, 'renderInstallmentsRateDetails']);

        $this->mercadopago->order->registerOrderDetailsAfterOrderTable([$this, 'renderInstallmentsRateDetails']);
        $this->mercadopago->order->registerAdminOrderTotalsAfterTotal([$this, 'registerCommissionAndDiscountOnAdminOrder']);

        $this->mercadopago->currency->handleCurrencyNotices($this);
        $this->mercadopago->endpoints->registerApiEndpoint(self::WEBHOOK_API_NAME, [$this, 'webhook']);
        $this->mercadopago->checkout->registerReceipt($this->id, [$this, 'renderOrderForm']);
    }

    /**
     * Get checkout name
     *
     * @return string
     */
    public function getCheckoutName(): string
    {
        return self::CHECKOUT_NAME;
    }

    /**
     * Init form fields for checkout configuration
     *
     * @return void
     */
    public function init_form_fields(): void
    {
        if ($this->addMissingCredentialsNoticeAsFormField()) {
            return;
        }
        parent::init_form_fields();

        $this->form_fields = array_merge($this->form_fields, [
            'header' => [
                'type'        => 'mp_config_title',
                'title'       => $this->adminTranslations['header_title'],
                'description' => $this->adminTranslations['gateway_description'],
            ],
            'card_homolog_validate' => $this->getHomologValidateNoticeOrHidden(),
            'card_settings' => [
                'type'  => 'mp_card_info',
                'value' => [
                    'title'       => $this->adminTranslations['card_settings_title'],
                    'subtitle'    => $this->adminTranslations['card_settings_subtitle'],
                    'button_text' => $this->adminTranslations['card_settings_button_text'],
                    'button_url'  => $this->links['admin_settings_page'],
                    'icon'        => 'mp-icon-badge-info',
                    'color_card'  => 'mp-alert-color-success',
                    'size_card'   => 'mp-card-body-size',
                    'target'      => '_self',
                ],
            ],
            'enabled' => [
                'type'         => 'mp_toggle_switch',
                'title'        => $this->adminTranslations['enabled_title'],
                'subtitle'     => $this->adminTranslations['enabled_subtitle'],
                'default'      => 'no',
                'descriptions' => [
                    'enabled'  => $this->adminTranslations['enabled_descriptions_enabled'],
                    'disabled' => $this->adminTranslations['enabled_descriptions_disabled'],
                ],
            ],
            'title' => [
                'type'        => 'text',
                'title'       => $this->adminTranslations['title_title'],
                'description' => $this->adminTranslations['title_description'],
                'default'     => $this->adminTranslations['title_default'],
                'desc_tip'    => $this->adminTranslations['title_desc_tip'],
                'class'       => 'limit-title-max-length',
            ],
            'card_info_helper' => [
                'type'  => 'title',
                'value' => '',
            ],
            'card_info_fees' => [
                'type'  => 'mp_card_info',
                'value' => [
                    'title'       => $this->adminTranslations['card_info_fees_title'],
                    'subtitle'    => $this->adminTranslations['card_info_fees_subtitle'],
                    'button_text' => $this->adminTranslations['card_info_fees_button_url'],
                    'button_url'  => $this->links['mercadopago_costs'],
                    'icon'        => 'mp-icon-badge-info',
                    'color_card'  => 'mp-alert-color-success',
                    'size_card'   => 'mp-card-body-size',
                    'target'      => '_blank',
                ],
            ],
            'currency_conversion' => [
                'type'         => 'mp_toggle_switch',
                'title'        => $this->adminTranslations['currency_conversion_title'],
                'subtitle'     => $this->adminTranslations['currency_conversion_subtitle'],
                'default'      => 'no',
                'descriptions' => [
                    'enabled'  => $this->adminTranslations['currency_conversion_descriptions_enabled'],
                    'disabled' => $this->adminTranslations['currency_conversion_descriptions_disabled'],
                ],
            ],
            'wallet_button' => [
                'type'         => 'mp_toggle_switch',
                'title'        => $this->adminTranslations['wallet_button_title'],
                'subtitle'     => $this->adminTranslations['wallet_button_subtitle'],
                'default'      => 'yes',
                'after_toggle' => $this->getWalletButtonPreview(),
                'descriptions' => [
                    'enabled'  => $this->adminTranslations['wallet_button_descriptions_enabled'],
                    'disabled' => $this->adminTranslations['wallet_button_descriptions_disabled'],
                ],
            ],
            'advanced_configuration_title' => [
                'type'  => 'title',
                'title' => $this->adminTranslations['advanced_configuration_title'],
                'class' => 'mp-subtitle-body',
            ],
            'advanced_configuration_description' => [
                'type'  => 'title',
                'title' => $this->adminTranslations['advanced_configuration_subtitle'],
                'class' => 'mp-small-text',
            ],
            'binary_mode' => [
                'type'         => 'mp_toggle_switch',
                'title'        => $this->adminTranslations['binary_mode_title'],
                'subtitle'     => $this->adminTranslations['binary_mode_subtitle'],
                'default'      => 'no',
                'descriptions' => [
                    'enabled'  => $this->adminTranslations['binary_mode_descriptions_enabled'],
                    'disabled' => $this->adminTranslations['binary_mode_descriptions_disabled'],
                ],
            ],
            'gateway_discount' => $this->getDiscountField(),
            'commission'       => $this->getCommissionField(),
        ]);
    }

    /**
     * Added gateway scripts
     *
     * @param string $gatewaySection
     *
     * @return void
     */
    public function payment_scripts(string $gatewaySection): void
    {
        parent::payment_scripts($gatewaySection);

        if ($this->canCheckoutLoadScriptsAndStyles()) {

            $this->mercadopago->scripts->registerCheckoutScript(
                'wc_mercadopago_security_session',
                $this->mercadopago->url->getPluginFileUrl('assets/js/checkouts/custom/session', '.js')
            );

            $this->mercadopago->scripts->registerCheckoutScript(
                'wc_mercadopago_sdk',
                'https://sdk.mercadopago.com/js/v2'
            );

            $this->mercadopago->scripts->registerCheckoutScript(
                'wc_mercadopago_custom_page',
                $this->mercadopago->url->getPluginFileUrl('assets/js/checkouts/custom/mp-custom-page', '.js')
            );

            $this->mercadopago->scripts->registerCheckoutScript(
                'wc_mercadopago_custom_elements',
                $this->mercadopago->url->getPluginFileUrl('assets/js/checkouts/custom/mp-custom-elements', '.js')
            );

            $this->mercadopago->scripts->registerCheckoutScript(
                'wc_mercadopago_custom_checkout',
                $this->mercadopago->url->getPluginFileUrl('assets/js/checkouts/custom/mp-custom-checkout', '.js'),
                [
                    'public_key'         => $this->mercadopago->seller->getCredentialsPublicKey(),
                    'intl'               => $this->countryConfigs['intl'],
                    'site_id'            => $this->countryConfigs['site_id'],
                    'currency'           => $this->countryConfigs['currency'],
                    'theme'             => get_stylesheet(),
                    'location'          => '/checkout',
                    'plugin_version'    => MP_VERSION,
                    'platform_version'  => $this->mercadopago->woocommerce->version,
                    'cvvText'           => $this->storeTranslations['cvv_text'],
                    'installmentObsFee' => $this->storeTranslations['installment_obs_fee'],
                    'installmentButton' => $this->storeTranslations['installment_button'],
                    'bankInterestText'  => $this->storeTranslations['bank_interest_text'],
                    'interestText'      => $this->storeTranslations['interest_text'],
                    'placeholders' => [
                        'issuer'             => $this->storeTranslations['placeholders_issuer'],
                        'installments'       => $this->storeTranslations['placeholders_installments'],
                        'cardExpirationDate' => $this->storeTranslations['placeholders_card_expiration_date'],
                    ],
                    'cvvHint' => [
                        'back'  => $this->storeTranslations['cvv_hint_back'],
                        'front' => $this->storeTranslations['cvv_hint_front'],
                    ],
                    'input_helper_message' => [
                        'cardNumber' => [
                            'invalid_type'   => $this->storeTranslations['input_helper_message_invalid_type'],
                            'invalid_length' => $this->storeTranslations['input_helper_message_invalid_length'],
                        ],
                        'cardholderName' => [
                            '221' => $this->storeTranslations['input_helper_message_card_holder_name_221'],
                            '316' => $this->storeTranslations['input_helper_message_card_holder_name_316'],
                        ],
                        'expirationDate' => [
                            'invalid_type'   => $this->storeTranslations['input_helper_message_expiration_date_invalid_type'],
                            'invalid_length' => $this->storeTranslations['input_helper_message_expiration_date_invalid_length'],
                            'invalid_value'  => $this->storeTranslations['input_helper_message_expiration_date_invalid_value'],
                        ],
                        'securityCode' => [
                            'invalid_type'   => $this->storeTranslations['input_helper_message_security_code_invalid_type'],
                            'invalid_length' => $this->storeTranslations['input_helper_message_security_code_invalid_length'],
                        ]
                    ],
                    'threeDsText' => [
                        'title_loading'             => $this->mercadopago->storeTranslations->threeDsTranslations['title_loading_3ds_frame'],
                        'title_loading2'             => $this->mercadopago->storeTranslations->threeDsTranslations['title_loading_3ds_frame2'],
                        'text_loading'       => $this->mercadopago->storeTranslations->threeDsTranslations['text_loading_3ds_frame'],
                        'title_loading_response' => $this->mercadopago->storeTranslations->threeDsTranslations['title_loading_3ds_response'],
                        'title_frame' => $this->mercadopago->storeTranslations->threeDsTranslations['title_3ds_frame'],
                        'tooltip_frame' => $this->mercadopago->storeTranslations->threeDsTranslations['tooltip_3ds_frame'],
                        'message_close' => $this->mercadopago->storeTranslations->threeDsTranslations['message_3ds_declined'],
                    ],
                ]
            );
        }

        $this->mercadopago->checkout->registerReviewOrderBeforePayment(function () {
            $this->mercadopago->scripts->registerCheckoutScript(
                'wc_mercadopago_custom_update_checkout',
                $this->mercadopago->url->getPluginFileUrl('assets/js/checkouts/custom/mp-custom-update-checkout', '.js')
            );
        });
    }

    /**
     * Render gateway checkout template
     *
     * @return void
     */
    public function payment_fields(): void
    {
        $this->mercadopago->template->getWoocommerceTemplate(
            'public/checkouts/custom-checkout.php',
            [
                'test_mode'                        => $this->mercadopago->store->isTestMode(),
                'test_mode_title'                  => $this->storeTranslations['test_mode_title'],
                'test_mode_description'            => $this->storeTranslations['test_mode_description'],
                'test_mode_link_text'              => $this->storeTranslations['test_mode_link_text'],
                'test_mode_link_src'               => $this->links['docs_integration_test'],
                'wallet_button'                    => $this->mercadopago->options->getGatewayOption($this, 'wallet_button', 'yes'),
                'wallet_button_image'              => $this->mercadopago->url->getPluginFileUrl("assets/images/icons/icon-logos", '.png', true),
                'wallet_button_title'              => $this->storeTranslations['wallet_button_title'],
                'wallet_button_description'        => $this->storeTranslations['wallet_button_description'],
                'wallet_button_button_text'        => $this->storeTranslations['wallet_button_button_text'],
                'available_payments_title_icon'    => $this->mercadopago->url->getPluginFileUrl("assets/images/icons/icon-purple-card", '.png', true),
                'available_payments_title'         => $this->storeTranslations['available_payments_title'],
                'available_payments_image'         => $this->mercadopago->url->getPluginFileUrl("assets/images/checkouts/custom/chevron-down", '.png', true),
                'available_payments_chevron_up'    => $this->mercadopago->url->getPluginFileUrl("assets/images/checkouts/custom/chevron-up", '.png', true),
                'available_payments_chevron_down'  => $this->mercadopago->url->getPluginFileUrl("assets/images/checkouts/custom/chevron-down", '.png', true),
                'payment_methods_items'            => wp_json_encode($this->getPaymentMethodsContent()),
                'payment_methods_promotion_link'   => $this->links['mercadopago_debts'],
                'payment_methods_promotion_text'   => $this->storeTranslations['payment_methods_promotion_text'],
                'site_id'                          => $this->mercadopago->seller->getSiteId() ?: $this->mercadopago->country::SITE_ID_MLA,
                'card_form_title'                  => $this->storeTranslations['card_form_title'],
                'card_number_input_label'          => $this->storeTranslations['card_number_input_label'],
                'card_number_input_helper'         => $this->storeTranslations['card_number_input_helper'],
                'card_holder_name_input_label'     => $this->storeTranslations['card_holder_name_input_label'],
                'card_holder_name_input_helper'    => $this->storeTranslations['card_holder_name_input_helper'],
                'card_expiration_input_label'      => $this->storeTranslations['card_expiration_input_label'],
                'card_expiration_input_helper'     => $this->storeTranslations['card_expiration_input_helper'],
                'card_security_code_input_label'   => $this->storeTranslations['card_security_code_input_label'],
                'card_security_code_input_helper'  => $this->storeTranslations['card_security_code_input_helper'],
                'card_document_input_label'        => $this->storeTranslations['card_document_input_label'],
                'card_document_input_helper'       => $this->storeTranslations['card_document_input_helper'],
                'card_installments_title'          => $this->storeTranslations['card_installments_title'],
                'card_issuer_input_label'          => $this->storeTranslations['card_issuer_input_label'],
                'card_installments_input_helper'   => $this->storeTranslations['card_installments_input_helper'],
                'terms_and_conditions_description' => $this->storeTranslations['terms_and_conditions_description'],
                'terms_and_conditions_link_text'   => $this->storeTranslations['terms_and_conditions_link_text'],
                'terms_and_conditions_link_src'    => $this->links['mercadopago_terms_and_conditions'],
                'amount'                           => $this->getAmount(),
                'currency_ratio'                   => $this->mercadopago->currency->getRatio($this),
            ]
        );
    }

    /**
     * Process payment and create woocommerce order
     *
     * @param $order_id
     *
     * @return array
     */
    public function process_payment($order_id): array
    {
        $order    = wc_get_order($order_id);
        try {
            $checkout = Form::sanitizeFromData($_POST['mercadopago_custom']);

            if ($checkout['is_3ds']) {
                return [
                    'result'   => 'success',
                    'redirect' => esc_url($order->get_checkout_order_received_url()),
                ];
            }
            
            parent::process_payment($order_id);

            switch ($checkout['checkout_type']) {
                case 'wallet_button':
                    $this->mercadopago->logs->file->info('Preparing to render wallet button checkout', self::LOG_SOURCE);

                    return [
                        'result'   => 'success',
                        'redirect' => $this->mercadopago->url->setQueryVar(
                            'wallet_button',
                            'open',
                            $order->get_checkout_payment_url(true)
                        ),
                    ];

                default:
                    $this->mercadopago->logs->file->info('Preparing to get response of custom checkout', self::LOG_SOURCE);

                    if (
                        !empty($checkout['token']) &&
                        !empty($checkout['amount']) &&
                        !empty($checkout['paymentMethodId']) &&
                        !empty($checkout['installments']) && $checkout['installments'] !== -1
                    ) {
                        $this->transaction = new CustomTransaction($this, $order, $checkout);
                        $response          = $this->transaction->createPayment();

                        $this->mercadopago->orderMetadata->setCustomMetadata($order, $response);

                        return $this->handleResponseStatus($order, $response, $checkout);
                    }
                    throw new \Exception('Invalid checkout data');
            }
        } catch (\Exception $e) {
            return $this->processReturnFail(
                $e,
                $this->mercadopago->storeTranslations->commonMessages['cho_default_error'],
                self::LOG_SOURCE,
                (array) $order,
                true
            );
        }

        return $this->processReturnFail(
            new \Exception('Couldn\'t process payment'),
            $this->mercadopago->storeTranslations->commonMessages['cho_default_error'],
            self::LOG_SOURCE,
            (array) $order,
            true
        );
    }

    /**
     * Generating Wallet Button preview component
     *
     * @return string
     */
    public function getWalletButtonPreview(): string
    {
        return $this->mercadopago->template->getWoocommerceTemplateHtml(
            'admin/components/preview.php',
            [
                'settings' => [
                    'url'         => $this->getWalletButtonPreviewUrl(),
                    'description' => $this->adminTranslations['wallet_button_preview_description'],
                ],
            ]
        );
    }

    /**
     * Get wallet button preview url
     *
     * @return string
     */
    private function getWalletButtonPreviewUrl(): string
    {
        $locale = substr(strtolower(get_locale()), 0, 2);

        if ($locale !== 'pt' && $locale !== 'es') {
            $locale = 'en';
        }

        return $this->mercadopago->url->getPluginFileUrl(
            "assets/images/gateways/wallet-button/preview-$locale",
            '.png',
            true
        );
    }

    /**
     * Get payment methods to fill in the available payments content
     *
     * @return array
     */
    private function getPaymentMethodsContent(): array
    {
        $debitCard      = [];
        $creditCard     = [];
        $paymentMethods = [];
        $cards          = $this->mercadopago->seller->getCheckoutBasicPaymentMethods();

        foreach ($cards as $card) {
            switch ($card['type']) {
                case 'credit_card':
                    $creditCard[] = [
                        'src' => $card['image'],
                        'alt' => $card['name'],
                    ];
                    break;

                case 'debit_card':
                case 'prepaid_card':
                    $debitCard[] = [
                        'src' => $card['image'],
                        'alt' => $card['name'],
                    ];
                    break;

                default:
                    break;
            }
        }

        if (count($creditCard) != 0) {
            $paymentMethods[] = [
                'title'           => $this->storeTranslations['available_payments_credit_card_title'],
                'label'           => $this->storeTranslations['available_payments_credit_card_label'],
                'payment_methods' => $creditCard,
            ];
        }

        if (count($debitCard) != 0) {
            $paymentMethods[] = [
                'title'           => $this->storeTranslations['available_payments_debit_card_title'],
                'payment_methods' => $debitCard,
            ];
        }

        return $paymentMethods;
    }

    /**
     * Render order form
     *
     * @param $order_id
     */
    public function renderOrderForm($order_id): void
    {
        if ($this->mercadopago->url->validateQueryVar('wallet_button')) {
            $order             = wc_get_order($order_id);
            $this->transaction = new WalletButtonTransaction($this, $order);
            $preference        = $this->transaction->createPreference();

            $this->mercadopago->template->getWoocommerceTemplate(
                'public/receipt/wallet-button.php',
                [
                    'public_key'          => $this->mercadopago->seller->getCredentialsPublicKey(),
                    'preference_id'       => $preference['id'],
                    'wallet_button_title' => $this->storeTranslations['wallet_button_order_receipt_title'],
                    'cancel_url'          => $order->get_cancel_order_url(),
                    'cancel_url_text'     => $this->storeTranslations['cancel_url_text'],
                ]
            );
        }
    }

    /**
     * Render thank you page
     *
     * @param $order_id
     */
    public function renderInstallmentsRateDetails($order_id): void
    {
        $order             = wc_get_order($order_id);
        $installments      = $this->mercadopago->orderMetadata->getInstallmentsMeta($order);
        $installmentAmount = $this->mercadopago->orderMetadata->getTransactionDetailsMeta($order);
        $transactionAmount = $this->mercadopago->orderMetadata->getTransactionAmountMeta($order);
        $totalPaidAmount   = $this->mercadopago->orderMetadata->getTotalPaidAmountMeta($order);
        $totalDiffCost     = (float) $totalPaidAmount - (float) $transactionAmount;

        if ($totalDiffCost > 0) {
            $this->mercadopago->template->getWoocommerceTemplate(
                'public/order/custom-order-received.php',
                [
                    'title_installment_cost'  => $this->storeTranslations['title_installment_cost'],
                    'title_installment_total' => $this->storeTranslations['title_installment_total'],
                    'text_installments'       => $this->storeTranslations['text_installments'],
                    'currency'                => $this->countryConfigs['currency_symbol'],
                    'total_paid_amount'       => Numbers::format($totalPaidAmount),
                    'transaction_amount'      => Numbers::format($transactionAmount),
                    'total_diff_cost'         => Numbers::format($totalDiffCost),
                    'installment_amount'      => Numbers::format($installmentAmount),
                    'installments'            => Numbers::format($installments),
                ]
            );
        }
    }

    /**
     * Handle with response status
     * The order_pay page always redirect the requester, so we must stop the current execution to return a JSON.
     * See mp-custom-checkout.js to understand how to handle the return.
     *
     * @param $return
     */
    private function handlePayForOrderRequest($return)
    {
        if (!headers_sent()) {
            header('Content-Type: application/json;');
        }
        echo wp_json_encode($return);
        die();
    }

    /**
     * Check if there is a pay_for_order query param.
     * This indicates that the user is on the Order Pay Checkout page.
     *
     * @return bool
     */
    private function isOrderPayPage(): bool
    {
        return $this->mercadopago->url->validateGetVar('pay_for_order');
    }

    /**
     * Handle with response status
     *
     * @param $order
     * @param $response
     * @param $checkout
     *
     * @return array
     */
    private function handleResponseStatus($order, $response, $checkout): array
    {
        try {
            if (is_array($response) && array_key_exists('status', $response)) {
                switch ($response['status']) {
                    case 'approved':
                        $this->mercadopago->woocommerce->cart->empty_cart();

                        $urlReceived = esc_url($order->get_checkout_order_received_url());
                        $orderStatus = $this->mercadopago->orderStatus->getOrderStatusMessage('accredited');

                        $this->mercadopago->notices->storeApprovedStatusNotice($orderStatus);
                        $this->mercadopago->orderStatus->setOrderStatus($order, 'failed', 'pending');

                        $return = [
                            'result'   => 'success',
                            'redirect' => $urlReceived,
                        ];

                        if ($this->isOrderPayPage()) {
                            $this->handlePayForOrderRequest($return);
                        }

                        return $return;

                    case 'pending':
                    case 'in_process':
                        $statusDetail = $response['status_detail'];
                        
                        if ($statusDetail === 'pending_challenge') {
                            $this->mercadopago->session->setSession('mp_3ds_url', $response['three_ds_info']['external_resource_url']);
                            $this->mercadopago->session->setSession('mp_3ds_creq', $response['three_ds_info']['creq']);
                            $this->mercadopago->session->setSession('mp_order_id', $order->ID);
                            $this->mercadopago->session->setSession('mp_payment_id', $response['id']);
                            $lastFourDigits = (empty($response['card']['last_four_digits'])) ? '****' : $response['card']['last_four_digits'];

                            $return = [
                                'result'        => 'success',
                                'three_ds_flow' => true,
                                'redirect'      => false,
                                'messages'      => '<script>load3DSFlow(' . $lastFourDigits . ');</script>',
                            ];

                            if ($this->isOrderPayPage()) {
                                $this->handlePayForOrderRequest($return);
                            }

                            return $return;
                        }
                        
                        $this->mercadopago->woocommerce->cart->empty_cart();

                        $checkoutType = $checkout['checkout_type'];
                        $linkText     = $this->mercadopago->storeTranslations->commonMessages['cho_form_error'];

                        $urlReceived = esc_url($order->get_checkout_order_received_url());
                        $orderStatus = $this->mercadopago->orderStatus->getOrderStatusMessage($statusDetail);

                        $this->mercadopago->notices->storePendingStatusNotice(
                            $orderStatus,
                            $urlReceived,
                            $checkoutType,
                            $linkText
                        );

                        $return = [
                            'result'   => 'success',
                            'redirect' => $order->get_checkout_payment_url(true),
                        ];

                        if ($this->isOrderPayPage()) {
                            $this->handlePayForOrderRequest($return);
                        }

                        return $return;

                    case 'rejected':
                        $urlReceived     = esc_url($order->get_checkout_payment_url());
                        $urlRetryPayment = esc_url($order->get_checkout_payment_url(true));

                        $checkoutType = $checkout['checkout_type'];
                        $noticeTitle  = $this->mercadopago->storeTranslations->commonMessages['cho_payment_declined'];
                        $orderStatus  = $this->mercadopago->orderStatus->getOrderStatusMessage($response['status_detail']);
                        $linkText     = $this->mercadopago->storeTranslations->commonMessages['cho_button_try_again'];

                        $this->mercadopago->notices->storeRejectedStatusNotice(
                            $noticeTitle,
                            $orderStatus,
                            $urlReceived,
                            $checkoutType,
                            $linkText
                        );

                        $return = [
                            'result'   => 'success',
                            'redirect' => $urlRetryPayment,
                        ];

                        if ($this->isOrderPayPage()) {
                            $this->handlePayForOrderRequest($return);
                        }

                        return $return;

                    default:
                        break;
                }
            }
            throw new \Exception('Response status not mapped on ' . __METHOD__);
        } catch (\Exception $e) {
            return $this->processReturnFail(
                $e,
                $this->mercadopago->storeTranslations->commonMessages['cho_form_error'],
                self::LOG_SOURCE,
                (array) $response
            );
        }
    }

    /**
     * Register commission and discount on admin order totals
     *
     * @param int $orderId
     *
     * @return void
     */
    public function registerCommissionAndDiscountOnAdminOrder(int $orderId): void
    {
        parent::registerCommissionAndDiscount($this, $orderId);
    }
}
