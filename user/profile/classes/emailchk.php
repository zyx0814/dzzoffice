<?php
namespace   user\profile\classes;

use \core as C;

class Emailchk{

    public function run(&$params)
    {

        $bindstatus = 0;
        $id = isset($params['id']) ? $params['id'] : '';
        $idchk = isset($params['idchk']) ? $params['idchk'] : '';
        $do = trim($params['do']) ? trim($params['do']) : '';
        if ($id && $do === 'changeemail') {

            $email = $params['email'];

            $buid = $params['uid'];

            $user = C::t('user')->get_user_by_uid($buid);

            $idstring = explode('_', $user['emailsenddate']);

            if ($idstring[0] == $id && (time() - $idstring[1]) < 86400) {

                dsetcookie('auth', authcode("{$user['password']}\t{$user['uid']}", 'ENCODE'), 0, 1, true);

                if ($uparr = array('email' => $email, 'emailstatus' => 1, 'emailsenddate' => 0)) {

                    if (C::t('user')->update($buid, $uparr)) {

                        if ($user['emailstatus']) $bindstatus = 1;

                        $newchange = true;

                        include template('pass_safe');

                        exit();
                    }

                }

            }

        } elseif ($idchk && $do === 'changeemail') {

            $email = $params['email'];

            $vuid = $params['uid'];

            $user = C::t('user')->get_user_by_uid($vuid);

            $idstring = explode('_', $user['emailsenddate']);

            if ($idstring[0] == $idchk && (time() - $idstring[1]) < 86400) {

                dsetcookie('auth', authcode("{$user['password']}\t{$user['uid']}", 'ENCODE'), 0, 1, true);

                if ($uparr = array('emailsenddate' => 0)) {


                    if (C::t('user')->update($vuid, $uparr)) {

                        $emailchange = $bindstatus = $user['emailstatus'];

                        $verifyresult = true;

                        include template('pass_safe');

                        exit();
                    }
                }

            }

        }
    }
}