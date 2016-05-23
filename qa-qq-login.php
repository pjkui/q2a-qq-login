<?php

/*
	Question2Answer by Gideon Greenspan and contributors
	http://www.question2answer.org/

	File: qa-plugin/qq-login/qa-qq-login.php
	Description: Login module class for qq login plugin


	This program is free software; you can redistribute it and/or
	modify it under the terms of the GNU General Public License
	as published by the Free Software Foundation; either version 2
	of the License, or (at your option) any later version.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	More about this license: http://www.question2answer.org/license.php
*/

class qa_qq_login
{
    public function match_source($source)
    {
//        print_r($source.'------');
        return $source == 'qq';
    }


    public function login_html($tourl, $context)
    {

        $app_id = qa_opt('qq_app_id');

        if (!strlen($app_id))
            return;

        $this->qq_html(qa_path_absolute('qq-login', array('to' => $tourl)), false, $context);
    }


    public function logout_html($tourl)
    {
        $app_id = qa_opt('qq_app_id');

        if (!strlen($app_id))
            return;

        $this->qq_html($tourl, true, 'menu');

    }


    public function qq_html($tourl, $logout, $context)
    {
        if(qa_opt('qq_enable')==false) return;

        if ($logout != true){
        ?>
        <script type="text/javascript">
            var childWindow;
            function toQzoneLogin() {
                childWindow = window.open("<?php echo qa_opt('site_url');?>?qa=qq-login", "_self");
            }

            function closeChildWindow() {
                childWindow.close();
            }
        </script>
        <style>
            .qq_login{
                border: 0px;
                /*float: left;*/
                margin: -3px 0px;
                padding: 0 4px;
            }
        </style>

        <a href="#" onclick='toQzoneLogin()'><img src="<?php echo qa_opt('site_url').'qa-plugin/'.QQ_LOGIN_DIR_NAME;?>/img/qq_login.png" class="qq_login"></a>
        <?php
        }
        else{
            echo "<a href=\"".qa_opt('site_url')."?qa=logout\">退出</a>";
        }

    }


    public function admin_form()
    {
        $saved = false;

        if (qa_clicked('qq_save_button')) {
            qa_opt('qq_enable',qa_post_text('qq_login_enable'));
            qa_opt('qq_app_id', qa_post_text('qq_app_id_field'));
            qa_opt('qq_app_secret', qa_post_text('qq_app_secret_field'));
            $callback_url = str_replace("?qa=qq-callback","",qa_post_text('qq_app_callback_field'))."?qa=qq-callback";
            qa_opt('qq_app_callback', $callback_url);
            $recorder = new Recorder();
            $recorder->writeInc('appid',qa_post_text('qq_app_id_field'));
            $recorder->writeInc('appkey',qa_post_text('qq_app_secret_field'));
            $recorder->writeInc('callback',$callback_url);
            $recorder->save();
            $saved = true;
        }

        $ready = strlen(qa_opt('qq_app_id')) && strlen(qa_opt('qq_app_secret'));

        return array(
            'ok' => $saved ? 'QQ application details saved' : null,

            'fields' => array(

                array(
                    'label' => "Enable QQ Login",
                    'tags' => 'name="qq_login_enable"',
                    'value' => qa_html(qa_opt('qq_enable')),
                    'type' => 'checkbox',
                ),
                array(
                    'label' => 'QQ App ID:',
                    'value' => qa_html(qa_opt('qq_app_id')),
                    'tags' => 'name="qq_app_id_field"',
                ),

                array(
                    'label' => 'QQ App Secret:',
                    'value' => qa_html(qa_opt('qq_app_secret')),
                    'tags' => 'name="qq_app_secret_field"',
                    'error' => $ready ? null : 'To use QQ Login, please <a href="http://connect.qq.com/manage/" target="_blank">set up a QQ application</a>.',
                ),
                array(
                    'label' => 'QQ callback URL:',
                    'value' => qa_html(qa_opt('qq_app_callback')),
                    'tags' => 'name="qq_app_callback_field"',
                ),
            ),

            'buttons' => array(
                array(
                    'label' => 'Save Changes',
                    'tags' => 'name="qq_save_button"',
                ),
            ),
        );
    }
}
