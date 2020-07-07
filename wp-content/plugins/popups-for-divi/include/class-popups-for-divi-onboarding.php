<?php
/**
 * Onboarding signup.
 *
 * Offers an onboarding signup form so new users for new users.
 * The onboarding notification is displayed only on the main Dashboard page,
 * i.e., on https://example.com/wp-admin/index.php
 *
 * @package Popups_For_Divi
 */

defined( 'ABSPATH' ) || die();

/**
 * Set up our popup integration.
 */
class Popups_For_Divi_Onboarding extends Popups_For_Divi_Component {

	/**
	 * Hook up the module.
	 *
	 * @since  1.6.0
	 * @return void
	 */
	public function setup() {
		if ( ! is_admin() ) {
			return;
		}

		// This action only fires on the main wp-admin Dashboard page.
		add_action(
			'load-index.php',
			[ $this, 'init_onboarding' ]
		);

		add_action(
			'wp_ajax_pfd_hide_onboarding',
			array( $this, 'ajax_hide_onboarding' )
		);

		add_action(
			'wp_ajax_pfd_start_course',
			array( $this, 'ajax_start_course' )
		);
	}

	/**
	 * Initialize the onboarding process.
	 *
	 * @since 1.6.0
	 * @return void
	 */
	public function init_onboarding() {
		if ( $this->show_onboarding_form() ) {
			add_action(
				'admin_notices',
				array( $this, 'onboarding_notice' ),
				1
			);
		}
	}

	/**
	 * Determine, whether to display the onboarding notice.
	 *
	 * @since 2.0.2
	 * @return bool
	 */
	private function show_onboarding_form() {
		if ( defined( 'DISABLE_NAG_NOTICES' ) && DISABLE_NAG_NOTICES ) {
			return false;
		}

		if ( ! defined( 'DIVI_POPUP_ONBOARDING_CAP' ) ) {
			// By default display the onboarding notice to all users who can
			// activate plugins (i.e. administrators).
			define( 'DIVI_POPUP_ONBOARDING_CAP', 'activate_plugins' );
		}

		$user = wp_get_current_user();

		if ( ! $user || ! $user->has_cap( DIVI_POPUP_ONBOARDING_CAP ) ) {
			return false;
		}

		if ( 'done' === $user->get( '_pfd_onboarding' ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Ajax handler: Permanently close the onboarding notice.
	 *
	 * @since 1.6.0
	 * @return void
	 */
	public function ajax_hide_onboarding() {
		// Make sure that the ajax request comes from the current WP admin site!
		if (
			! is_user_logged_in() // better safe than sorry.
			|| empty( $_POST['_wpnonce'] )
			|| ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ), 'no-onboarding' )
		) {
			wp_send_json_success( 'ERROR' );
		}

		// phpcs:ignore WordPress.VIP.RestrictedFunctions.user_meta_update_user_meta
		update_user_meta( get_current_user_id(), '_pfd_onboarding', 'done' );

		wp_send_json_success();
	}

	/**
	 * Ajax handler: Subscribe the email address to our onboarding course.
	 *
	 * Note that this ajax handler only fires for authenticated requests:
	 * We handle action `wp_ajax_pfd_start_course`.
	 * There is NO handler for `wp_ajax_nopriv_pfd_start_course`!
	 *
	 * @since 1.6.0
	 * @return void
	 */
	public function ajax_start_course() {
		// Make sure that the ajax request comes from the current WP admin site!
		if (
			! is_user_logged_in() // better safe than sorry.
			|| empty( $_POST['_wpnonce'] )
			|| ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ), 'onboarding' )
		) {
			wp_send_json_success( 'ERROR' );
		}

		$form = wp_unslash( $_POST ); // input var okay.

		$email = sanitize_email( trim( $form['email'] ) );
		$name  = sanitize_text_field( trim( $form['name'] ) );

		// Send the subscription details to our website.
		$resp = wp_remote_post(
			'https://divimode.com/wp-admin/admin-post.php',
			array(
				'headers' => [
					'Content-Type' => 'application/x-www-form-urlencoded; charset=utf-8',
				],
				'body'    => array(
					'action' => 'pfd_start_onboarding',
					'fname'  => $name,
					'email'  => $email,
				),
			)
		);

		if ( is_wp_error( $resp ) ) {
			wp_send_json_success( 'ERROR' );
		}

		$result = wp_remote_retrieve_body( $resp );
		wp_send_json_success( $result );
	}

	/**
	 * Output the onboarding notice on th wp-admin Dashboard.
	 *
	 * This function intentionally outputs inline CSS and JS - since it's only
	 * displayed once, it does not make sense to store the CSS/JS in the browsers
	 * cache.
	 *
	 * @since 1.6.0
	 * @return void
	 */
	public function onboarding_notice() {
		$user = wp_get_current_user();

		?>
		<div class="pfd-onboarding notice">
			<p class="title"><?php $this->say( __( 'Thanks for using Popups&nbsp;for&nbsp;Divi', 'divi-popup' ) ); ?> 😊</p>
			<div class="pfd-layout">
				<p class="msg"><?php $this->say( __( 'We have created a <strong>free email course</strong> to help you get the most out of Popups for Divi. <strong>Sign up now</strong>, and you will receive six emails with easy to follow instructions, lots of examples and some pretty advanced Popup techniques.', 'divi-popup' ) ); ?></p>
				<div class="form">
					<input
						type="name"
						class="name"
						placeholder="Your first name"
					/>
					<input
						type="email"
						class="email"
						placeholder="Your email address"
						value="<?php echo esc_attr( $user->user_email ); ?>"
					/>
					<button class="button-primary submit">
					<?php esc_html_e( 'Start The Course', 'divi-popup' ); ?>
					</button>
				</div>
			</div>
			<p class="privacy"><?php $this->say( __( 'Only your name and email is sent to our website. We use the information to deliver the onboarding mails. <a href="https://divimode.com/privacy/" target="_blank">Privacy&nbsp;Policy</a>', 'divi-popup' ) ); ?></p>
			<div class="loader"><span class="spinner is-active"></span></div>
			<span class="notice-dismiss"><?php esc_html_e( 'Close forever', 'divi-popup' ); ?></span>
		</div>
		<style>
			.wrap .notice.pfd-onboarding{position:relative;margin-bottom:4em;padding-bottom:0;border-left-color:#660099}
			.pfd-onboarding .title{font-weight:600;color:#000;border-bottom:1px solid #eee;padding-bottom:.5em;padding-right:100px;margin-bottom:0}
			.pfd-onboarding .form{text-align:center;position:relative;padding:.5em}
			.pfd-onboarding .privacy{font-size:.9em;text-align:center;opacity:.6;position:absolute;left:0;right:0}
			.pfd-onboarding .pfd-layout{display:flex;flex-wrap:wrap;position:relative}
			.pfd-onboarding .form:before{content:'';position:absolute;right:-9px;left:-9px;top:0;bottom:1px;background:#9944cc linear-gradient(-45deg,#660099 0%,#9944cc 100%)!important;box-shadow:0 0 0 1px #0004 inset}
			.pfd-onboarding .pfd-layout>*{flex:1 1 100%;align-self:center;z-index:10}
			.pfd-onboarding input:focus,
			.pfd-onboarding input,
			.pfd-onboarding button.button-primary,
			.pfd-onboarding button.button-primary:focus{display:block;width:80%;margin:12px auto;text-align:center;border-radius:0;height:30px;box-shadow:0 0 0 5px #fff3;outline:none;position:relative;z-index:10}
			.pfd-onboarding input:focus,
			.pfd-onboarding input{border:1px solid #0002;padding:5px 3px}
			.pfd-onboarding .notice-dismiss:before{display:none}
			.pfd-onboarding .msg{position:relative;z-index:20}
			.pfd-onboarding .msg .dismiss{float:right}
			.pfd-onboarding .msg strong{white-space:nowrap}
			.pfd-onboarding .msg .emoji{width:3em!important;height:3em!important;vertical-align:middle!important;margin-right:1em!important;float:left}
			.pfd-onboarding .loader{display:none;position:absolute;background:#fffc;z-index:50;left:0;top:0;right:0;bottom:0}
			.pfd-onboarding.loading .loader{display:block}
			.pfd-onboarding .loader .spinner{position:absolute;left:50%;top:50%;margin:0;transform:translate(-50%,-50%)}

			@media (min-width: 783px) and (max-width: 1023px) {
				.pfd-onboarding .form:before{right:-11px;left:-11px}
			}
			@media (min-width:1024px) {
				.wrap .notice.pfd-onboarding{margin-bottom:2em;padding-right:0}
				.pfd-onboarding .pfd-layout{flex-wrap:nowrap;overflow:hidden;padding:.5em 0}
				.pfd-onboarding .pfd-layout>*{flex:0 0 50%}
				.pfd-onboarding input:focus,
				.pfd-onboarding input,
				.pfd-onboarding button.button-primary,
				.pfd-onboarding button.button-primary:focus{display:inline-block;width:auto;margin:5px}
				.pfd-onboarding input:focus,
				.pfd-onboarding input{width:32%}
				.pfd-onboarding .form{position:static}
				.pfd-onboarding .form:before{width:50%;right:0;left:auto;bottom:0}
				.pfd-onboarding .form:after{content:'';position:absolute;right:50%;width:50px;height:50px;top:50%;background:#fff;transform:translate(50%,-50%) rotate(45deg) skew(20deg,20deg)}
			}
		</style>
		<script>jQuery(function(){
			var notice=jQuery('.pfd-onboarding.notice');
			var msg=notice.find('.msg');
			var email=notice.find('input.email');
			var name=notice.find('input.name');
			var submit=notice.find('.submit');
			notice.on('click','.notice-dismiss,.dismiss',dismissForever);
			notice.on('click',focusForm);
			submit.on('click',startCourse);
			name.on('keypress',maybeSubmit);
			email.on('keypress',maybeSubmit);
			function dismissForever(e){
				notice.addClass('loading');
				jQuery.post(ajaxurl,{
					action: 'pfd_hide_onboarding',
					_wpnonce: '<?php echo esc_js( wp_create_nonce( 'no-onboarding' ) ); ?>'
				},function(){
					notice.removeClass('loading');
					notice.fadeOut(400,function(){
						notice.remove();
					});
				});
			}
			function focusForm(e){
				var el=jQuery(e.target);
				var tag=el.prop('tagName');
				if('A'===tag||'INPUT'===tag||'BUTTON'===tag||el.hasClass('dismiss')||el.hasClass('notice-dismiss')){
					return;
				}
				if(name.val().trim().length<2){
					name.focus().select();
				} else if(email.val().trim().length<5){
					email.focus().select();
				} else {
					submit.focus();
				}
			}
			function maybeSubmit(e){
				if(13===e.which){
					startCourse();
					return false;
				}
			}
			function startCourse(){
				var valEmail=email.val().trim();
				var valName=name.val().trim();

				if(valName.length<2){
					name.focus().select();
					return false;
				}
				if(valEmail.length<5){
					email.focus().select();
					return false;
				}
				notice.addClass('loading');
				jQuery.post(ajaxurl,{
					action:'pfd_start_course',
					name:valName,
					email:valEmail,
					_wpnonce:'<?php echo esc_js( wp_create_nonce( 'onboarding' ) ); ?>'
				},function(res){
					notice.removeClass('loading');
					state=res&&res.data?res.data:'';
					if('OK'===state){
						msg.html("🎉 <?php $this->say( __( 'Congratulations! Please check your inbox and look for an email with the subject &quot;<strong>Just one more click for your free content!</strong>&quot; to confirm your registration.', 'divi-popup' ) ); ?>");
						msg.append("<br><a href='#' class='dismiss'><?php esc_html_e( 'Close this message', 'divi-popup' ); ?></a>");
					}else if('DUPLICATE'===state){
						msg.html("<?php esc_html_e( 'It looks like you already signed up for this course... Please check your inbox or use a different email address.', 'divi-popup' ); ?>");
					}else if('INVALID_NAME'===state){
						msg.html("<?php esc_html_e( 'Our system says, your name is invalid. Please check your input.', 'divi-popup' ); ?>");
					}else if('INVALID_EMAIL'===state){
						msg.html("<?php esc_html_e( 'Our system rejected the email address. Please check your input.', 'divi-popup' ); ?>");
					}else{
						msg.html("<?php $this->say_js( __( 'Something went wrong, but we\'re not sure what. Please reload this page and try again. If that does not work, you can contact us via the <a href="https://wordpress.org/support/plugin/popups-for-divi/" target="_blank">wp.org support forum</a>.', 'divi-popup' ) ); ?>");
					}
				});
			}
		})</script>
		<?php
	}

	/**
	 * Output text with minimal allowed HTML markup.
	 *
	 * @since 2.0.0
	 * @param string $text   The un-sanitized HTML code.
	 * @param bool   $return Whether to return the text or output it.
	 * @return void|string
	 */
	protected function say( $text, $return = false ) {
		$kses_args = [
			'strong' => [],
			'a'      => [
				'href'   => [],
				'target' => [],
			],
		];

		if ( $return ) {
			return wp_kses( $text, $kses_args );
		} else {
			echo wp_kses( $text, $kses_args );
		}
	}

	/**
	 * Output text inside a JS string/variable with minimal allowed HTML markup.
	 *
	 * @since 2.0.2
	 * @param string $text The un-sanitized HTML code.
	 * @return void
	 */
	protected function say_js( $text ) {
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo str_replace( '"', '\\"', $this->say( $text, true ) );
	}
}
