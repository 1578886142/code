<?php
if (!(defined('IN_IA'))) 
{
	exit('Access Denied');
}
class Index_EweiShopV2Page extends WebPage 
{
	public function main() 
	{
		if (cv('sysset.shop')) 
		{
			header('location: ' . webUrl('sysset/shop'));
		}
		else if (cv('sysset.follow')) 
		{
			header('location: ' . webUrl('sysset/follow'));
		}
		else if (cv('sysset.wap')) 
		{
			header('location: ' . webUrl('sysset/wap'));
		}
		else if (cv('sysset.pcset')) 
		{
			header('location: ' . webUrl('sysset/pcset'));
		}
		else if (cv('sysset.notice')) 
		{
			header('location: ' . webUrl('sysset/notice'));
		}
		else if (cv('sysset.trade')) 
		{
			header('location: ' . webUrl('sysset/trade'));
		}
		else if (cv('sysset.payset')) 
		{
			header('location: ' . webUrl('sysset/payset'));
		}
		else if (cv('sysset.templat')) 
		{
			header('location: ' . webUrl('sysset/templat'));
		}
		else if (cv('sysset.member')) 
		{
			header('location: ' . webUrl('sysset/member'));
		}
		else if (cv('sysset.category')) 
		{
			header('location: ' . webUrl('sysset/category'));
		}
		else if (cv('sysset.contact')) 
		{
			header('location: ' . webUrl('sysset/contact'));
		}
		else if (cv('sysset.qiniu')) 
		{
			header('location: ' . webUrl('sysset/qiniu'));
		}
		else if (cv('sysset.sms.set')) 
		{
			header('location: ' . webUrl('sysset/sms/set'));
		}
		else if (cv('sysset.sms.temp')) 
		{
			header('location: ' . webUrl('sysset/sms/temp'));
		}
		else if (cv('sysset.close')) 
		{
			header('location: ' . webUrl('sysset/close'));
		}
		else if (cv('sysset.tmessage')) 
		{
			header('location: ' . webUrl('sysset/tmessage'));
		}
		else if (cv('sysset.cover')) 
		{
			header('location: ' . webUrl('sysset/cover'));
		}
		else 
		{
			header('location: ' . webUrl());
		}
	}
	public function shop() 
	{
		global $_W;
		global $_GPC;
		$data = m('common')->getSysset('shop');
		if ($_W['ispost']) 
		{
			ca('sysset.shop.edit');
			$data = ((is_array($_GPC['data']) ? $_GPC['data'] : array()));
			$data['name'] = trim($data['name']);
			$data['img'] = save_media($data['img']);
			$data['logo'] = save_media($data['logo']);
			$data['signimg'] = save_media($data['signimg']);
			$data['diycode'] = $_POST['data']['diycode'];
			m('common')->updateSysset(array('shop' => $data));
			plog('sysset.shop.edit', '修改系统设置-商城设置');
			show_json(1);
		}
		include $this->template('sysset/index');
	}
	public function follow() 
	{
		global $_W;
		global $_GPC;
		if ($_W['ispost']) 
		{
			ca('sysset.follow.edit');
			$data = ((is_array($_GPC['data']) ? $_GPC['data'] : array()));
			$data['logo'] = save_media($data['icon']);
			m('common')->updateSysset(array('share' => $data));
			plog('sysset.follow.edit', '修改系统设置-分享及关注设置');
			show_json(1);
		}
		$data = m('common')->getSysset('share');
		include $this->template();
	}
	public function notice() 
	{
		global $_W;
		global $_GPC;
		$data = m('common')->getSysset('notice', false);
		$salers = array();
		if (isset($data['openid'])) 
		{
			if (!(empty($data['openid']))) 
			{
				$openids = array();
				$strsopenids = explode(',', $data['openid']);
				foreach ($strsopenids as $openid ) 
				{
					$openids[] = '\'' . $openid . '\'';
				}
				$salers = pdo_fetchall('select id,nickname,avatar,openid from ' . tablename('ewei_shop_member') . ' where openid in (' . implode(',', $openids) . ') and uniacid=' . $_W['uniacid']);
			}
		}
		$newtype = explode(',', $data['newtype']);
		if (!(empty($newtype)) && is_array($newtype)) 
		{
			foreach ($newtype as $i => $nt ) 
			{
				if ($nt == '') 
				{
					unset($newtype[$i]);
				}
			}
		}
		$opensms = com('sms');
		if ($_W['ispost']) 
		{
			ca('sysset.notice.edit');
			$data = ((is_array($_GPC['data']) ? $_GPC['data'] : array()));
			if (is_array($_GPC['openids'])) 
			{
				$data['openid'] = implode(',', $_GPC['openids']);
			}
			else 
			{
				$data['openid'] = '';
			}
			if (is_array($data['newtype'])) 
			{
				$data['newtype'] = implode(',', $data['newtype']);
			}
			else 
			{
				$data['newtype'] = '';
			}
			m('common')->updateSysset(array('notice' => $data));
			plog('sysset.notice.edit', '修改系统设置-模板消息通知设置');
			show_json(1);
		}
		$template_list = pdo_fetchall('SELECT id,title FROM ' . tablename('ewei_shop_member_message_template') . ' WHERE uniacid=:uniacid ', array(':uniacid' => $_W['uniacid']));
		if ($opensms) 
		{
			$smsset = com('sms')->sms_set();
			if (empty($smsset['juhe']) && empty($smsset['dayu']) && empty($smsset['emay'])) 
			{
				$opensms = false;
			}
			$template_sms = com('sms')->sms_temp();
		}
		include $this->template();
	}
	public function notice_user() 
	{
		global $_W;
		global $_GPC;
		$data = m('common')->getSysset('notice', false);
		$salers = array();
		if (isset($data['openid'])) 
		{
			if (!(empty($data['openid']))) 
			{
				$openids = array();
				$strsopenids = explode(',', $data['openid']);
				foreach ($strsopenids as $openid ) 
				{
					$openids[] = '\'' . $openid . '\'';
				}
				$salers = pdo_fetchall('select id,nickname,avatar,openid from ' . tablename('ewei_shop_member') . ' where openid in (' . implode(',', $openids) . ') and uniacid=' . $_W['uniacid']);
			}
		}
		$newtype = explode(',', $data['newtype']);
		include $this->template('sysset/notice/user');
	}
	public function trade() 
	{
		global $_W;
		global $_GPC;
		if ($_W['ispost']) 
		{
			ca('sysset.trade.edit');
			$data = ((is_array($_GPC['data']) ? $_GPC['data'] : array()));
			if (!(empty($data['withdrawcharge']))) 
			{
				$data['withdrawcharge'] = trim($data['withdrawcharge']);
				$data['withdrawcharge'] = floatval(trim($data['withdrawcharge'], '%'));
			}
			$data['minimumcharge'] = floatval(trim($data['minimumcharge']));
			$data['withdrawbegin'] = floatval(trim($data['withdrawbegin']));
			$data['withdrawend'] = floatval(trim($data['withdrawend']));
			$data['nodispatchareas'] = iserializer($data['nodispatchareas']);
			$data['withdrawcashweixin'] = intval($data['withdrawcashweixin']);
			$data['withdrawcashalipay'] = intval($data['withdrawcashalipay']);
			$data['withdrawcashcard'] = intval($data['withdrawcashcard']);
			m('common')->updateSysset(array('trade' => $data));
			plog('sysset.trade.edit', '修改系统设置-交易设置');
			show_json(1);
		}
		$areas = m('common')->getAreas();
		$data = m('common')->getSysset('trade');
		$data['nodispatchareas'] = iunserializer($data['nodispatchareas']);
		include $this->template();
	}
	protected function upload_cert($fileinput) 
	{
		global $_W;
		$path = IA_ROOT . '/addons/ewei_shopv2/cert';
		load()->func('file');
		mkdirs($path);
		$f = $fileinput . '_' . $_W['uniacid'] . '.pem';
		$outfilename = $path . '/' . $f;
		$filename = $_FILES[$fileinput]['name'];
		$tmp_name = $_FILES[$fileinput]['tmp_name'];
		if (!(empty($filename)) && !(empty($tmp_name))) 
		{
			$ext = strtolower(substr($filename, strrpos($filename, '.')));
			if ($ext != '.pem') 
			{
				$errinput = '';
				if ($fileinput == 'weixin_cert_file') 
				{
					$errinput = 'CERT文件格式错误';
				}
				else if ($fileinput == 'weixin_key_file') 
				{
					$errinput = 'KEY文件格式错误';
				}
				else if ($fileinput == 'weixin_root_file') 
				{
					$errinput = 'ROOT文件格式错误';
				}
				show_json(0, $errinput . ',请重新上传!');
			}
			return file_get_contents($tmp_name);
		}
		return '';
	}
	public function payset() 
	{
		global $_W;
		global $_GPC;
		$data = m('common')->getSysset('pay');
		$sec = m('common')->getSec();
		$sec = iunserializer($sec['sec']);
		if ($_W['ispost']) 
		{
			ca('sysset.payset.edit');
			if ($_FILES['weixin_cert_file']['name']) 
			{
				$sec['cert'] = $this->upload_cert('weixin_cert_file');
			}
			if ($_FILES['weixin_key_file']['name']) 
			{
				$sec['key'] = $this->upload_cert('weixin_key_file');
			}
			if ($_FILES['weixin_root_file']['name']) 
			{
				$sec['root'] = $this->upload_cert('weixin_root_file');
			}
			if ($_FILES['weixin_sub_cert_file']['name']) 
			{
				$sec['sub']['cert'] = $this->upload_cert('weixin_sub_cert_file');
			}
			if ($_FILES['weixin_sub_key_file']['name']) 
			{
				$sec['sub']['key'] = $this->upload_cert('weixin_sub_key_file');
			}
			if ($_FILES['weixin_sub_root_file']['name']) 
			{
				$sec['sub']['root'] = $this->upload_cert('weixin_sub_root_file');
			}
			if ($_FILES['weixin_jie_cert_file']['name']) 
			{
				$sec['jie']['cert'] = $this->upload_cert('weixin_jie_cert_file');
			}
			if ($_FILES['weixin_jie_key_file']['name']) 
			{
				$sec['jie']['key'] = $this->upload_cert('weixin_jie_key_file');
			}
			if ($_FILES['weixin_jie_root_file']['name']) 
			{
				$sec['jie']['root'] = $this->upload_cert('weixin_jie_root_file');
			}
			if ($_FILES['weixin_jie_sub_cert_file']['name']) 
			{
				$sec['jie_sub']['cert'] = $this->upload_cert('weixin_jie_sub_cert_file');
			}
			if ($_FILES['weixin_jie_sub_key_file']['name']) 
			{
				$sec['jie_sub']['key'] = $this->upload_cert('weixin_jie_sub_key_file');
			}
			if ($_FILES['weixin_jie_sub_root_file']['name']) 
			{
				$sec['jie_sub']['root'] = $this->upload_cert('weixin_jie_sub_root_file');
			}
			if ($_FILES['app_wechat_cert_file']['name']) 
			{
				$sec['app_wechat']['cert'] = $this->upload_cert('app_wechat_cert_file');
			}
			if ($_FILES['app_wechat_key_file']['name']) 
			{
				$sec['app_wechat']['key'] = $this->upload_cert('app_wechat_key_file');
			}
			if ($_FILES['app_wechat_root_file']['name']) 
			{
				$sec['app_wechat']['root'] = $this->upload_cert('app_wechat_root_file');
			}
			$sec['app_wechat']['appid'] = trim($_GPC['data']['app_wechat_appid']);
			$sec['app_wechat']['appsecret'] = trim($_GPC['data']['app_wechat_appsecret']);
			$sec['app_wechat']['merchname'] = trim($_GPC['data']['app_wechat_merchname']);
			$sec['app_wechat']['merchid'] = trim($_GPC['data']['app_wechat_merchid']);
			$sec['app_wechat']['apikey'] = trim($_GPC['data']['app_wechat_apikey']);
			$sec['app_alipay']['public_key'] = trim($_GPC['data']['app_alipay_public_key']);
			$sec['appid_sub'] = trim($_GPC['data']['appid_sub']);
			$sec['sub_appid_sub'] = trim($_GPC['data']['sub_appid_sub']);
			$sec['mchid_sub'] = trim($_GPC['data']['mchid_sub']);
			$sec['sub_mchid_sub'] = trim($_GPC['data']['sub_mchid_sub']);
			$sec['apikey_sub'] = trim($_GPC['data']['apikey_sub']);
			$sec['appid'] = trim($_GPC['data']['appid']);
			$sec['mchid'] = trim($_GPC['data']['mchid']);
			$sec['apikey'] = trim($_GPC['data']['apikey']);
			$sec['appid_jie_sub'] = trim($_GPC['data']['appid_jie_sub']);
			$sec['sub_appid_jie_sub'] = trim($_GPC['data']['sub_appid_jie_sub']);
			$sec['mchid_jie_sub'] = trim($_GPC['data']['mchid_jie_sub']);
			$sec['sub_mchid_jie_sub'] = trim($_GPC['data']['sub_mchid_jie_sub']);
			$sec['apikey_jie_sub'] = trim($_GPC['data']['apikey_jie_sub']);
			pdo_update('ewei_shop_sysset', array('sec' => iserializer($sec)), array('uniacid' => $_W['uniacid']));
			$inputdata = ((is_array($_GPC['data']) ? $_GPC['data'] : array()));
			$data['weixin'] = intval($inputdata['weixin']);
			$data['weixin_sub'] = intval($inputdata['weixin_sub']);
			$data['weixin_jie'] = intval($inputdata['weixin_jie']);
			$data['weixin_jie_sub'] = intval($inputdata['weixin_jie_sub']);
			$data['alipay'] = intval($inputdata['alipay']);
			$data['credit'] = intval($inputdata['credit']);
			$data['cash'] = intval($inputdata['cash']);
			$data['app_wechat'] = intval($inputdata['app_wechat']);
			$data['app_alipay'] = intval($inputdata['app_alipay']);
			m('common')->updateSysset(array('pay' => $data));
			plog('sysset.payset.edit', '修改系统设置-支付设置');
			show_json(1);
		}
		$url = $_W['siteroot'] . 'addons/ewei_shopv2/payment/wechat/notify.php';
		load()->func('communication');
		$resp = ihttp_get($url);
		include $this->template();
		exit();
	}
	public function member() 
	{
		global $_W;
		global $_GPC;
		if ($_W['ispost']) 
		{
			ca('sysset.member.edit');
			$data = ((is_array($_GPC['data']) ? $_GPC['data'] : array()));
			$data['levelname'] = trim($data['levelname']);
			$data['levelurl'] = trim($data['levelurl']);
			$data['leveltype'] = intval($data['leveltype']);
			m('common')->updateSysset(array('member' => $data));
			$shop = m('common')->getSysset('shop');
			$shop['levelname'] = $data['levelname'];
			$shop['levelurl'] = $data['levelurl'];
			$shop['leveltype'] = $data['leveltype'];
			m('common')->updateSysset(array('shop' => $shop));
			plog('sysset.member.edit', '修改系统设置-会员设置');
			show_json(1);
		}
		$data = m('common')->getSysset('member');
		if (!(isset($data['levelname']))) 
		{
			$shop = m('common')->getSysset('shop');
			$data['levelname'] = $shop['levelname'];
			$data['levelurl'] = $shop['levelurl'];
			$data['leveltype'] = $shop['leveltype'];
		}
		include $this->template();
	}
	public function category() 
	{
		global $_W;
		global $_GPC;
		if ($_W['ispost']) 
		{
			ca('sysset.category.edit');
			$data = ((is_array($_GPC['data']) ? $_GPC['data'] : array()));
			$shop = m('common')->getSysset('shop');
			$shop['level'] = intval($data['level']);
			$shop['show'] = intval($data['show']);
			$shop['style'] = intval($data['style']);
			$shop['advimg'] = save_media($data['advimg']);
			$shop['advurl'] = trim($data['advurl']);
			m('common')->updateSysset(array('category' => $data));
			$shop = m('common')->getSysset('shop');
			$shop['catlevel'] = $data['level'];
			$shop['catshow'] = $data['show'];
			$shop['catadvimg'] = save_media($data['advimg']);
			$shop['catadvurl'] = $data['advurl'];
			m('common')->updateSysset(array('shop' => $shop));
			plog('sysset.category.edit', '修改系统设置-分类层级设置');
			m('shop')->getCategory(true);
			show_json(1);
		}
		$data = m('common')->getSysset('category');
		if (empty($data)) 
		{
			$shop = m('common')->getSysset('shop');
			$data['level'] = $shop['catlevel'];
			$data['show'] = $shop['catshow'];
			$data['advimg'] = $shop['catadvimg'];
			$data['advurl'] = $shop['catadvurl'];
		}
		include $this->template();
	}
	public function contact() 
	{
		global $_W;
		global $_GPC;
		if ($_W['ispost']) 
		{
			ca('sysset.contact.edit');
			$data = ((is_array($_GPC['data']) ? $_GPC['data'] : array()));
			$data['qq'] = trim($data['qq']);
			$data['address'] = trim($data['address']);
			$data['phone'] = trim($data['phone']);
			m('common')->updateSysset(array('contact' => $data));
			$shop = m('common')->getSysset('shop');
			$shop['qq'] = $data['qq'];
			$shop['address'] = $data['address'];
			$shop['phone'] = $data['phone'];
			m('common')->updateSysset(array('shop' => $shop));
			plog('sysset.contact.edit', '修改系统设置-联系方式设置');
			show_json(1);
		}
		$data = m('common')->getSysset('contact');
		if (empty($data)) 
		{
			$shop = m('common')->getSysset('shop');
			$data['qq'] = $shop['qq'];
			$data['address'] = $shop['address'];
			$data['phone'] = $shop['phone'];
		}
		include $this->template();
	}
	public function close() 
	{
		global $_W;
		global $_GPC;
		if ($_W['ispost']) 
		{
			ca('sysset.close.edit');
			$data = ((is_array($_GPC['data']) ? $_GPC['data'] : array()));
			$data['flag'] = intval($data['flag']);
			$data['detail'] = m('common')->html_images($data['detail']);
			$data['url'] = trim($data['url']);
			m('common')->updateSysset(array('close' => $data));
			$shop = m('common')->getSysset('shop');
			$shop['close'] = $data['flag'];
			$shop['closedetail'] = $data['detail'];
			$shop['closeurl'] = $data['url'];
			m('common')->updateSysset(array('shop' => $shop));
			plog('sysset.close.edit', '修改系统设置-商城关闭设置');
			show_json(1);
		}
		$data = m('common')->getSysset('close');
		if (empty($data)) 
		{
			$shop = m('common')->getSysset('shop');
			$data['flag'] = $shop['close'];
			$data['detail'] = $shop['closedetail'];
			$data['url'] = $shop['closeurl'];
		}
		include $this->template();
	}
	public function templat() 
	{
		global $_W;
		global $_GPC;
		if ($_W['ispost']) 
		{
			ca('sysset.templat.edit');
			$data = ((is_array($_GPC['data']) ? $_GPC['data'] : array()));
			m('common')->updateSysset(array('template' => $data));
			$shop = m('common')->getSysset('shop');
			$shop['style'] = $data['style'];
			m('common')->updateSysset(array('shop' => $shop));
			m('cache')->set('template_shop', $data['style']);
			plog('sysset.templat.edit', '修改系统设置-模板设置');
			show_json(1);
		}
		$styles = array();
		$dir = IA_ROOT . '/addons/ewei_shopv2/template/mobile/';
		if ($handle = opendir($dir)) 
		{
			while (($file = readdir($handle)) !== false) 
			{
				if (($file != '..') && ($file != '.')) 
				{
					if (is_dir($dir . '/' . $file)) 
					{
						$styles[] = $file;
					}
				}
			}
			closedir($handle);
		}
		$data = m('common')->getSysset('template', false);
		include $this->template();
	}
	public function goodsprice() 
	{
		include $this->template();
	}
	public function wap() 
	{
		global $_W;
		global $_GPC;
		$data = m('common')->getSysset('wap');
		$wap = com('wap');
		if (!($wap)) 
		{
			$this->message('您没权限访问!');
			exit();
		}
		$sms = com('sms');
		if (!($sms)) 
		{
			$this->message('开启全网通请先开通短信通知');
			exit();
		}
		$template_sms = com('sms')->sms_temp();
		if ($_W['ispost']) 
		{
			ca('sysset.wap.edit');
			$data = ((is_array($_GPC['data']) ? $_GPC['data'] : array()));
			$data['open'] = intval($data['open']);
			$data['loginbg'] = save_media($data['loginbg']);
			$data['regbg'] = save_media($data['regbg']);
			$data['sns']['wx'] = intval($data['sns']['wx']);
			$data['sns']['qq'] = intval($data['sns']['qq']);
			m('common')->updateSysset(array('wap' => $data));
			plog('sysset.wap.edit', '修改WAP设置');
			show_json(1);
		}
		$styles = array();
		$dir = IA_ROOT . '/addons/ewei_shopv2/template/account/';
		if ($handle = opendir($dir)) 
		{
			while (($file = readdir($handle)) !== false) 
			{
				if (($file != '..') && ($file != '.')) 
				{
					if (is_dir($dir . '/' . $file)) 
					{
						$styles[] = $file;
					}
				}
			}
			closedir($handle);
		}
		include $this->template('sysset/wap');
	}
}
?>