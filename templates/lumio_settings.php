<div class="wrap">

    <h1><?php echo WLA_NAME; ?>
        <small><?php echo 'v' . WLA_VERSION; ?></small>
    </h1>

    <form method="post" action="">

        <div class="metabox-holder">

            <div class="meta-box-sortables ui-sortable">

                <div id="wla-panel-settings" class="postbox">

                    <h2><?php esc_html_e('Plugin Settings', 'wla'); ?></h2>

                    <div >

                        <div class="wla-panel-settings">

                            <table class="widefat">
                                <tr>
                                    <th><label for="wla_key"><?php esc_html_e('Lumio Tracking key',
                                                'wla') ?></label></th>
                                    <td><input id="wla_key" name="wla_key" type="text" size="40"
                                               maxlength="40" value="<?php if (isset($this->_key)) {
                                            echo esc_attr($this->_key);
                                        } ?>"></td>
                                </tr>
                                <tr>
                                    <td colspan="2" style="text-align:center"><a href="<?php echo $wla_register_url;?>"><?php esc_html_e('Sign Up', 'wla'); ?></a></td>
                                </tr>
                            </table>

                        </div>

                        <input type="submit" class="button-primary"
                               value="<?php esc_attr_e('Save Changes', 'wla'); ?>"/>

                    </div>

                </div>

            </div>

        </div>

    </form>

</div>