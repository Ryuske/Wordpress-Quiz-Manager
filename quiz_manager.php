<?php
/*
Plugin Name: Quiz Manager
Description: A simple quiz manager, and generator.
Version: 1.0
Author: Kenyon Haliwell
License: GPL2
 */

class quiz_manager {
    private $plugin_info = array(
        'database_version' => '1.0',
        'table_name' => 'quiz_manager'
    );
    public $questions = array();

    function __construct() {
        wp_enqueue_script('jquery');
        wp_enqueue_script('jquery-ui-core');
        wp_enqueue_script('jquery-ui-tabs');
        wp_enqueue_script('jquery-ui-dialog');
        wp_register_style('black-tie', plugins_url('application/view/css/jquery-ui.css', __FILE__));
        wp_register_script('quizManagerScript', plugins_url('application/view/js/admin.js', __FILE__));

        register_activation_hook(__FILE__, array(&$this, 'activate_plugin'));

        add_action('admin_menu', array(&$this, 'add_admin_menu'));
        add_action('admin_init', array(&$this, 'admin_settings'));

        switch ($_POST['type']) {
            case 'add':
                $this->add_question();
                break;
            case 'update':
                $this->update_question();
                break;
            case 'delete':
                $this->delete_question();
                break;
            default:
        }

        $this->get_questions();
    } //End __construct

    public function activate_plugin() {
        global $wpdb;
        $table_name = $wpdb->prefix . $this->plugin_info['table_name'];
        include_once(ABSPATH . 'wp-admin/includes/upgrade.php');

        $sql = "
        CREATE TABLE IF NOT EXISTS `" . $table_name . "` (
            `id` int(255) unsigned NOT NULL AUTO_INCREMENT,
            `question` varchar(150) NOT NULL,
            `answers` TEXT NOT NULL,
            PRIMARY KEY (`id`)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;";
        dbDelta($sql);
    }

    public function admin_settings() {
        register_setting('quiz_manager_settings', 'quiz_manager_settings', array(&$this, 'validate_settings'));

        if (isset($_POST['action'])) {
            update_option('quiz_manager_settings', $this->validate_settings($_POST['quiz_manager_settings']));
        }
    } //End admin_settings

    public function validate_settings($input) {
        $valid_input['quiz_length'] = trim($input['quiz_length']);

        if (!is_numeric($valid_input['quiz_length']) && $valid_input['quiz_length'] < 0) {
            $valid_input = '';
        }

        return $valid_input;
    } //End validate_settings

    private function add_question() {
        global $wpdb;
        $table_name = $wpdb->prefix . $this->plugin_info['table_name'];
        $answers = serialize($_POST['answer']);
        $rows_affected = $wpdb->insert($table_name, array('question' => $_POST['question'], 'answers' => $answers));
    } //End add_question

    private function update_question() {
        global $wpdb;
        $table_name = $wpdb->prefix . $this->plugin_info['table_name'];
        $answers = serialize($_POST['answer']);
        $rows_affected = $wpdb->update($table_name, array('question' => $_POST['question'], 'answers' => $answers), array('id' => $_POST['id']));
    } //End update_question

    private function delete_question() {
        global $wpdb;
        $table_name = $wpdb->prefix . $this->plugin_info['table_name'];
        $rows_affected = $wpdb->query("DELETE FROM `$table_name` WHERE `id`='" . $wpdb->escape($_POST['id']) . "'");
    } //End delete_question

    private function get_questions() {
        global $wpdb;
        $table_name = $wpdb->prefix . $this->plugin_info['table_name'];
        $questions = $wpdb->get_results("SELECT `id`, `question`, `answers` FROM `" . $table_name . "`", ARRAY_A);
        foreach ($questions as $question) {
            $this->questions[$question['id']]['id'] = $question['id'];
            $this->questions[$question['id']]['question'] = $question['question'];
            $this->questions[$question['id']]['answers'] = unserialize($question['answers']);
        }
    } //End get_questions

    public function add_admin_menu() {
        add_plugins_page('Manage Quizes and Options', 'Quiz Manager', 'administrator', 'quiz_manager', array(&$this, 'render_backend'));
    } //End add_admin_menu

    public function render_backend() {
        wp_enqueue_style('black-tie');
        wp_enqueue_script('quizManagerScript');
        include dirname(__FILE__) . '/application/view/options.php';
    } //End render_backend

    public function generate_quiz() {
        /*
         * This is all that's left in version 1 :D
         */
        echo '<h1 style="color: purple;">Koala Bears</h1>';
    } //End generate_quiz
} //End quiz_manager

$quiz_manager = new quiz_manager;
add_shortcode('generate_quiz', array('quiz_manager', 'generate_quiz'));
?>
