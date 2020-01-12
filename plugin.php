<?php
/*
Plugin Name: {plugin_title}
Plugin URI: #
Description:
Version: 1.0
Author:
*/

class WordPress_Plugin
{
	public $pluginTitle = '{plugin_title}';
	public $pluginSlug = '{plugin_slug}';
	public $settingsPageTitle = '{plugin_title}';

	static private $_instance = null;

    private $_options;

    private function __construct() {
        add_action( 'admin_menu', array(&$this, 'admin_menu') );
        $this->_options = get_option($this->pluginSlug.'-options', $this->default_options());
    }

    private function default_options() {
    	$default_options = array();

    	foreach ($this->options_fields() as $option) {
    		$default_options[$option['name']] = $option['default'];
		}

    	return $default_options;
	}

	private function options_fields() {
    	return [
			[
				'label' => 'How often run parsing',
				'name' => 'frequency',
				'type' => 'select',
				'options' => [
					'daily' => 'Daily',
					'twice_daily' => 'Twice Daily',
					'never' => 'Never',
				],
				'default' => 'never',
			],
    		[
    				'label' => 'Loader url',
    				'name' => 'loader_url',
    				'type' => 'text',
					'default' => '',
			],
		];
	}

	private function display_text_field($field, $value) {
		$output = '<tr>'.
					'<td>'.$field['label'].'</td>'.
					'<td><input type="text" name="'.$field['name'].'" value="'.esc_attr($value).'" class="regular-text"></td>'.
				  '</tr>';
		echo $output;
	}

	private function display_select_field($field, $value) {
		$output = '<tr>'.
					'<td>'.$field['label'].'</td>'.
					'<td><select name="'.$field['name'].'">';

		foreach ($field['options'] as $name => $label) {
			$output .= '<option value="'.$name.'" '.selected($name, $value, false).'>'.$label.'</option>';
		}

		$output .= '</select></td></tr>';

		echo $output;
	}

    public function admin_menu() {
    	add_menu_page($this->pluginTitle, $this->pluginTitle, 'manage_options', $this->pluginSlug, array(&$this, 'plugin_options'));
    //  add_submenu_page( 'tools.php', $this->pluginTitle, $this->pluginTitle, 'manage_options', $this->pluginSlug, array(&$this, 'plugin_options') );
    }

    public function plugin_options() {
        if (!current_user_can('manage_options'))
        {
            wp_die( __('You do not have sufficient permissions to access this page.') );
        }
        ?>
        <h2><?php echo $this->settingsPageTitle; ?></h2>
        <?php
        $hidden_field_name = $this->pluginSlug.'-hidden';
        $requestSent = true;
        if ( isset($_POST[ $hidden_field_name ]) && $_POST[ $hidden_field_name ] == 'Y' ) {
            $requestSent = true;

            foreach ($this->_options as $name => $value) {
            	if (array_key_exists($name, $_POST)) $this->_options[$name] = $_POST[$name];
			}

            update_option($this->pluginSlug.'-options', $this->_options);

            ?>
            <div class="updated"><p><strong><?php _e('Settings saved.'); ?></strong></p></div>
        <?php
        }
        ?>

		<form name="form1" method="post" action=""method="post" enctype="multipart/form-data">
			<input type="hidden" name="<?php echo $hidden_field_name; ?>" value="Y">

            <table id="table_keys" class="wp-list-table widefat striped">
                <tbody>
				<?php
					foreach ($this->options_fields() as $option_field) {
						$name = $option_field['name'];
						$type = $option_field['type'];
						$output_function = 'display_'.$type.'_field';
						call_user_func(array($this, $output_function), $option_field, $this->_options[$name]);
					}
				?>
                </tbody>
            </table>

            <p class="submit">
                <input type="submit" name="Compare" class="button-primary" value="<?php esc_attr_e('Submit') ?>" />
            </p>
        </form>
    <?php
    }

	static public function this() {
		if ( !self::$_instance ) {
			self::$_instance = new WordPress_Plugin();
		}

		return self::$_instance;
	}
}

WordPress_Plugin::this();
