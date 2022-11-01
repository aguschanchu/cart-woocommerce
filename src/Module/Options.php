<?php

namespace MercadoPago\Woocommerce\Module;

class Options
{
    const CREDENTIALS_PUBLIC_KEY_PROD = '_mp_public_key_prod';
    const CREDENTIALS_PUBLIC_KEY_TEST = '_mp_public_key_test';
    const CREDENTIALS_ACCESS_TOKEN_PROD = '_mp_access_token_prod';
    const CREDENTIALS_ACCESS_TOKEN_TEST = '_mp_access_token_test';
    const CHECKOUT_COUNTRY = 'checkout_country';
    const STORE_ID = '_mp_store_identificator';
    const STORE_NAME = 'mp_statement_descriptor';
    const STORE_CATEGORY = '_mp_category_id';
    const INTEGRATOR_ID = '_mp_integrator_id';
    const DEBUG_MODE = '_mp_debug_mode';
    const CUSTOM_DOMAIN = '_mp_custom_domain';
    const CHECKBOX_CHECKOUT_TEST_MODE = 'checkbox_checkout_test_mode';
    const CHECKBOX_CHECKOUT_PRODUCTION_MODE = 'checkbox_checkout_production_mode';
    const WOOCOMMERCE_COUNTRY = 'woocommerce_default_country';
    const HOMOLOG_VALIDATE = 'homolog_validate';
    const APPLICATION_ID = 'mp_application_id';
    const SITE_ID = '_site_id_v1';
    const CLIENT_ID = '_mp_client_id';

    protected string $credentialsPublicKeyProd;
    protected string $credentialsPublicKeyTest;
    protected string $credentialsAccessTokenProd;
    protected string $credentialsAccessTokenTest;
    protected string $checkoutCountry;
    protected string $storeId;
    protected string $storeName;
    protected string $storeCategory;
    protected string $integratorId;
    protected string $debugMode;
    protected string $customDomain;
    protected string $checkboxCheckoutTestMode;
    protected string $checkboxCheckoutProductionMode;
    protected string $woocommerceCountry;
    protected string $homologValidate;
    protected string $applicationId;
    protected string $siteId;
    protected string $clientId;

    public static Options $instance;

    public function __construct()
    {
        $this->credentialsPublicKeyProd = get_option(self::CREDENTIALS_PUBLIC_KEY_PROD);
        $this->credentialsPublicKeyTest = get_option(self::CREDENTIALS_PUBLIC_KEY_TEST);
        $this->credentialsAccessTokenProd = get_option(self::CREDENTIALS_ACCESS_TOKEN_PROD);
        $this->credentialsAccessTokenTest = get_option(self::CREDENTIALS_ACCESS_TOKEN_TEST);
        $this->checkoutCountry = get_option(self::CHECKOUT_COUNTRY);
        $this->storeId = get_option(self::STORE_ID);
        $this->storeName = get_option(self::STORE_NAME);
        $this->storeCategory = get_option(self::STORE_CATEGORY);
        $this->integratorId = get_option(self::INTEGRATOR_ID);
        $this->debugMode = get_option(self::DEBUG_MODE);
        $this->customDomain = get_option(self::CUSTOM_DOMAIN);
        $this->checkboxCheckoutTestMode = get_option(self::CHECKBOX_CHECKOUT_TEST_MODE);
        $this->checkboxCheckoutProductionMode = get_option(self::CHECKBOX_CHECKOUT_PRODUCTION_MODE);
        $this->woocommerceCountry = get_option(self::WOOCOMMERCE_COUNTRY);
        $this->homologValidate = get_option(self::HOMOLOG_VALIDATE);
        $this->applicationId = get_option(self::APPLICATION_ID);
        $this->siteId = get_option(self::SITE_ID);
        $this->clientId = get_option(self::CLIENT_ID);
    }

    public static function getInstance(): Options
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function __get($name)
    {
        return $this->{$name};
    }
}
