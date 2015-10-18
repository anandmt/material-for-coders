<?php

function register_m4c_settings() {
    register_setting( 'm4c-settings', 'footer-phrase' );
    register_setting( 'm4c-settings', 'color-scheme' );
	register_setting( 'm4c-settings', 'sidebar-active' );
}


function m4c_admin_menu() {
	add_theme_page( 'Material for coders', 'Material for coders', 'manage_options', 'm4c-settings', 'm4c_settings' );
}

function m4c_settings() {
	if ( !current_user_can( 'manage_options' ) )  {
		wp_die( 'You do not have sufficient permissions to access this page.' );
	}
    ?>
	<div class="wrap">
        <h2>Material for coders settings</h2>
        <br />

        <form method="post" action="options.php">

            <?PHP settings_fields( 'm4c-settings' ); ?>
            <?PHP do_settings_sections( 'm4c-settings' ); ?>
            <hr />
            <h3><span class="dashicons dashicons-admin-appearance"></span> Color scheme:</h3>
            <?PHP
            $schemes = array(
                array('Pink', '#E2105D', '#b20d49', '#fbc3d8'),
                array('Turquoise', '#33b9d3', '#2597ae', '#dbf3f7'),
                array('Green', '#18B576', '#128859', '#a6f3d4'),
            );
            $default = $schemes[0][0];
            $selected = get_option('color-scheme');
            $selected_valid = false;
            foreach( $schemes as $scheme ):
                if($scheme[0] === $selected):
                    $selected_valid = true;
                endif;
            endforeach;
            $selected = ($selected_valid ? $selected : $default);
            ?>
            <style>
            label.scheme-label{
                display: inline-block;
                padding: 5px;
                background: #FFF;
                border: 2px solid transparent;
            }
            input[type="radio"][name="color-scheme"]{
                display: none;
            }
            input[type="radio"][name="color-scheme"]:checked + label {
                border: 2px solid #2ED8A7;
            }
            </style>
            <?PHP foreach( $schemes as $scheme ): ?>
                <input type="radio" id="scheme-<?PHP echo $scheme[0]?>" name="color-scheme" value="<?PHP echo $scheme[0]?>" <?PHP if($selected == $scheme[0]): ?>checked<?PHP endif; ?>>
                <label class="scheme-label" for="scheme-<?PHP echo $scheme[0]?>">
                    <div style="display: inline-block; width: 100px; height: 50px; background-color: <?PHP echo $scheme[1]?>;"></div><!--
                    --><div style="display: inline-block; width: 50px; height: 50px; background-color: <?PHP echo $scheme[2]?>;"></div><!--
                    --><div style="display: inline-block; width: 50px; height: 50px; background-color: <?PHP echo $scheme[3]?>;"></div>
                    <div>
                    <?PHP echo $scheme[0]?>
                    </div>
                </label>
            <?PHP endforeach; ?>

			<hr >

            <h3><span class="dashicons dashicons-format-quote"></span> Footer phrase (usually the license):</h3>
            <p><input style="width: 80%;" type="text" name="footer-phrase" value="<?php echo esc_attr( get_option('footer-phrase') ); ?>" /></p>

			<hr />

			<?php
			# Get the option
			$sidebar_active = intval(get_option('sidebar-active'));
			$sidebar = ($sidebar_active == 1);
			?>

            <h3><span class="dashicons dashicons-admin-appearance"></span> Sidebar:</h3>
			<ul>
				<li><label><input type="radio" name="sidebar-active" value="1" <?php echo ($sidebar ? 'checked' : '');?> /> With sidebar</label></li>
				<li><label><input type="radio" name="sidebar-active" value="0" <?php echo ($sidebar ? '' : 'checked');?> /> Without sidebar</label></li>
			</ul>

			<hr />


            <!-- https://wordpress.org/plugins/advanced-custom-fields/ -->

            <h3><span class="dashicons dashicons-align-right"></span> Portfolio:</h3>

            <?php $acf_activated = is_plugin_active('advanced-custom-fields/acf.php'); ?>

            <?php if($acf_activated): ?>
                <p>
                    Portfolio enabled. Remember that you must maintain the
                    <a href="https://wordpress.org/plugins/advanced-custom-fields/" target="_blank">Advanced Custom Fields</a>
                    plugin activated.
                </p>
            <?php else: ?>
                <p>
                    To use the portfolio features you need to have the
                    <a href="https://wordpress.org/plugins/advanced-custom-fields/" target="_blank">Advanced Custom Fields</a>
                    plugin activated.
                </p>
            <?php endif; ?>

			<hr />

			<?PHP
			# Get the current version:
			{
				$theme = wp_get_theme();
				$version_current = $theme->Version;
			}

			# Get latest version:
			{

				if(isset($_GET['update_check'])):
					$latest_style_url = 'https://raw.githubusercontent.com/emmgfx/material-for-coders/master/style.css';
					$latest_style_content = file_get_contents($latest_style_url);

					foreach(preg_split("/((\r?\n)|(\r\n?))/", $latest_style_content) as $line){
						if (strpos($line, 'Version') === false) {
							# Version is not in this line.
						} else {
							$version_latest = trim(str_replace('Version:', '', $line));
							break;
						}
					}
				endif;
			}
			?>


			<h3><span class="dashicons dashicons-update"></span> Updater</h3>

			<?php if(isset($_GET['update_check'])): ?>
				<?php $updated = version_compare($version_current, $version_latest); ?>
				<?php if($updated == -1): ?>
					<p><span class="dashicons dashicons-warning"></span> <strong>You have an outdated version</strong> (<?php echo $version_current; ?>). <a href="themes.php?page=m4c-settings&updater">Update to <?php echo $version_latest; ?></a>.</p>

				<?php elseif($updated == 0): ?>
					<p><span class="dashicons dashicons-yes"></span> You have the latest version, <?php echo $version_current; ?>.</p>

				<?php elseif($updated == 1): ?>
					<p>Mmm... something weird has happened... do you have a more recent version than the latest?</p>

				<?php endif; ?>
			<?php elseif(isset($_GET['updater'])): ?>

				<ul>
					<li>
						Downloading...
						<?php $downloaded = download_latest();?>
					</li>
					<li>
						<?php echo ($downloaded ? 'Download complete' : 'Error downloading'); ?>
					</li>
					<?php if($downloaded): ?>
						<li>
							Unpacking...
							<?php $unpacked = unpack_downloaded(); ?>
						</li>
						<li>
							<?php echo ($unpacked ? 'Unpacked' : 'Error unpacking'); ?>
						</li>
						<?php if($unpacked): ?>
							<li>
								Installing...
								<?php $installed = install_unpacked(); ?>
							</li>
							<li>
								<?php echo ($unpacked ? 'Install complete.' : 'Error installing'); ?>
							</li>
						<?php endif; ?>
					<?php endif; ?>
				</ul>


			<?php else: ?>
				<p>I don't like the Wordpress update system, so I prepared an
					auto update against the GitHub repository. You don't have
					to download and override the theme files, only click on
					the link down here to check for updates.</p>
				<p><a href="themes.php?page=m4c-settings&update_check">Check for update</a></p>
			<?php endif; ?>

			<br />
			<hr />
			<br />

            <?php submit_button(); ?>

        </form>
	</div>
    <?PHP
}

if ( is_admin() ){
    add_action( 'admin_menu', 'm4c_admin_menu' );
    add_action( 'admin_init', 'register_m4c_settings' );
}

?>