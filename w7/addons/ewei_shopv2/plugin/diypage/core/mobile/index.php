<?php
if (!(defined('IN_IA'))) 
{
	exit('Access Denied');
}
class Index_EweiShopV2Page extends PluginMobilePage 
{
	public function main() 
	{
		global $_W;
		global $_GPC;
		$id = intval($_GPC['id']);
		if (empty($id)) 
		{
			$this->message('请求参数错误！', mobileUrl());
		}
		$page = $this->model->getPage($id, true);
		if (empty($page)) 
		{
			$this->message('页面不存在！', mobileUrl());
		}
		if (empty($_W['openid']) && (($page['type'] == 3) || ($page['type'] == 4))) 
		{
			$_W['openid'] = m('account')->checkLogin();
		}
		if (!(empty($page['data']['page']['visit'])) && ($page['data']['page']['type'] == 1)) 
		{
			if (empty($_W['openid'])) 
			{
				$_W['openid'] = m('account')->checkLogin();
				exit();
			}
			$title = ((!(empty($page['data']['page']['novisit']['title'])) ? $page['data']['page']['novisit']['title'] : '您没有权限访问!'));
			$link = ((!(empty($page['data']['page']['novisit']['link'])) ? $page['data']['page']['novisit']['link'] : mobileUrl()));
			$member = m('member')->getMember($_W['openid']);
			$visit_m = $page['data']['page']['visitlevel']['member'];
			$visit_c = $page['data']['page']['visitlevel']['commission'];
			$visit_c = ((isset($visit_c) ? explode(',', $visit_c) : array()));
			$visit_m = ((isset($visit_m) ? explode(',', $visit_m) : array()));
			if (!(in_array((empty($member['level']) ? 'default' : $member['level']), $visit_m)) && (!(in_array($member['agentlevel'], $visit_c)) || empty($member['isagent']))) 
			{
				$this->message($title, $link);
			}
		}
		$diyitems = $page['data']['items'];
		$this->model->setShare($page);
		include $this->template();
	}

}
?>