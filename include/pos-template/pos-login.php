<?php
/**
 * pos login
 * 
 */
defined( 'ABSPATH' ) || exit;
?>
<div id="tmd-pos-login-container" class="tmd-pos-login-form">
	<div class="tmd-pos-circles">
	  	<div class="tmd-pos-circle1"></div>
	  	<div class="tmd-pos-circle2"></div>
	</div>
	<div class="tmd-pos-login_form tmd-pos-animate">
	  	<h1><?php esc_html_e( 'Login', 'tmdpos' ); ?></h1>
		<div class="tmd-pos-container">	
			<!-- error message -->
			<div class="tmd-error"></div>
<div class="tmd_notice">
	<div class="tmd_danger_notice"><label class="tmd-login-error-message"></label></div>
</div>
							
				<label for="uname"><b><?php esc_html_e( 'Username', 'tmdpos' ); ?></b>
					<input type="text" placeholder=" <?php esc_attr_e( 'Enter Username', 'tmdpos' ); ?>" name="pos_uname" />
				</label>
				<label for="psw"><b><?php esc_html_e( 'Password', 'tmdpos' ); ?></b>
					<input type="password" placeholder=" <?php esc_attr_e( 'Enter Password', 'tmdpos' ); ?>" name="pos_upass">
				</label>
			    <label>
				  <input type="checkbox" checked="checked" name="pos_remember" value="true">
				  <?php esc_html_e( 'Remember Me', 'tmdpos' ); ?>
			    </label>
				<div class="tmd_button_row">
					  <button class="tmd-pos-button tmd-pos-user-login-btn" type="button">
						  <?php esc_html_e( 'Login', 'tmdpos' ); ?>
					 </button>
					   <a class="tmd-pos-button" href="<?php echo esc_url( home_url() ); ?>" >
						  <?php esc_html_e ( 'Return To Home', 'tmdpos' ); ?>
					   </a>
				</div>
		</div>
	</div>
</div>