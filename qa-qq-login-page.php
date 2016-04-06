<?php
/*
	Question2Answer by Gideon Greenspan and contributors
	http://www.question2answer.org/

	File: qa-plugin/qq-login/qa-qq-login-page.php
	Description: Page which performs Facebook login action


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
require_once(dirname(__FILE__) . "/API/qqConnectAPI.php");

class qa_qq_login_page
{
    private $directory;

    public function load_module($directory, $urltoroot)
    {
        $this->directory = $directory;
    }

    public function match_request($request)
    {
        return ($request == 'qq-login' || $request == 'qq-callback');
    }

    /**
     * conflict with OpenLogin ,because OpenLogin override qa_login_external_user , so I hava copy qa_log_in_external_user function ,and change the function name to
     * qa_log_in_external_user_qq as below , then everything is going on . --quinnpan 2016-4-5 17:19:20
     */
    public function qa_log_in_external_user_qq($source, $identifier, $fields)
    {


        require_once QA_INCLUDE_DIR . 'db/users.php';

        $users = qa_db_user_login_find($source, $identifier);
        $countusers = count($users);

        if ($countusers > 1)
            qa_fatal_error('External login mapped to more than one user'); // should never happen

        if ($countusers) // user exists so log them in
            qa_set_logged_in_user($users[0]['userid'], $users[0]['handle'], false, $source);

        else { // create and log in user
            require_once QA_INCLUDE_DIR . 'app/users-edit.php';

            qa_db_user_login_sync(true);

            $users = qa_db_user_login_find($source, $identifier); // check again after table is locked

            if (count($users) == 1) {
                qa_db_user_login_sync(false);
                qa_set_logged_in_user($users[0]['userid'], $users[0]['handle'], false, $source);

            } else {
                $handle = qa_handle_make_valid(@$fields['handle']);

                if (strlen(@$fields['email'])) { // remove email address if it will cause a duplicate
                    $emailusers = qa_db_user_find_by_email($fields['email']);
                    if (count($emailusers)) {
                        qa_redirect('login', array('e' => $fields['email'], 'ee' => '1'));
                        unset($fields['email']);
                        unset($fields['confirmed']);
                    }
                }

                $userid = qa_create_new_user((string)@$fields['email'], null /* no password */, $handle,
                    isset($fields['level']) ? $fields['level'] : QA_USER_LEVEL_BASIC, @$fields['confirmed']);

                qa_db_user_login_add($userid, $source, $identifier);
                qa_db_user_login_sync(false);

                $profilefields = array('name', 'location', 'website', 'about');

                foreach ($profilefields as $fieldname)
                    if (strlen(@$fields[$fieldname]))
                        qa_db_user_profile_set($userid, $fieldname, $fields[$fieldname]);

                if (strlen(@$fields['avatar']))
                    qa_set_user_avatar($userid, $fields['avatar']);

                qa_set_logged_in_user($userid, $handle, false, $source);
            }
        }
    }

    public function process_request($request)
    {
        //如果调用qq-callback界面
        if ($request == 'qq-callback') {

            $qc = new QC();
            $aq_access_token = $qc->qq_callback();
            $qq_openid = $qc->get_openid();

            $topath = qa_get('to');
            if (!isset($topath)) {
                $topath = ''; // redirect to front page
            }
            print_r($qq_openid . '<br/>');
            if ($qq_openid) {
                try {
                    $qc = new QC($aq_access_token, $qq_openid);
                    $user = $qc->get_user_info();
                    $info = @$qc->get_info();

                    if (is_array($user)) {
                        //in order to speed up QQ login speed. we should first checkout whether user is in our database
                        //if user in our database. then call qq_log_in_external_user_qq() function without user info
                        //if user doesn't in our database, it's meaning user is first time login our system.
                        //then we should download user avatar (it will cost lost of server time, especial server network speed is slow)
                        require_once QA_INCLUDE_DIR . 'db/users.php';
                        $source = 'qq';
                        //check whether user is in database
                        $users = qa_db_user_login_find($source, $qq_openid);
                        $countusers = count($users);

                        if ($countusers > 0) {
                            //if user exist in database, then let user data array to be null, to speed up login
                            $this->qa_log_in_external_user_qq('qq', $qq_openid, null);
                        } else {
                            //with QQ token
                            // if user is first time login system , then get user info and download user avatar from QQ
                            $this->qa_log_in_external_user_qq('qq', $qq_openid, array(
                                'email' => @$user['email'],
                                'handle' => uniqid('qq_'),//qq_56f8d5ce88fbe,//user nickname
                                'confirmed' => false,
                                'name' => @$user['nickname'],
                                'location' => @$user['province'] . @$user['city'],
                                'website' => @$info['data']['homepage'],
                                'about' => @$info['data']['introduction'],
                                'avatar' => strlen(@$user['figureurl_qq_1']) ? qa_retrieve_url($user['figureurl_qq_1']) : null,
                            ));
                        }
                    }
                    qa_redirect('logins', array('confirm' => '1', 'to' => $topath));

                } catch (Exception $e) {
                    //自己编写一个QQException
                    print_r($e);
                }
            } else {
                $qc->qq_login();
            }


        } elseif ($request == 'qq-login') {
            $qc = new QC();
            $qc->qq_login();
        }
    }
}
