<?php

/**
 * Part of Woo Mercado Pago Module
 * Author - Mercado Pago
 * Developer
 * Copyright - Copyright(c) MercadoPago [https://www.mercadopago.com]
 * License - https://www.gnu.org/licenses/gpl.html GPL version 2 or higher
 *
 * @package MercadoPago
 */

if (!defined('ABSPATH')) {
	exit;
}
?>

<div class="mp-settings">
	<div class="mp-settings-header">
		<img class="mp-settings-header-img" src="<?php echo esc_url(plugins_url('../assets/images/mercadopago-settings/header-settings.png', plugin_dir_path(__FILE__))); ?>">
		<img class="mp-settings-header-logo" src="<?php echo esc_url(plugins_url('../assets/images/mercadopago-settings/mercadopago-logo.png', plugin_dir_path(__FILE__))); ?>">
		<hr class="mp-settings-header-hr" />
		<p>Aceite <b>pagamentos no ato</b> com<br />
			toda a <b>segurança</b> Mercado Pago</p>
	</div>
	<div class="mp-settings-requirements">
		<div class="mp-container">
			<div class="mp-block mp-block-requirements mp-settings-margin-right">
				<p class="mp-settings-font-color mp-settings-title-requirements-font-size">Requisitos técnicos</p>
				<div class="mp-inner-container">
					<div>
						<p class="mp-settings-font-color mp-settings-subtitle-font-size">SSL</p>
						<img class="mp-icon" src="<?php echo esc_url(plugins_url('../assets/images/mercadopago-settings/icon-info.png', plugin_dir_path(__FILE__))); ?>">
					</div>
					<div>
						<img class="mp-credential-input-success">
					</div>
				</div>
				<hr>
				<div class="mp-inner-container">
					<div>
						<p class="mp-settings-font-color mp-settings-subtitle-font-size">Extensões GD</p>
						<img class="mp-icon" src="<?php echo esc_url(plugins_url('../assets/images/mercadopago-settings/icon-info.png', plugin_dir_path(__FILE__))); ?>">
					</div>
					<div>
						<img class="mp-credential-input-success">
					</div>
				</div>
				<hr>
				<div class="mp-inner-container">
					<div>
						<p class="mp-settings-font-color mp-settings-subtitle-font-size">Curl</p>
						<img class="mp-icon" src="<?php echo esc_url(plugins_url('../assets/images/mercadopago-settings/icon-info.png', plugin_dir_path(__FILE__))); ?>">
					</div>
					<div>
						<img class="mp-credential-input-success">
					</div>
				</div>
			</div>
			<div class="mp-block mp-block-flex mp-settings-margin-left mp-settings-margin-right">
				<div class="mp-inner-container-settings">
					<div>
						<p class="mp-settings-font-color mp-settings-title-font-size">Recebimentos e parcelamento</p>
						<p class="mp-settings-font-color mp-settings-subtitle-font-size mp-settings-title-color">Escolha <b>quando quer receber o dinheiro</b> das vendas e se quer oferecer
							<b>parcelamento sem
								juros</b> aos clientes.
						</p>
					</div>
					<div>
						<button class="mp-button">Ajustar prazos e taxas </button>
					</div>
				</div>
			</div>
			<div class="mp-block mp-block-flex mp-block-manual mp-settings-margin-left">
				<div class="mp-inner-container-settings">
					<div>
						<p class="mp-settings-font-color mp-settings-title-font-size">Dúvidas?</p>
						<p class="mp-settings-font-color mp-settings-subtitle-font-size mp-settings-title-color">Revise o passo a passo de <span>como integrar o Plugin do Mercado Pago</span> no nosso site de
							desenvolvedores. </p>
					</div>
					<div>
						<button class="mp-button mp-button-light-blue"> Manual do plugin </button>
					</div>
				</div>
			</div>
		</div>
	</div>

	<hr class="mp-settings-hr" />
	<div class="mp-settings-credentials">
		<div>
			<p class="mp-settings-font-color mp-settings-title-blocks">1. Integre a loja com o Mercado Pago</p>
			<div class="mp-settings-margin-right">
				<p class="mp-settings-subtitle-font-size">Para habilitar e testar e vendas, você deve <b>copiar e colar suas credenciais abaixo.</b></p>
				<button class="mp-button mp-button-light-blue"> Consultar credenciais </button>
			</div>
			<div class="mp-container">
				<div class="mp-block mp-block-flex mp-settings-margin-right">
					<p class="mp-settings-title-font-size"><b>Credenciais de teste</b></p>
					<p class="mp-settings-label">Habilitam os checkouts Mercado Pago para testes de compras na loja.</p>
					<fieldset>
						<legend clas="mp-settings-label">Public Key</legend>
						<input class="mp-settings-input" type="text" placeholder="Cole aqui sua Public Key">
					</fieldset>
					<fieldset>
						<legend clas="mp-settings-label">Access token</legend>
						<input class="mp-settings-input" type="text" placeholder="Cole aqui seu Access Token">
					</fieldset>
				</div>
				<div class="mp-block mp-block-flex mp-settings-margin-left">
					<p class="mp-settings-title-font-size"><b>Credenciais de produção</b></p>
					<p class="mp-settings-label">Habilitam os checkouts Mercado Pago para receber pagamentos reais na loja.</p>
					<fieldset>
						<legend clas="mp-settings-label">Public Key</legend>
						<input class="mp-settings-input" type="text" placeholder="Cole aqui seu Access Token">
					</fieldset>
					<fieldset>
						<legend clas="mp-settings-label">Access token</legend>
						<input class="mp-settings-input" type="text" placeholder="Cole aqui seu Access Token">
					</fieldset>
				</div>
			</div>
			<button class="mp-button"> Salvar e continuar </button>
		</div>
	</div>
	
	<hr class="mp-settings-hr" />
	<div class="mp-settings-customize">
		<p class="mp-settings-font-color mp-settings-title-blocks">2. Personalize seu negócio</p>
		<p class="mp-settings-font-color mp-settings-subtitle-font-size mp-settings-title-color">Preencha as informações a seguir para ter uma melhor experiência e oferecer mais informações aos clientes</p>
		<div class="mp-container">
			<div class="mp-block mp-block-flex mp-settings-margin-right" style="flex-direction:column; justify-content:space-between">
				<div>
					<p class="mp-settings-title-font-size"><b>Informações sobre sua loja</b></p>
				</div>
				<div class="mp-settings-standard-margin">
					<fieldset>

						<legend class="mp-settings-label">Nome da sua loja nas faturas do cliente</legend>
						<input type="text" class="mp-settings-input" placeholder="Ex.:Loja da Maria">
						<span class="mp-settings-helper">Se o campo estiver vazio, a compra do cliente será identificada como Mercado Pago.</span>

					</fieldset>
				</div>
				<div class="mp-settings-standard-margin">
					<fieldset>

						<legend class="mp-settings-label">Identificação em Atividades do Mercado Pago</legend>
						<input type="text" class="mp-settings-input" placeholder="Ex.:Loja da Maria">
						<span class="mp-settings-helper">Nas Ativades voce verá o termo inserido antes do númer o do pedido</span>

					</fieldset>
				</div>
				<div class="mp-settings-standard-margin">
					<fieldset>

						<legend class="mp-settings-label">Nome da sua loja nas faturas do cliente</legend>
						<select name="select" class="mp-settings-input">
							<option value="valor1">Valor 1</option>
							<option value="valor2" selected>Valor 2</option>
							<option value="valor3">Valor 3</option>
						</select>
					</fieldset>
				</div>
			</div>

			<div class="mp-block mp-block-flex mp-block-manual mp-settings-margin-left">
				<div>
					<p class="mp-settings-title-font-size"><b>Opções avançadas de integração (opcional)</b></p>
				</div>
				<p class="mp-settings-font-color mp-settings-subtitle-font-size mp-settings-title-color">
					Para mais integração da sua loja com o Mercado Pago (IPN, Parceiros Certificados, Modo Debug)
				</p>
				<div>
					<p class="mp-settings-blue-text">
						Ver opções avançadas
					</p>
					<div>
						<div class="mp-settings-standard-margin">
							<fieldset>

								<legend class="mp-settings-label">URL para IPN</legend>
								<input type="text" class="mp-settings-input" placeholder="Ex.: https://examples.com/my-custom-ipn-url">
								<span class="mp-settings-helper">Insira a URL para receber notificações de pagamento. Confira mais informções nos <span class="mp-settings-blue-text"> manuais.</span>

							</fieldset>
						</div>
						<div class="mp-settings-standard-margin">
							<fieldset>

								<legend class="mp-settings-label">integrator_id</legend>
								<input type="text" class="mp-settings-input" placeholder="Ex.: 14987126498">
								<span class="mp-settings-helper">Se você é Parceiro certificado do Mercado Pago, não esqueça de inserir seu integrator_id.</span><br>
								<span class="mp-settings-helper">Se você não possui o código, <span class="mp-settings-blue-text">solicite agora<span>.</span>

							</fieldset>
						</div>
						<div class="mp-container">
							<!-- Rounded switch -->
							<div>

								<label class="mp-settings-switch">
									<input type="checkbox">
									<span class="mp-settings-slider mp-settings-round"></span>
								</label>
							</div>
							<div class="mp-settings-margin-left">
								<p class="mp-settings-subtitle-font-size mp-settings-debug">
									Modo debug e log
								</p>
								<p class="mp-settings-font-color mp-settings-subtitle-font-size mp-settings-title-color mp-settings-debug">
									Gravamos ações da sua loja para proporcionar melhor suporte.
								</p>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>

	<hr class="mp-settings-hr" />
	<div class="mp-settings-payment">
		<div>
			<p class="mp-settings-font-color mp-settings-title-blocks">3. Configure os meios de pagamento</p>
			<p class="mp-settings-subtitle-font-size">Selecione um meio de pagamento a seguir para ver mais opções</p>
		</div>

		<div class="mp-block mp-block-flex mp-settings-payment-block mp-settings-margin-right">
			<img class="" src="<?php echo esc_url(plugins_url('../assets/images/mercadopago-settings/icon-mp.png', plugin_dir_path(__FILE__))); ?>">
			<span class="mp-settings-subtitle-font-size">Checkout Pro - Pix, débito, crédito e boleto, no ambiente do Mercado Pago</span>
			<span class="mp-settings-badge-active">Ativado</span>
			<span class="">Configurar</span>
			<img class="" src="<?php echo esc_url(plugins_url('../assets/images/mercadopago-settings/icon-config.png', plugin_dir_path(__FILE__))); ?>">
		</div>

		<div class="mp-block mp-block-flex mp-settings-payment-block mp-settings-margin-right">
			<img class="" src="<?php echo esc_url(plugins_url('../assets/images/mercadopago-settings/icon-card.png', plugin_dir_path(__FILE__))); ?>">
			<span class="mp-settings-subtitle-font-size">Débito e crédito - Checkout Transparente, no ambiente da sua loja</span>
			<span class="mp-settings-badge-active">Ativado</span>
			<span class="">Configurar</span>
			<img class="" src="<?php echo esc_url(plugins_url('../assets/images/mercadopago-settings/icon-config.png', plugin_dir_path(__FILE__))); ?>">
		</div>
		<div class="mp-block mp-block-flex mp-settings-payment-block mp-settings-margin-right">
			<img class="" src="<?php echo esc_url(plugins_url('../assets/images/mercadopago-settings/icon-code.png', plugin_dir_path(__FILE__))); ?>">
			<span class="mp-settings-subtitle-font-size">Boleto e lotérica - Checkout Transparente, no ambiente da sua loja</span>
			<span class="mp-settings-badge-inactive">Ativado</span>
			<span class="">Configurar</span>
			<img class="" src="<?php echo esc_url(plugins_url('../assets/images/mercadopago-settings/icon-config.png', plugin_dir_path(__FILE__))); ?>">
		</div>
		<div class="mp-block mp-block-flex mp-settings-payment-block mp-settings-margin-right">
			<img class="" src="<?php echo esc_url(plugins_url('../assets/images/mercadopago-settings/icon-pix.png', plugin_dir_path(__FILE__))); ?>">
			<span class="mp-settings-subtitle-font-size">Pix - Checkout Transparente, no ambiente da sua loja</span>
			<span class="mp-settings-badge-inactive">Ativado</span>
			<span class="">Configurar</span>
			<img class="" src="<?php echo esc_url(plugins_url('../assets/images/mercadopago-settings/icon-config.png', plugin_dir_path(__FILE__))); ?>">
		</div>

		<button class="mp-button"> Continuar </button>
	</div>

	<hr class="mp-settings-hr" />
	<div class="mp-settings-payment">
		<div class="mp-container">
			<span class="mp-settings-font-color mp-settings-title-blocks mp-settings-margin-right">4. Teste sua loja antes de vender</span>
			<span class="mp-settings-test-mode-alert mp-settings-margin-left">Loja em modo teste</span>
		</div>
		<div>
			<p class="mp-settings-subtitle-font-size">Selecione um meio de pagamento a seguir para ver mais opções</p>
		</div>
		<p class="mp-settings-font-color mp-settings-subtitle-font-size mp-settings-title-color">Teste a experiência no Modo Teste. Depois ative o Modo Vendas (Produção) para fazer vendas.</p>

		<div class="mp-container">
			<div class="mp-block mp-block-flex" style="flex-direction:column; justify-content:space-between">
				<div>
					<p class="mp-settings-title-font-size"><b>Escolha como você quer operar sua loja:</b></p>
				</div>
				<div class="mp-container" style="margin-top: 20px;">
					<div>
						<input type="radio" class="mp-settings-radio-button">
					</div>
					<div>
						<span class="mp-settings-font-color mp-settings-subtitle-font-size mp-settings-font-color">Modo Teste</span><br>

						<span class="mp-settings-font-color mp-settings-subtitle-font-size mp-settings-title-color">Checkouts Mercado Pago inativos para cobranças reais.<span class="mp-settings-blue-text">Regras do modo teste.<span></span>
					</div>
				</div>
				<div class="mp-container mp-settings-margin-right" style="margin-top: 20px;">
					<div>
						<input type="radio" class="mp-settings-radio-button">
					</div>
					<div>
						<span class="mp-settings-font-color mp-settings-subtitle-font-size mp-settings-font-color">Modo Vendas (Produção)</span><br>

						<span class="mp-settings-font-color mp-settings-subtitle-font-size mp-settings-title-color">Mercado Pago ativos para cobranças reais.</span>
					</div>
				</div>
				<div class="mp-settings-alert-payment-methods">
					<div class="mp-settings-alert-payment-methods-orange">
					</div>
					<div class="mp-settings-helper-payment-methods mp-settings-alert-payment-methods-gray">
						<div style="width: 40px; height:40px; background:rgba(0, 0, 0, 0.04);" class="mp-settings-margin-right">

						</div>
						<div style="display:flex; flex-direction:column; justify-content:flex-start;">
							<div class="mp-settings-margin-left">
								<div>
									<span class="mp-settings-font-color mp-settings-subtitle-font-size mp-settings-font-color">Meios de pagamento Mercado Pago em Modo Teste </span>
								</div>
								<div>
									<span class="mp-settings-font-color mp-settings-font-color"><span class="mp-settings-blue-text">Visite sua loja</span> para testar compras </span>
								</div>
							</div>

						</div>
					</div>

				</div>
			</div>

		</div>
		<button class="mp-button"> Salvar Mudanças </button>

	</div>
</div>
