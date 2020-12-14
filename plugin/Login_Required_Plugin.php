<?php

    namespace noxwp\lr\plugin;

    use noxwp\lr\base\Login_Required_Base;

    /**
     * Login Required Admin
     */
    class Login_Required_Plugin extends Login_Required_Base
    {
        /**
         * Run all the required admin actions.
         */
        public function run()
        {
            $enabled = (bool)get_option($this->prefix('enabled'));

            if ($enabled) {
                $this->apply_general_actions();
                $this->apply_rest_actions();
            }
        }

        /**
         *
         */
        protected function apply_general_actions()
        {
            add_action(
                'template_redirect',
                function () {
                    if (!is_user_logged_in()) {
                        $usesHtml    = (bool)get_option($this->prefix('custom_html'));
                        $canRedirect = !$usesHtml;

                        if ($usesHtml) {
                            $htmlContents = get_option($this->prefix('custom_html_contents'));

                            if (!empty($htmlContents)) {
                                echo $htmlContents;

                                http_response_code(200);

                                exit;
                            }

                            $canRedirect = true;
                        }

                        if ($canRedirect) {
                            auth_redirect();
                        }
                    }
                }
            );

            add_action(
                'plugins_loaded',
                static function () {
                    remove_filter('lostpassword_url', 'wc_lostpassword_url');
                }
            );
        }

        protected function apply_rest_actions()
        {
            $enable_rest = (bool)get_option($this->prefix('rest_enabled'));

            if (!$enable_rest) {
                add_filter(
                    'rest_authentication_errors',
                    function ($result) {
                        if (!empty($result)) {
                            return $result;
                        }

                        if (!is_user_logged_in()) {
                            return new \WP_Error(
                                'rest_not_logged_in',
                                __('API Requests are only supported for authenticated requests.', 'wp-nox-login-required'),
                                ['status' => 401]
                            );
                        }

                        return $result;
                    }
                );
            }
        }
    }
