<?php

namespace XT\LastMemberOnline\Widget;

use XF;
use XF\Finder\UserFinder;
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
        'time' => '24',
        'sort' => 'last_activity',
        'order' => 'DESC',
        'usercount' => '0',
        'limit' => '50',
        'style' => 'name'
    ];

    public function render(): false|WidgetRenderer
    {
        $visitor = XF::visitor();
        if (!$visitor->canViewMemberList())
        {
            return '';
        }

        $options = $this->options;
        $time = $options['time'];
        $sort = $options['sort'];
        $order = $options['order'];
        $usercount = $options['usercount'];
        $limit = $options['limit'];
        $style = $options['style'];

        $userFinder = $this->finder(UserFinder::class)
            ->where(['last_activity', '>=', time() - 3600 * $time])
            ->order($sort, $order)
            ->limit($limit);

        if (!$visitor->canBypassUserPrivacy())
        {
            $userFinder->where(['visible', '=', '1']);
        }

        $users = $userFinder->fetch();

        $usercounted = $usercount ? count($users) : '0';
        $usercounted_more = $usercounted - $limit;

        $viewParams = [
            'users' => $users,
            'usercounted' => $usercounted,
            'usercounted_more' => $usercounted_more,
            'style' => $style,
        ];

        return $this->renderer('xt_lmo_widget', $viewParams);
    }

    public function verifyOptions(Request $request, array &$options, &$error = null): true
    {
        $options = $request->filter([
            'time' => 'str',
            'sort' => 'str',
            'order' => 'str',
            'usercount' => 'bool',
            'limit' => 'str',
            'style' => 'str'
        ]);

        return true;
    }
}