<?php

namespace XT\LastMemberOnline\Widget;

use XF;
use XF\Http\Request;
use XF\Widget\AbstractWidget;
use XF\Widget\WidgetRenderer;

/**
 * Class LastOnline
 * @package XT\LastMemberOnline\Widget
 */
class LastOnline extends AbstractWidget
{
    protected $defaultOptions = [
      'xt_lmo_time' => '24',
      'xt_lmo_sort' => 'last_activity',
      'xt_lmo_order' => 'DESC',
      'xt_lmo_usercount' => '0',
      'xt_lmo_limit' => '50'
    ];

    public function render(): false|WidgetRenderer
    {
        if (!XF::visitor()->canViewMemberList())
        {
            return false;
        }

        $options = $this->options;
        $xt_lmo_time = $options['xt_lmo_time'];
        $xt_lmo_sort = $options['xt_lmo_sort'];
        $xt_lmo_order = $options['xt_lmo_order'];
        $xt_lmo_usercount = $options['xt_lmo_usercount'];
        $xt_lmo_limit = $options['xt_lmo_limit'];

        if (!XF::visitor()->canBypassUserPrivacy())
        {
            $users = XF::finder('XF:User')->where([
                ['last_activity > ?' => time() - 3600 * $xt_lmo_time],
                ['visible', '=', '1']
            ])->order($xt_lmo_sort, $xt_lmo_order)->limit($xt_lmo_limit)->fetch();
        }
        else
        {
            $users = XF::finder('XF:User')->where([
                ['last_activity', '>=', time() - 3600 * $xt_lmo_time]
            ])->order($xt_lmo_sort, $xt_lmo_order)->limit($xt_lmo_limit)->fetch();
        }

        $outputUsers = [];

        foreach ($users as $user)
        {
            $outputUsers[] = [
                'user_id' => $user['user_id'],
                'username' => $user['username'],
                'visible' => $user['visible'],
                'display_style_group_id' => $user['display_style_group_id']
            ];
        }

        if ($xt_lmo_usercount)
        {
            $xt_lmo_usercounted = count($users);
            $xt_lmo_usercounted_more = $xt_lmo_usercounted - $xt_lmo_limit;
        }
        else
        {
            $xt_lmo_usercounted = '0';
            $xt_lmo_usercounted_more = $xt_lmo_usercounted - $xt_lmo_limit;
        }

        $viewParams = [
            'outputUsers' => $outputUsers,
            'xt_lmo_usercounted' => $xt_lmo_usercounted,
            'xt_lmo_usercounted_more' => $xt_lmo_usercounted_more
        ];

        return $this->renderer('xt_lmo_widget', $viewParams);
    }

    public function verifyOptions(Request $request, array &$options, &$error = null): true
    {
        $options = $request->filter([
            'xt_lmo_time' => 'str',
            'xt_lmo_sort' => 'str',
            'xt_lmo_order' => 'str',
            'xt_lmo_usercount' => 'bool',
            'xt_lmo_limit' => 'str'
        ]);

        return true;
    }
}