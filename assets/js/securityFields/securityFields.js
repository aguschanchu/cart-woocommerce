var cardForm;
var mercado_pago_submit, hasToken = false;
var cardFormMounted, cardFormReady, cardFormError = false;
var triggeredPaymentMethodSelectedEvent = false;

var form = document.querySelector("form[name=checkout]");
var formId = "checkout";

if (form) {
  form.id = formId;
} else {
  formId = "order_review";
}

/**
 * Handler form submit
 * @return {bool}
 */
function mercadoPagoFormHandler() {
  let formOrderReview = document.querySelector("form[id=order_review]");

  if (formOrderReview) {
    let choCustomContent = document.querySelector(
      ".mp-checkout-custom-container"
    );

    let choCustomHelpers = choCustomContent.querySelectorAll("input-helper");
    choCustomHelpers.forEach((item) => {
      let inputHelper = item.querySelector("div");
      if (inputHelper.style.display != "none") {
        removeBlockOverlay();
      }
    });
  }

  if (mercado_pago_submit) {
    return true;
  }

  if (jQuery("#mp_checkout_type").val() === "wallet_button") {
    return true;
  }

  jQuery("#mp_checkout_type").val("custom");

  if (CheckoutPage.validateInputsCreateToken() && !hasToken) {
    return createToken();
  }

  return false;
}

/**
 * Create a new token
 * @return {bool}
 */
function createToken() {
  cardForm
    .createCardToken()
    .then((cardToken) => {
      if (cardToken.token) {
        if (hasToken) return;
        document.querySelector("#cardTokenId").value = cardToken.token;
        mercado_pago_submit = true;
        hasToken = true;
        jQuery("form.checkout, form#order_review").submit();
      } else {
        throw new Error("cardToken is empty");
      }
    })
    .catch((error) => {
      console.warn("Token creation error: ", error);
    });

  return false;
}

/**
 * Init cardForm
 */
function init_cardForm() {
  var mp = new MercadoPago(wc_mercadopago_params.public_key);

  try {
    cardForm = mp.cardForm({
      amount: getAmount(),
      iframe: true,
      form: {
        id: formId,
        cardNumber: {
          id: "form-checkout__cardNumber-container",
          placeholder: "0000 0000 0000 0000",
          style: {
            "font-size": "16px",
            height: "40px",
            padding: "14px",
          },
        },
        cardholderName: {
          id: "form-checkout__cardholderName",
          placeholder: "Ex.: María López",
        },
        cardExpirationDate: {
          id: "form-checkout__expirationDate-container",
          placeholder: wc_mercadopago_params.placeholders["cardExpirationDate"],
          mode: "short",
          style: {
            "font-size": "16px",
            height: "40px",
            padding: "14px",
          },
        },
        securityCode: {
          id: "form-checkout__securityCode-container",
          placeholder: "123",
          style: {
            "font-size": "16px",
            height: "40px",
            padding: "14px",
          },
        },
        identificationType: {
          id: "form-checkout__identificationType",
        },
        identificationNumber: {
          id: "form-checkout__identificationNumber",
        },
        issuer: {
          id: "form-checkout__issuer",
          placeholder: wc_mercadopago_params.placeholders["issuer"],
        },
        installments: {
          id: "form-checkout__installments",
          placeholder: wc_mercadopago_params.placeholders["installments"],
        },
      },
      callbacks: {
        onReady: () => {
          setCustomCheckoutLoaded();
        },
        onFormMounted: function (error) {
          cardFormMounted = true;

          if (error) {
            console.log("Callback to handle the error: creating the CardForm", error);
            return;
          }
        },
        onFormUnmounted: function (error) {
          cardFormMounted = false;
          CheckoutPage.clearInputs();
          setCustomCheckoutUnloaded();

          if (error) {
            console.log("Callback to handle the error: unmounting the CardForm", error);
            return;
          }
        },
        onInstallmentsReceived: (error, installments) => {
          if (error) {
            return console.warn("Installments handling error: ", error);
          }

          CheckoutPage.setChangeEventOnInstallments(CheckoutPage.getCountry(), installments);
        },
        onCardTokenReceived: (error) => {
          if (error) {
            return console.warn("Token handling error: ", error);
          }
        },
        onPaymentMethodsReceived: (error, paymentMethods) => {
          try {
            if (paymentMethods) {
              CheckoutPage.setValue("paymentMethodId", paymentMethods[0].id);
              CheckoutPage.setCvvHint(paymentMethods[0].settings[0].security_code);
              CheckoutPage.changeCvvPlaceHolder(paymentMethods[0].settings[0].security_code.length);
              CheckoutPage.clearInputs();
              CheckoutPage.setDisplayOfError("fcCardNumberContainer", "remove", "mp-error");
              CheckoutPage.setDisplayOfInputHelper("mp-card-number", "none");
              CheckoutPage.setImageCard(paymentMethods[0].thumbnail);
              CheckoutPage.installment_amount(paymentMethods[0].payment_type_id);
              CheckoutPage.loadAdditionalInfo(paymentMethods[0].additional_info_needed);
              CheckoutPage.additionalInfoHandler(additionalInfoNeeded);
            } else {
              CheckoutPage.setDisplayOfError("fcCardNumberContainer", "add", "mp-error");
              CheckoutPage.setDisplayOfInputHelper("mp-card-number", "flex");
            }
          } catch (error) {
            CheckoutPage.setDisplayOfError("fcCardNumberContainer", "add", "mp-error");
            CheckoutPage.setDisplayOfInputHelper("mp-card-number", "flex");
          }
        },
        onSubmit: function (event) {
          event.preventDefault();
        },
        onValidityChange: function (error, field) {
          if (error) {
            let helper_message = CheckoutPage.getHelperMessage(field);
            let message = wc_mercadopago_params.input_helper_message[field][error[0].code];

            if (message) {
              helper_message.innerHTML = message;
            } else {
              helper_message.innerHTML = wc_mercadopago_params.input_helper_message[field]["invalid_length"];
            }

            if (field == "cardNumber") {
              if (error[0].code !== "invalid_length") {
                CheckoutPage.setBackground("fcCardNumberContainer", "no-repeat #fff");
                CheckoutPage.removeAdditionFields();
                CheckoutPage.clearInputs();
              }
            }

            let containerField = CheckoutPage.findContainerField(field);
            CheckoutPage.setDisplayOfError(containerField, "add", "mp-error");

            return CheckoutPage.setDisplayOfInputHelper(CheckoutPage.inputHelperName(field), "flex");
          }

          let containerField = CheckoutPage.findContainerField(field);
          CheckoutPage.setDisplayOfError(containerField, "removed", "mp-error");

          return CheckoutPage.setDisplayOfInputHelper(CheckoutPage.inputHelperName(field), "none");
        },
        onError: function (errors) {
          errors.forEach((error) => {
            removeBlockOverlay();

            if (error.message.includes("cardNumber")) {
              CheckoutPage.setDisplayOfError("fcCardNumberContainer", "add", "mp-error");
              return CheckoutPage.setDisplayOfInputHelper("mp-card-number", "flex");
            } else if (error.message.includes("cardholderName")) {
              CheckoutPage.setDisplayOfError("fcCardholderName", "add", "mp-error");
              return CheckoutPage.setDisplayOfInputHelper("mp-card-holder-name", "flex");
            } else if ( error.message.includes("expirationMonth") || error.message.includes("expirationYear")) {
              CheckoutPage.setDisplayOfError("fcCardExpirationDateContainer", "add", "mp-error" );
              return CheckoutPage.setDisplayOfInputHelper("mp-expiration-date", "flex");
            } else if (error.message.includes("securityCode")) {
              CheckoutPage.setDisplayOfError("fcSecurityNumberContainer", "add", "mp-error");
              return CheckoutPage.setDisplayOfInputHelper("mp-security-code", "flex");
            } else if (error.message.includes("identificationNumber")) {
              CheckoutPage.setDisplayOfError("fcIdentificationNumberContainer", "add", "mp-error");
              return CheckoutPage.setDisplayOfInputHelper("mp-doc-number", "flex");
            } else {
              return console.error("Unknown error on cardForm: " + error.message);
            }
          });
        },
      },
    });
  } catch(err) {
    console.error('Instance cardForm error: ', err);
  }
}

function getCustomCheckoutElements() {
  return {
    loader: document.getElementById('mp-custom-checkout-loader'),
    container: document.getElementById('mp-custom-checkout-form-container'),
  }
}

function setCustomCheckoutLoaded() {
  var customCheckoutElements = getCustomCheckoutElements();
  customCheckoutElements.loader.style.display = 'none';
  customCheckoutElements.container.style.display = 'block';
}

function setCustomCheckoutUnloaded() {
  var customCheckoutElements = getCustomCheckoutElements();
  customCheckoutElements.loader.style.display = 'flex';
  customCheckoutElements.container.style.display = 'none';
}

function getAmount() {
  const amount = parseFloat(
    document.getElementById("mp-amount").value.replace(",", ".")
  );

  const currencyRatio = parseFloat(
    document.getElementById("currency_ratio").value.replace(",", ".")
  );

  return String(amount * currencyRatio);
}

/**
 * Remove Block Overlay from Order Review page
 */
function removeBlockOverlay() {
  if (jQuery("form#order_review").length > 0) {
    jQuery(".blockOverlay").css("display", "none");
  }
}

/**
 * Manage mount and unmount the Cardform Instance
 */
function cardFormLoad() {
  if (document.getElementById("payment_method_woo-mercado-pago-custom").checked) {
    setTimeout(() => {
      if (!cardFormMounted) {
        init_cardForm();
      }
    }, 1000);
  } else {
    if (cardFormMounted) {
      cardForm.unmount();
    }
  }
}

jQuery("form.checkout").on(
  "checkout_place_order_woo-mercado-pago-custom",
  function () {
    return mercadoPagoFormHandler();
  }
);

jQuery("body").on("payment_method_selected", function () {
  if (!triggeredPaymentMethodSelectedEvent) {
    cardFormLoad();
  }
});

// If payment fail, retry on next checkout page
jQuery("form#order_review").submit(function () {
  if (document.getElementById("payment_method_woo-mercado-pago-custom").checked) {
    return mercadoPagoFormHandler();
  } else {
    cardFormLoad();
  }
});

jQuery(document.body).on("checkout_error", () => {
  hasToken = false;
  mercado_pago_submit = false;
});

if (!triggeredPaymentMethodSelectedEvent) {
  jQuery("body").trigger("payment_method_selected");
}
