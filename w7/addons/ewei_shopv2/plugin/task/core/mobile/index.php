<?php
if (!defined('IN_IA')) 
{
	exit('Access Denied');
}
class index_EweiShopV2Page extends PluginMobilePage 
{
	public function main() 
	{
		global $_W;
		global $_GPC;
		$tabpage = $_GPC['tabpage'];
		$openid = trim($_W['openid']);
		$is_menu = $this->model->getdefault('menu_state');
		$member = m('member')->getMember($openid);
		$now_time = time();
		$task_sql = 'SELECT * FROM ' . tablename('ewei_shop_task_poster') . ' WHERE timestart<=' . $now_time . ' AND timeend>' . $now_time . ' AND uniacid=' . $_W['uniacid'] . ' AND `status`=1 AND `is_delete`=0 ORDER BY `createtime` DESC LIMIT 0,15';
		$task_list = pdo_fetchall($task_sql);
		foreach ($task_list as $key => $val ) 
		{
			$val['reward_data'] = unserialize($val['reward_data']);
			$recward = $val['reward_data']['rec'];
			if (isset($recward['credit']) && (0 < $recward['credit'])) 
			{
				$task_list[$key]['is_credit'] = 1;
			}
			if (isset($recward['money']['num']) && (0 < $recward['money']['num'])) 
			{
				$task_list[$key]['is_money'] = 1;
			}
			if (isset($recward['bribery']) && (0 < $recward['bribery'])) 
			{
				$task_list[$key]['is_bribery'] = 1;
			}
			if (isset($recward['goods']) && count($recward['goods'])) 
			{
				$task_list[$key]['is_goods'] = 1;
			}
			if (isset($recward['coupon']['total']) && (0 < $recward['coupon']['total'])) 
			{
				$task_list[$key]['is_coupon'] = 1;
			}
		}
		$running_sql = 'SELECT `join`.*,`task`.title,`task`.reward_data AS `poster_reward` FROM ' . tablename('ewei_shop_task_join') . ' AS `join` LEFT JOIN ' . tablename('ewei_shop_task_poster') . ' AS `task` ON `join`.task_id=`task`.`id` WHERE `join`.`failtime`>' . $now_time . ' AND `join`.`join_user`="' . $openid . '" AND `join`.uniacid=' . $_W['uniacid'] . ' AND `join`.`is_reward`=0 ORDER BY `join`.`addtime` DESC LIMIT 0,15';
		$task_running = pdo_fetchall($running_sql);
		foreach ($task_running as $key => $val ) 
		{
			$val['reward_data'] = unserialize($val['poster_reward']);
			$recward = $val['reward_data']['rec'];
			if (isset($recward['credit']) && (0 < $recward['credit'])) 
			{
				$task_running[$key]['is_credit'] = 1;
			}
			if (isset($recward['money']['num']) && (0 < $recward['money']['num'])) 
			{
				$task_running[$key]['is_money'] = 1;
			}
			if (isset($recward['bribery']) && (0 < $recward['bribery'])) 
			{
				$task_running[$key]['is_bribery'] = 1;
			}
			if (isset($recward['goods']) && count($recward['goods'])) 
			{
				$task_running[$key]['is_goods'] = 1;
			}
			if (isset($recward['coupon']['total']) && (0 < $recward['coupon']['total'])) 
			{
				$task_running[$key]['is_coupon'] = 1;
			}
		}
		$complete_sql = 'SELECT `join`.*,`task`.title FROM ' . tablename('ewei_shop_task_join') . ' AS `join` LEFT JOIN ' . tablename('ewei_shop_task_poster') . ' AS `task` ON `join`.task_id=`task`.`id` WHERE `join`.uniacid=' . $_W['uniacid'] . ' AND `join`.`join_user`="' . $openid . '" AND `join`.`is_reward`=1 ORDER BY `join`.`addtime` DESC LIMIT 0,15';
		$task_complete = pdo_fetchall($complete_sql);
		foreach ($task_complete as $key => $val ) 
		{
			$task_complete[$key]['reward_data'] = unserialize($val['reward_data']);
			$val['reward_data'] = unserialize($val['reward_data']);
			$recward = $val['reward_data'];
			if (isset($recward['credit']) && (0 < $recward['credit'])) 
			{
				$task_complete[$key]['is_credit'] = 1;
			}
			if (isset($recward['money']['num']) && (0 < $recward['money']['num'])) 
			{
				$task_complete[$key]['is_money'] = 1;
			}
			if (isset($recward['bribery']) && (0 < $recward['bribery'])) 
			{
				$task_complete[$key]['is_bribery'] = 1;
			}
			if (isset($recward['goods']) && count($recward['goods'])) 
			{
				$task_complete[$key]['is_goods'] = 1;
				foreach ($task_complete[$key]['reward_data']['goods'] as $k => $v ) 
				{
					$searchsql = 'SELECT thumb FROM ' . tablename('ewei_shop_goods') . ' WHERE uniacid= ' . $_W['uniacid'] . ' and id=' . $k . ' and status=1 and deleted=0';
					$thumb = pdo_fetchcolumn($searchsql);
					$thumb = tomedia($thumb);
					$task_complete[$key]['reward_data']['goods'][$k]['thumb'] = $thumb;
				}
			}
			if (isset($recward['coupon']['total']) && (0 < $recward['coupon']['total'])) 
			{
				$task_complete[$key]['is_coupon'] = 1;
			}
		}
		$faile_sql = 'SELECT `join`.*,`task`.title,`task`.reward_data AS `poster_reward` FROM ' . tablename('ewei_shop_task_join') . ' AS `join` LEFT JOIN ' . tablename('ewei_shop_task_poster') . ' AS `task` ON `join`.task_id=`task`.`id` WHERE `join`.`failtime`<=' . $now_time . ' AND `join`.`join_user`="' . $openid . '" AND `join`.uniacid=' . $_W['uniacid'] . ' AND `join`.`is_reward`=0 ORDER BY `join`.`addtime` DESC LIMIT 0,15';
		$faile_complete = pdo_fetchall($faile_sql);
		foreach ($faile_complete as $key => $val ) 
		{
			$val['reward_data'] = unserialize($val['poster_reward']);
			$recward = $val['reward_data']['rec'];
			if (isset($recward['credit']) && (0 < $recward['credit'])) 
			{
				$faile_complete[$key]['is_credit'] = 1;
			}
			if (isset($recward['money']['num']) && (0 < $recward['money']['num'])) 
			{
				$faile_complete[$key]['is_money'] = 1;
			}
			if (isset($recward['bribery']) && (0 < $recward['bribery'])) 
			{
				$faile_complete[$key]['is_bribery'] = 1;
			}
			if (isset($recward['goods']) && count($recward['goods'])) 
			{
				$faile_complete[$key]['is_goods'] = 1;
			}
			if (isset($recward['coupon']['total']) && (0 < $recward['coupon']['total'])) 
			{
				$faile_complete[$key]['is_coupon'] = 1;
			}
		}
		$advs = pdo_fetchall('select id,advname,link,thumb from ' . tablename('ewei_shop_task_adv') . ' where uniacid=:uniacid and enabled=1 order by displayorder desc', array(':uniacid' => $_W['uniacid']));
		$advs = set_medias($advs, 'thumb');
		include $this->template();
	}
	public function gettask() 
	{
		global $_W;
		global $_GPC;
		$content = trim($_GPC['content']);
		$timeout = 4;
		$url = mobileUrl('task/build', array('timestamp' => TIMESTAMP), true);
		$resp = ihttp_request($url, array('openid' => $_W['openid'], 'content' => urlencode($content)), array(), $timeout);
		echo json_encode(array('status' => 1));
		exit();
	}
	public function gettaskinfo() 
	{
		global $_W;
		global $_GPC;
		if (intval($_GPC['id'])) 
		{
			$param = array(':id' => intval($_GPC['id']), ':uniacid' => $_W['uniacid']);
			$now_time = time();
			$task_sql = 'SELECT * FROM ' . tablename('ewei_shop_task_poster') . ' WHERE timestart<=' . $now_time . ' AND timeend>' . $now_time . ' AND uniacid=:uniacid AND id=:id AND `status`=1  ';
			$taskinfo = pdo_fetch($task_sql, $param);
			$taskinfo['reward_data'] = unserialize($taskinfo['reward_data']);
			$db_data = pdo_fetchcolumn('select `data` from ' . tablename('ewei_shop_task_default') . ' where uniacid=:uniacid limit 1', array(':uniacid' => $_W['uniacid']));
			$res = '';
			if (!empty($db_data)) 
			{
				$res = unserialize($db_data);
			}
			include $this->template('task/taskinfo');
		}
		else 
		{
			$taskinfo = '';
			include $this->template('task/taskinfo');
		}
	}
	public function getMoreTask() 
	{
	}
}
?>