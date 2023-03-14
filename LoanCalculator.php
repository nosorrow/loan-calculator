<?php
/*
Plugin Name: Loan Calculator
Description: Кредитен калкулатор
Version: 1.0.2
Author: p.d.p
*/
defined("ABSPATH") or die("No script kiddies please!");

class LoanCalculator
{
    public function __construct()
    {
        add_action("wp_enqueue_scripts", [$this, "lc_register_scripts"]);
        add_action("init", [$this, "lc_add_shortcode"]);
        add_action("admin_init", [$this, "loan_calculator_activate"]);
        add_action('admin_init', [$this, 'loan_calculator_register_settings']);
        add_action('admin_menu', [$this, 'loan_calculator_add_options_page']);

	    add_filter( 'plugin_action_links_loan-calculator/LoanCalculator.php',
            [$this, 'plugin_settings_link'] );

    }

	function plugin_settings_link( $links ) {
		$settings_link = '<a href="' . admin_url( 'options-general.php?page=loan-calculator' ) . '">Settings</a>';
		array_unshift( $links, $settings_link );

		return $links;
	}

    public function lc_register_scripts()
    {
        wp_enqueue_style("lc_calculator", plugins_url("/assets/css/lc_style.css", __FILE__), false, "1.0.0");
    }

    public function lc_add_shortcode()
    {
        add_shortcode("loan_calculator", [$this, "lc_display_form_html"]);
    }

    public function lc_display_form_html()
    {
        return include plugin_dir_path(__FILE__) . "/lc_html.php";
    }

    public function loan_calculator_activate()
    {
        $options = get_option('loan_calculator_options');

        if ($options === false) {
            $options = [
                'min_loan_amount' => 500,
                'max_loan_amount' => 25000,
                'interest_rate' => 5,
                'max_interest_rate' => 15,
                'min_loan_term' => 3,
                'max_loan_term' => 60,
                'apply_show'=>1,
                'apply_url' => site_url(),
                'image_url'=>plugins_url('images/accounting.png', __FILE__)
            ];

            add_option('loan_calculator_options', $options);
        }
    }

    public function loan_calculator_options_page()
    {
        if (isset($_GET['settings-updated']) && $_GET['settings-updated']) {
            ?>
            <div class="updated">
                <h3>Промените са записани успешно!</h3>
            </div>
            <?php
        }

        ?>
        <div class="wrap">
            <h1>Кредитен калкулатор - настройки</h1>
            <form method="post" action="options.php">
                <?php
                settings_fields('loan_calculator_options');
                do_settings_sections('loan_calculator_options');
                ?>
                <table class="form-table">
                    <tr>
                        <th scope="row">Мин. сума на кредит:</th>
                        <td>
                            <input type="number" name="loan_calculator_options[min_loan_amount]"
                                   value="<?php echo esc_attr(get_option('loan_calculator_options')['min_loan_amount']); ?>"
                                   required></td>
                    </tr>
                    <tr>
                        <th scope="row">Макс. сума на кредит:</th>
                        <td>
                            <input type="number" name="loan_calculator_options[max_loan_amount]"
                                   value="<?php echo esc_attr(get_option('loan_calculator_options')['max_loan_amount']); ?>"
                                   required></td>
                    </tr>
                    <tr>
                        <th scope="row">Мин. Лихвен %:</th>
                        <td>
                            <input type="number" name="loan_calculator_options[interest_rate]"
                                   value="<?php echo esc_attr(get_option('loan_calculator_options')['interest_rate']); ?>"
                                   required></td>
                    </tr>
                    <tr>
                        <th scope="row">Макс. Лихвен %:</th>
                        <td>
                            <input type="number" name="loan_calculator_options[max_interest_rate]"
                                   value="<?php echo esc_attr(get_option('loan_calculator_options')['max_interest_rate']); ?>"
                                   required></td>
                    </tr>
                    <tr>
                        <th scope="row">Мин. Период (в месеци):</th>
                        <td>
                            <input type="number" name="loan_calculator_options[min_loan_term]"
                                   value="<?php echo esc_attr(get_option('loan_calculator_options')['min_loan_term']); ?>"
                                   required></td>
                    </tr>
                    <tr>
                        <th scope="row">Макс. Период (в месеци):</th>
                        <td>
                            <input type="number" name="loan_calculator_options[max_loan_term]"
                                   value="<?php echo esc_attr(get_option('loan_calculator_options')['max_loan_term']); ?>"
                                   required></td>
                    </tr>
                    <tr>
                        <th scope="row">Покажи бутон "Кандидатствай" (URL):</th>
                        <td>
                            <input type="checkbox" name="loan_calculator_options[apply_show]"
                                   <?= isset(get_option('loan_calculator_options')['apply_show']) ? 'checked': '' ?>
                                   value="1">
                            <span class="description" id="home-description">
                                Изберете дали бутонът "Кандидатствай" ще се показва във вашата страница.
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Линк за бутон "Кандидатствай" (URL):</th>
                        <td>
                            <input type="text" name="loan_calculator_options[apply_url]"
                                   value="<?php echo esc_attr(get_option('loan_calculator_options')['apply_url']); ?>"
                                   size="80">
                            <p class="description" id="home-description">
                                Въведете URL адрес, към който ще бъдете препратени при натискане на бутона.
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Линк за иконка:</th>
                        <td>
                            <input type="text" name="loan_calculator_options[image_url]"
                                   value="<?php echo esc_attr(get_option('loan_calculator_options')['image_url']); ?>"
                                   size="80"
                                   id="image-url-input"
                            >
                            <p class="description" id="home-description">
                                Въведете URL адрес, който може да копирате от Файлове!
                                <br>URL по подразбиране:
                                <a id="image-url" href="#"><?= plugins_url('images/accounting.png', __FILE__) ?></a>
                                <script>
                                    const spanElement = document.getElementById('image-url');
                                    spanElement.addEventListener('click', function(e) {
                                        e.preventDefault();
                                        const text = spanElement.textContent;
                                        const inputElement = document.getElementById('image-url-input');
                                        inputElement.value = text;
                                    });
                                </script>
                            </p>
                        </td>
                    </tr>
                </table>
                <?php submit_button(); ?>
            </form>
        </div>
        <?php
    }

    public function loan_calculator_add_options_page()
    {
        add_menu_page(
            'Loan Calculator',
            'Кредитен калкулатор',
            'manage_options',
            'loan-calculator',
            [$this, 'loan_calculator_options_page'],
            'dashicons-calculator'
        );
    }

    public function loan_calculator_register_settings()
    {
        register_setting(
            'loan_calculator_options',
            'loan_calculator_options'
        );
    }

    public function activate()
    {
        flush_rewrite_rules();
    }

    public function deactivate()
    {
        delete_option('loan_calculator_options');
        flush_rewrite_rules();
    }
}

$loanCalculator = new LoanCalculator();

register_activation_hook(__FILE__, [$loanCalculator, "activate"]);
register_deactivation_hook(__FILE__, [$loanCalculator, "deactivate"]);
