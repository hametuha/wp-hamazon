<div class="wrap">

	<script>(function (d, s, id) {
		var js, fjs = d.getElementsByTagName(s)[0];
		if (d.getElementById(id)) return;
		js = d.createElement(s);
		js.id = id;
		js.src = "//connect.facebook.net/ja_JP/all.js#xfbml=1&appId=983379265125123";
		fjs.parentNode.insertBefore(js, fjs);
		}(document, 'script', 'facebook-jssdk'));</script>

	<h1 class="wp-heading-inline">
		<?php echo esc_html__( 'Hamazon Affiliate Setting', 'wp-hamazon' ); ?>
	</h1>

	<div class="hamazon-wrapper">

		<div class="hamazon-row">

			<div class="hamazon-main">
				<form method="post" action="<?php echo admin_url( 'options.php' ); ?>">
					<?php
					settings_fields( 'wp-hamazon' );
					do_settings_sections( 'wp-hamazon' );
					submit_button();
					?>
				</form>
			</div>

			<div class="hamazon-sidebar">
				<img src="<?php echo hamazon_asset_url( '/img/admin-banner.jpg' ); ?>" alt="Hamazon" class="hamazon-sidebar-banner">

				<h3 class="hamazon-sidebar-title">
					<?php esc_html_e( 'Related Links', 'wp-hamazon' ); ?>
				</h3>

				<ul class="hamazon-sidebar-list">
					<li>
						<?php
						// translators: %s is a URL,
						echo wp_kses_post( sprintf( __( 'This plugin is hosted on <a href="%s" target="_blank">github</a>. Any pull requests are welcomed!', 'wp-hamazon' ), 'https://github.com/fumikito/wp-hamazon' ) );
						?>
					</li>
					<li>
						<?php
						// translators: %s is a URL,
						echo wp_kses_post( sprintf( __( '<a href="%s" target="_blank">Gisnism.info</a> has lots of tips. Please touch it and join us.', 'wp-hamazon' ), 'https://gianism.info' ) );
						?>
					</li>
					<li>
						<?php
						// translators: %s is a URL,
						echo wp_kses_post( sprintf( __( 'Please review our plugin at <a href="%s" target="_blank">WordPress.org</a>. Feedback will grow us.', 'wp-hamazon' ), 'https://wordpress.org/support/plugin/wp-hamazon/reviews/#new-post' ) );
						?>
					</li>
				</ul>

				<hr />

				<h3 class="hamazon-sidebar-title">
					<?php esc_html_e( 'Social Links', 'wp-hamazon' ); ?>
				</h3>

				<div class="fb-page" data-href="https://www.facebook.com/gianism.info" data-small-header="true"
					data-adapt-container-width="true" data-hide-cover="false" data-show-facepile="true"
					data-show-posts="false">
					<div class="fb-xfbml-parse-ignore">
						<blockquote cite="https://www.facebook.com/gianism.info"><a
									href="https://www.facebook.com/gianism.info">Gianism</a></blockquote>
					</div>
				</div>
				<p class="social-link">
					<a href="https://twitter.com/intent/tweet?screen_name=wpGianism" class="twitter-mention-button"
						data-lang="ja" data-related="takahashifumiki">Tweet to @wpGianism</a>
					<script>!function (d, s, id) {
						var js, fjs = d.getElementsByTagName(s)[0];
						if (!d.getElementById(id)) {
							js = d.createElement(s);
							js.id = id;
							js.src = "//platform.twitter.com/widgets.js";
							fjs.parentNode.insertBefore(js, fjs);
						}
						}(document, "script", "twitter-wjs");</script>
				</p>

				<p class="hamazon-sidebar-desc description">
					<?php esc_html_e( 'Please visit our social media to make us more powerful!', 'wp-hamazon' ); ?>
				</p>

			</div>
		</div>

	</div>


</div>
