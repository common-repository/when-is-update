<?php
/**
 * Plugin Name: When Is Update
 * Plugin URI: https://whenisupdate.com
 * Description: Provides a dynamic image banner for whenisupdate.com.
 * Version: 1.0.8
 * Author: Pauli 'Dids' Jokela
 * Author URI: http://paulijokela.com
 * License: GPL2
 */

function when_is_update($atts)
{
    extract(shortcode_atts( array(
        'align' => 'left'
    ), $atts));
	$options = get_option('when_is_update_option_name', array());
	$attribute = isset($options['attribute_link_enabled']) && $options['attribute_link_enabled'];
	return getBannerCode($attribute, $align);
}
add_shortcode('whenisupdate', 'when_is_update');

function getBannerCode($attribute = false, $align = 'left')
{
    $result = null;
    if ($attribute)
    {
        $result = '<script src="//cdnjs.cloudflare.com/ajax/libs/retina.js/2.1.0/retina.min.js"></script><a href="//whenisupdate.com" target="_blank"><img id="whenisupdate-banner" class="align'.$align.'" data-rjs=3 /></a><script>var _img=document.getElementById("whenisupdate-banner"),newImg=new Image;newImg.onload=function(){_img.src=this.src;retinajs();},newImg.src="//whenisupdate.com/banner.png?tzoffset="+(new Date).getTimezoneOffset();</script>';
    }
    else
    {
        $result = '<script src="//cdnjs.cloudflare.com/ajax/libs/retina.js/2.1.0/retina.min.js"></script><img id="whenisupdate-banner" class="align'.$align.'" data-rjs=3 /><script>var _img=document.getElementById("whenisupdate-banner"),newImg=new Image;newImg.onload=function(){_img.src=this.src;retinajs();},newImg.src="//whenisupdate.com/banner.png?tzoffset="+(new Date).getTimezoneOffset();</script>';
    }
    return $result;
}

function shortcode_button_script() 
{
    if (wp_script_is("quicktags"))
    {
        ?>
            <script type="text/javascript">
                QTags.addButton( 
                    "whenisupdate_shortcode", 
                    "When Is Update", 
                    callback
                );
                function callback()
                {
                    QTags.insertContent("[whenisupdate]");
                }
            </script>
        <?php
    }
}
add_action("admin_print_footer_scripts", "shortcode_button_script");

function enqueue_plugin_scripts($plugin_array)
{
    //enqueue TinyMCE plugin script with its ID.
    $plugin_array["whenisupdate_button_plugin"] =  plugin_dir_url(__FILE__) . "index.js";
    return $plugin_array;
}
add_filter("mce_external_plugins", "enqueue_plugin_scripts");

function register_buttons_editor($buttons)
{
    //register buttons with their id.
    array_push($buttons, "whenisupdate");
    return $buttons;
}

add_filter("mce_buttons", "register_buttons_editor");

class WhenIsUpdateSettingsPage
{
    /**
     * Holds the values to be used in the fields callbacks
     */
    private $options;

    /**
     * Start up
     */
    public function __construct()
    {
        add_action('admin_menu', array( $this, 'add_plugin_page'));
        add_action('admin_init', array( $this, 'page_init'));
    }

    /**
     * Add options page
     */
    public function add_plugin_page()
    {
        // This page will be under "Settings"
        add_options_page(
            'When Is Update Settings', 
            'When Is Update', 
            'manage_options', 
            'when-is-update-setting-admin', 
            array($this, 'create_admin_page')
        );
    }

    /**
     * Options page callback
     */
    public function create_admin_page()
    {
        // Set class property
        $this->options = get_option('when_is_update_option_name');
        ?>
        <div class="wrap">
            <h1>When Is Update (Settings)</h1>
            <br />
            <h2>Banner Preview</h2>
            <p>
            	<?php if (isset($this->options['attribute_link_enabled']) && (esc_attr($this->options['attribute_link_enabled'])))
                {
                    print getBannerCode(true, 'left');
            	}
                else
                {
                    print getBannerCode(false, 'left');
            	}
                print '<br /><br /><br />';
                ?>
            </p>
            <br />
            <form method="post" action="options.php">
            <?php
                // This prints out all hidden setting fields
                settings_fields('when_is_update_option_group');
                do_settings_sections('when-is-update-setting-admin');
                submit_button();
            ?>
            </form>
        </div>
        <?php
    }

    /**
     * Register and add settings
     */
    public function page_init()
    {
        register_setting(
            'when_is_update_option_group', // Option group
            'when_is_update_option_name', // Option name
            array($this, 'sanitize') // Sanitize
        );

        add_settings_section(
            'setting_section_id', // ID
            'Attribution', // Title
            array($this, 'print_section_info'), // Callback
            'when-is-update-setting-admin' // Page
        );

        add_settings_field(
            'attribute_link_enabled', // ID
            'Link to whenisupdate.com', // Title
            array($this, 'attribute_link_enabled_callback'), // Callback
            'when-is-update-setting-admin', // Page
            'setting_section_id' // Section
        );     
    }

    /**
     * Sanitize each setting field as needed
     *
     * @param array $input Contains all settings fields as array keys
     */
    public function sanitize($input)
    {
        $new_input = array();
        if (isset($input['attribute_link_enabled'])) $new_input['attribute_link_enabled'] = absint($input['attribute_link_enabled']);
        if (isset($input['title'])) $new_input['title'] = sanitize_text_field($input['title']);
        return $new_input;
    }

    /** 
     * Print the Section text
     */
    public function print_section_info()
    {
        print '<p>We would appreciate it if you would link back to whenisupdate.com.</p><p><i>Ticking the box below allows the banner image to link back to us, opening whenisupdate.com in a new tab or page.</i></p>';
    }

    /** 
     * Get the settings option array and print one of its values
     */
    public function attribute_link_enabled_callback()
    {
    	$checked = '';
    	if (isset($this->options['attribute_link_enabled'])) if (esc_attr($this->options['attribute_link_enabled'])) $checked = 'checked="checked"';

        printf(
            '<input type="checkbox" id="attribute_link_enabled" name="when_is_update_option_name[attribute_link_enabled]" value="1" %s />', $checked
        );
    }

    /** 
     * Get the settings option array and print one of its values
     */
    public function title_callback()
    {
        printf(
            '<input type="text" id="title" name="when_is_update_option_name[title]" value="%s" />',
            isset($this->options['title']) ? esc_attr( $this->options['title']) : ''
        );
    }
}

// Creating the widget 
class whenisupdate_widget extends WP_Widget
{
    function __construct()
    {
        parent::__construct(
            // Base ID of your widget
            'whenisupdate_widget', 

            // Widget name will appear in UI
            __('When Is Update', 'whenisupdate_widget_domain'), 

            // Widget description
            array( 'description' => __('Provides a banner image for whenisupdate.com', 'whenisupdate_widget_domain'), ) 
        );
    }

    // Creating widget front-end
    // This is where the action happens
    public function widget($args, $instance)
    {
        $title = apply_filters('widget_title', $instance['title']);
        $alignment = apply_filters('widget_alignment', $instance['alignment']);

        // before and after widget arguments are defined by themes
        echo $args['before_widget'];

        if (!empty($title)) echo $args['before_title'] . $title . $args['after_title'];

        // This is where you run the code and display the output
        echo __(whenisupdate_widget_code($alignment), 'whenisupdate_widget_domain');
        echo $args['after_widget'];
    }
            
    // Widget Backend 
    public function form($instance)
    {
        if (isset($instance['title']))
        {
            $title = $instance['title'];
        }
        else
        {
            $title = __('When Is Update', 'whenisupdate_widget_domain');
        }

        if (isset($instance['alignment']))
        {
            $alignment = $instance['alignment'];
        }
        else
        {
            $alignment = __('left', 'whenisupdate_widget_domain');
        }

        // Widget admin form
        ?>
            <p>
                <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?></label>
                <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>" />
            </p>
            <p>
                <label for="<?php echo $this->get_field_id('alignment'); ?>"><?php _e('Alignment:'); ?></label>
                <select class='widefat' id="<?php echo $this->get_field_id('alignment'); ?>" name="<?php echo $this->get_field_name('alignment'); ?>" type="text">
                    <option value='left'<?php echo ($alignment=='left')?'selected':''; ?>>
                        Left
                    </option>
                    <option value='center'<?php echo ($alignment=='center')?'selected':''; ?>>
                        Center
                    </option> 
                    <option value='right'<?php echo ($alignment=='right')?'selected':''; ?>>
                        Right
                    </option> 
                </select>
            </p>
        <?php 
    }
        
    // Updating widget replacing old instances with new
    public function update($new_instance, $old_instance)
    {
        $instance = array();
        $instance['title'] = (!empty($new_instance['title'])) ? strip_tags($new_instance['title']) : '';
        $instance['alignment'] = (!empty($new_instance['alignment'])) ? strip_tags($new_instance['alignment']) : '';
        return $instance;
    }
} // Class whenisupdate_widget ends here

function whenisupdate_widget_code($align = 'left')
{
    $options = get_option('when_is_update_option_name', array());
    $attribute = isset($options['attribute_link_enabled']) && $options['attribute_link_enabled'];
    return getBannerCode($attribute, $align);
}

// Register and load the widget
function whenisupdate_load_widget()
{
    register_widget('whenisupdate_widget');
}
add_action('widgets_init', 'whenisupdate_load_widget');

if (is_admin()) $when_is_update_settings_page = new WhenIsUpdateSettingsPage();
