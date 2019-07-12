<?php
if (!defined('IN_IA')) 
{
	exit('Access Denied');
}
require EWEI_SHOPV2_PLUGIN . 'commission/core/page_login_mobile.php';
class Apply_EweiShopV2Page extends CommissionMobileLoginPage 
{
	public function main() 
	{
		global $_W;
		global $_GPC;
		$openid = $_W['openid'];
		$level = $this->set['level'];
		$member = $this->model->getInfo($openid, array());
		if (empty($member['realname'])) 
		{
			$returnurl = urlencode(mobileUrl('commission/apply'));
			$this->message('需要您完善资料才能继续操作!', mobileUrl('member/info', array('returnurl' => $returnurl)), 'info');
		}
		$time = time();
		$day_times = intval($this->set['settledays']) * 3600 * 24;
		$agentLevel = $this->model->getLevel($openid);
		$commission_ok = 0;
		$orderids = array();
		$orderpaytime = array();
		$residue = 0;
		if (1 <= $level) 
		{
			$level1_orders = pdo_fetchall('select distinct o.id,o.paytime from ' . tablename('ewei_shop_order') . ' o ' . ' left join  ' . tablename('ewei_shop_order_goods') . ' og on og.orderid=o.id ' . ' where o.agentid=:agentid and o.status>=3  and og.status1=0 and og.nocommission=0 and (' . $time . ' - o.finishtime > ' . $day_times . ') and o.uniacid=:uniacid  group by o.id', array(':uniacid' => $_W['uniacid'], ':agentid' => $member['id']));
			foreach ($level1_orders as $o ) 
			{
				if (empty($o['id'])) 
				{
					continue;
				}
				$hasorder = false;
				foreach ($orderids as $or ) 
				{
					if ($or['orderid'] == $o['id']) 
					{
						$hasorder = true;
						break;
					}
				}
				if ($hasorder) 
				{
					continue;
				}
				$orderids[] = array('orderid' => $o['id'], 'level' => 1, 'paytime' => date('Y-m', $o['paytime']));
				$orderpaytime[] = date('Y-m', $o['paytime']);
			}
		}
		if (2 <= $level) 
		{
			if (0 < $member['level1']) 
			{
				$level2_orders = pdo_fetchall('select distinct o.id,o.paytime from ' . tablename('ewei_shop_order') . ' o ' . ' left join  ' . tablename('ewei_shop_order_goods') . ' og on og.orderid=o.id ' . ' where o.agentid in( ' . implode(',', array_keys($member['level1_agentids'])) . ')  and o.status>=3  and og.status2=0 and og.nocommission=0 and (' . $time . ' - o.finishtime > ' . $day_times . ') and o.uniacid=:uniacid  group by o.id', array(':uniacid' => $_W['uniacid']));
				foreach ($level2_orders as $o ) 
				{
					if (empty($o['id'])) 
					{
						continue;
					}
					$hasorder = false;
					foreach ($orderids as $or ) 
					{
						if ($or['orderid'] == $o['id']) 
						{
							$hasorder = true;
							break;
						}
					}
					if ($hasorder) 
					{
						continue;
					}
					$orderids[] = array('orderid' => $o['id'], 'level' => 2, 'paytime' => date('Y-m', $o['paytime']));
					$orderpaytime[] = date('Y-m', $o['paytime']);
				}
			}
		}
		if (3 <= $level) 
		{
			if (0 < $member['level2']) 
			{
				$level3_orders = pdo_fetchall('select distinct o.id,o.paytime from ' . tablename('ewei_shop_order') . ' o ' . ' left join  ' . tablename('ewei_shop_order_goods') . ' og on og.orderid=o.id ' . ' where o.agentid in( ' . implode(',', array_keys($member['level2_agentids'])) . ')  and o.status>=3  and  og.status3=0 and og.nocommission=0 and (' . $time . ' - o.finishtime > ' . $day_times . ')   and o.uniacid=:uniacid  group by o.id', array(':uniacid' => $_W['uniacid']));
				foreach ($level3_orders as $o ) 
				{
					if (empty($o['id'])) 
					{
						continue;
					}
					$hasorder = false;
					foreach ($orderids as $or ) 
					{
						if ($or['orderid'] == $o['id']) 
						{
							$hasorder = true;
							break;
						}
					}
					if ($hasorder) 
					{
						continue;
					}
					$orderids[] = array('orderid' => $o['id'], 'level' => 3, 'paytime' => date('Y-m', $o['paytime']));
					$orderpaytime[] = date('Y-m', $o['paytime']);
				}
			}
		}
		if (!empty($this->set['repurchase'])) 
		{
			if ($agentLevel) 
			{
				$repurchase_price = (double) $agentLevel['repurchase'];
			}
			else 
			{
				$repurchase_price = (double) $this->set['repurchase_default'];
			}
			$year_month = $this->model->getRepurchase($openid, array_unique($orderpaytime));
		}
		$pay_time = array();
		foreach ($orderids as $key => $o ) 
		{
			$goods = pdo_fetchall('SELECT ' . 'og.commission1,og.commission2,og.commission3,og.commissions,' . 'og.status1,og.status2,og.status3,' . 'og.content1,og.content2,og.content3 from ' . tablename('ewei_shop_order_goods') . ' og' . ' left join ' . tablename('ewei_shop_goods') . ' g on g.id=og.goodsid  ' . ' where og.orderid=:orderid and og.nocommission=0 and og.uniacid = :uniacid order by og.createtime  desc ', array(':uniacid' => $_W['uniacid'], ':orderid' => $o['orderid']));
			$commission_money = 0;
			foreach ($goods as $g ) 
			{
				$commissions = iunserializer($g['commissions']);
				if (($o['level'] == 1) && ($g['status1'] == 0)) 
				{
					$commission1 = iunserializer($g['commission1']);
					if (empty($commissions)) 
					{
						$commission_money = ((isset($commission1['level' . $agentLevel['id']]) ? $commission1['level' . $agentLevel['id']] : $commission1['default']));
						$commission_ok += $commission_money;
					}
					else 
					{
						$commission_money = ((isset($commissions['level1']) ? floatval($commissions['level1']) : 0));
						$commission_ok += $commission_money;
					}
				}
				if (($o['level'] == 2) && ($g['status2'] == 0)) 
				{
					$commission2 = iunserializer($g['commission2']);
					if (empty($commissions)) 
					{
						$commission_money += ((isset($commission2['level' . $agentLevel['id']]) ? $commission2['level' . $agentLevel['id']] : $commission2['default']));
						$commission_ok += ((isset($commission2['level' . $agentLevel['id']]) ? $commission2['level' . $agentLevel['id']] : $commission2['default']));
					}
					else 
					{
						$commission_money += ((isset($commissions['level2']) ? floatval($commissions['level2']) : 0));
						$commission_ok += ((isset($commissions['level2']) ? floatval($commissions['level2']) : 0));
					}
				}
				if (($o['level'] == 3) && ($g['status3'] == 0)) 
				{
					$commission3 = iunserializer($g['commission3']);
					if (empty($commissions)) 
					{
						$commission_money += ((isset($commission3['level' . $agentLevel['id']]) ? $commission3['level' . $agentLevel['id']] : $commission3['default']));
						$commission_ok += ((isset($commission3['level' . $agentLevel['id']]) ? $commission3['level' . $agentLevel['id']] : $commission3['default']));
					}
					else 
					{
						$commission_money += ((isset($commissions['level3']) ? floatval($commissions['level3']) : 0));
						$commission_ok += ((isset($commissions['level3']) ? floatval($commissions['level3']) : 0));
					}
				}
			}
			$pay_time[$o['paytime']] += $commission_money;
			unset($orderids[$key]['paytime']);
		}
		if (!empty($this->set['repurchase'])) 
		{
			$temp_year_month = array();
			foreach ($year_month as $key => $item ) 
			{
				if (0 < $year_month[$key]) 
				{
					$temp_year_month[$key] = max($pay_time[$key] - $year_month[$key], 0);
					$temp_year_month[$key] = (($temp_year_month[$key] == 0 ? $pay_time[$key] : $temp_year_month[$key]));
					$residue += $temp_year_month[$key];
				}
			}
		}
		if ($agentLevel) 
		{
			$withdraw_price = (double) $agentLevel['withdraw'];
		}
		else 
		{
			$withdraw_price = (double) $this->set['withdraw_default'];
		}
		$withdraw = ((!empty($withdraw_price) ? $withdraw_price : (double) $this->set['withdraw']));
		if ($withdraw <= 0) 
		{
			$withdraw = 1;
		}
		$cansettle = $withdraw <= $commission_ok;
		if ($cansettle == false) 
		{
			$this->message('你还没有到达提现要求!需要达到' . $withdraw . '元,您目前为' . $commission_ok . '元', mobileUrl('commission/withdraw'), 'danger');
		}
		$member['commission_ok'] = number_format($commission_ok, 2);
		$set_array = array();
		$set_array['charge'] = $this->set['withdrawcharge'];
		$set_array['begin'] = floatval($this->set['withdrawbegin']);
		$set_array['end'] = floatval($this->set['withdrawend']);
		$realmoney = $commission_ok;
		if (isset($residue) && (0 < $residue)) 
		{
			$realmoney = $realmoney - $residue;
		}
		$deductionmoney = 0;
		if (!empty($set_array['charge'])) 
		{
			$money_array = m('member')->getCalculateMoney($commission_ok, $set_array);
			if ($money_array['flag']) 
			{
				$realmoney = $money_array['realmoney'];
				$deductionmoney = $money_array['deductionmoney'];
			}
		}
		$last_data = $this->model->getLastApply($member['id']);
		$type_array = array();
		if ($this->set['cashcredit'] == 1) 
		{
			$type_array[0]['title'] = $this->set['texts']['withdraw'] . '到' . $_W['shopset']['trade']['moneytext'];
		}
		if ($this->set['cashweixin'] == 1) 
		{
			$type_array[1]['title'] = $this->set['texts']['withdraw'] . '到微信钱包';
		}
		if ($this->set['cashother'] == 1) 
		{
			if ($this->set['cashalipay'] == 1) 
			{
				$type_array[2]['title'] = $this->set['texts']['withdraw'] . '到支付宝';
				if (!empty($last_data)) 
				{
					if ($last_data['type'] != 2) 
					{
						$type_last = $this->model->getLastApply($member['id'], 2);
						if (!empty($type_last)) 
						{
							$last_data['realname'] = $type_last['realname'];
							$last_data['alipay'] = $type_last['alipay'];
						}
					}
				}
			}
			if ($this->set['cashcard'] == 1) 
			{
				$type_array[3]['title'] = $this->set['texts']['withdraw'] . '到银行卡';
				if (!empty($last_data)) 
				{
					if ($last_data['type'] != 3) 
					{
						$type_last = $this->model->getLastApply($member['id'], 3);
						if (!empty($type_last)) 
						{
							$last_data['realname'] = $type_last['realname'];
							$last_data['bankname'] = $type_last['bankname'];
							$last_data['bankcard'] = $type_last['bankcard'];
						}
					}
				}
				$condition = ' and uniacid=:uniacid';
				$params = array(':uniacid' => $_W['uniacid']);
				$banklist = pdo_fetchall('SELECT * FROM ' . tablename('ewei_shop_commission_bank') . ' WHERE 1 ' . $condition . '  ORDER BY displayorder DESC', $params);
			}
		}
		if (!empty($last_data)) 
		{
			if (array_key_exists($last_data['type'], $type_array)) 
			{
				$type_array[$last_data['type']]['checked'] = 1;
			}
		}
		if ($_W['ispost']) 
		{
			if (empty($_SESSION['commission_apply_token'])) 
			{
				show_json(0, '不要短时间重复下提交!');
			}
			unset($_SESSION['commission_apply_token']);
			if (($commission_ok <= 0) || empty($orderids)) 
			{
				show_json(0, '参数错误,请刷新页面后重新提交!');
			}
			$type = intval($_GPC['type']);
			if (isset($residue) && (0 < $residue)) 
			{
				$type = ((0 < ($commission_ok - $residue) ? $type : 0));
			}
			if (!array_key_exists($type, $type_array)) 
			{
				show_json(0, '未选择提现方式，请您选择提现方式后重试!');
			}
			$apply = array();
			if ($type == 2) 
			{
				$alipay = trim($_GPC['alipay']);
				$alipay1 = trim($_GPC['alipay1']);
				$realname = trim($_GPC['realname']);
				if (empty($realname)) 
				{
					show_json(0, '请填写姓名!');
				}
				if (!$cansettle) 
				{
					show_json(0, '提现金额小于最低提现额度!');
				}
				if (empty($alipay)) 
				{
					show_json(0, '请填写支付宝帐号!');
				}
				if (empty($alipay1)) 
				{
					show_json(0, '请填写确认帐号!');
				}
				if ($alipay != $alipay1) 
				{
					show_json(0, '支付宝帐号与确认帐号不一致!');
				}
				$apply['realname'] = $realname;
				$apply['alipay'] = $alipay;
			}
			else if ($type == 3) 
			{
				$bankname = trim($_GPC['bankname']);
				$bankcard = trim($_GPC['bankcard']);
				$bankcard1 = trim($_GPC['bankcard1']);
				$realname = trim($_GPC['realname']);
				if (empty($realname)) 
				{
					show_json(0, '请填写姓名!');
				}
				if (empty($bankname)) 
				{
					show_json(0, '请选择银行!');
				}
				if (empty($bankcard)) 
				{
					show_json(0, '请填写银行卡号!');
				}
				if (empty($bankcard1)) 
				{
					show_json(0, '请填写确认卡号!');
				}
				if ($bankcard != $bankcard1) 
				{
					show_json(0, '银行卡号与确认卡号不一致!');
				}
				$apply['realname'] = $realname;
				$apply['bankname'] = $bankname;
				$apply['bankcard'] = $bankcard;
			}
			foreach ($orderids as $o ) 
			{
				pdo_update('ewei_shop_order_goods', array('status' . $o['level'] => 1, 'applytime' . $o['level'] => $time), array('orderid' => $o['orderid'], 'uniacid' => $_W['uniacid']));
			}
			$applyno = m('common')->createNO('commission_apply', 'applyno', 'CA');
			$apply['uniacid'] = $_W['uniacid'];
			$apply['applyno'] = $applyno;
			$apply['orderids'] = iserializer($orderids);
			$apply['mid'] = $member['id'];
			$apply['commission'] = $commission_ok;
			$apply['type'] = $type;
			$apply['status'] = 1;
			$apply['applytime'] = $time;
			$apply['realmoney'] = $realmoney;
			$apply['deductionmoney'] = $deductionmoney;
			$apply['charge'] = $set_array['charge'];
			$apply['beginmoney'] = $set_array['begin'];
			$apply['endmoney'] = $set_array['end'];
			$apply['repurchase'] = ((isset($residue) ? $residue : 0));
			pdo_insert('ewei_shop_commission_apply', $apply);
			$applyid = pdo_insertid();
			if (!empty($temp_year_month)) 
			{
				foreach ($temp_year_month as $key => $val ) 
				{
					$date_year_month = explode('-', $key);
					$data = array('uniacid' => $_W['uniacid'], 'openid' => $_W['openid'], 'year' => $date_year_month[0], 'month' => $date_year_month[1], 'repurchase' => $val, 'applyid' => $applyid);
					pdo_insert('ewei_shop_commission_repurchase', $data);
				}
			}
			$apply_type = array('余额', '微信钱包', '支付宝', '银行卡');
			$mcommission = $commission_ok;
			if (!empty($deductionmoney)) 
			{
				$mcommission .= ',实际到账金额:' . $realmoney . ',个人所得税金额:' . $deductionmoney;
			}
			$this->model->sendMessage($openid, array('commission' => $mcommission, 'type' => $apply_type[$apply['type']]), TM_COMMISSION_APPLY);
			show_json(1, '已提交,请等待审核!');
		}
		if (isset($residue) && (0 < $residue)) 
		{
			$type_array = ((0 < ($commission_ok - $residue) ? $type_array : array($type_array[0])));
		}
		$token = md5(microtime());
		$_SESSION['commission_apply_token'] = $token;
		include $this->template();
	}
}
?>