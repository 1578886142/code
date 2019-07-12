<?php

if (!defined('IN_IA')) {
	exit('Access Denied');
}


require EWEI_SHOPV2_PLUGIN . 'merch/core/inc/page_merch.php';
class Comment_EweiShopV2Page extends MerchWebPage
{
	public function main()
	{
		global $_W;
		global $_GPC;
		$pindex = max(1, intval($_GPC['page']));
		$psize = 20;
		$condition = ' and c.uniacid=:uniacid and c.deleted=0 and g.merchid=:merchid';
		$params = array(':uniacid' => $_W['uniacid'], ':merchid' => $_W['merchid']);

		if (!empty($_GPC['keyword'])) {
			$_GPC['keyword'] = trim($_GPC['keyword']);
			$condition .= ' and ( o.ordersn like :keyword or g.title like :keyword)';
			$params[':keyword'] = '%' . $_GPC['keyword'] . '%';
		}


		if (empty($starttime) || empty($endtime)) {
			$starttime = strtotime('-1 month');
			$endtime = time();
		}


		if (!empty($_GPC['time']['start']) && !empty($_GPC['time']['end'])) {
			$starttime = strtotime($_GPC['time']['start']);
			$endtime = strtotime($_GPC['time']['end']);
			$condition .= ' AND c.createtime >= :starttime AND c.createtime <= :endtime ';
			$params[':starttime'] = $starttime;
			$params[':endtime'] = $endtime;
		}


		if ($_GPC['fade'] != '') {
			if (empty($_GPC['fade'])) {
				$condition .= ' AND c.openid=\'\'';
			}
			 else {
				$condition .= ' AND c.openid<>\'\'';
			}
		}


		if ($_GPC['replystatus'] != '') {
			if (empty($_GPC['replystatus'])) {
				$condition .= ' AND c.reply_content=\'\'';
			}
			 else {
				$condition .= ' AND c.append_content=\'\' and c.append_reply_content=\'\'';
			}
		}


		$list = pdo_fetchall('SELECT  c.*, o.ordersn,g.title,g.thumb FROM ' . tablename('ewei_shop_order_comment') . ' c  ' . ' left join ' . tablename('ewei_shop_goods') . ' g on c.goodsid = g.id  ' . ' left join ' . tablename('ewei_shop_order') . ' o on c.orderid = o.id  ' . ' WHERE 1 ' . $condition . ' ORDER BY createtime desc LIMIT ' . (($pindex - 1) * $psize) . ',' . $psize, $params);
		$total = pdo_fetchcolumn('SELECT count(*) FROM ' . tablename('ewei_shop_order_comment') . ' c  ' . ' left join ' . tablename('ewei_shop_goods') . ' g on c.goodsid = g.id  ' . ' left join ' . tablename('ewei_shop_order') . ' o on c.orderid = o.id  ' . ' WHERE 1 ' . $condition . ' ', $params);
		$pager = pagination($total, $pindex, $psize);
		include $this->template();
	}

	public function delete()
	{
		global $_W;
		global $_GPC;
		$id = intval($_GPC['id']);

		if (empty($id)) {
			$id = ((is_array($_GPC['ids']) ? implode(',', $_GPC['ids']) : 0));
		}


		$items = pdo_fetchall('SELECT id FROM ' . tablename('ewei_shop_order_comment') . ' WHERE id in( ' . $id . ' ) AND uniacid=' . $_W['uniacid']);

		foreach ($items as $item ) {
			pdo_update('ewei_shop_order_comment', array('deleted' => 1), array('id' => $item['id'], 'uniacid' => $_W['uniacid']));
			$goods = pdo_fetch('select id,thumb,title from ' . tablename('ewei_shop_goods') . ' where id=:id and uniacid=:uniacid limit 1', array(':id' => $item['goodsid'], ':uniacid' => $_W['uniacid']));
			plog('shop.comment.delete', '删除评价 ID: ' . $id . ' 商品ID: ' . $goods['id'] . ' 商品标题: ' . $goods['title']);
		}

		show_json(1, array('url' => referer()));
	}

	public function post()
	{
		global $_W;
		global $_GPC;
		$id = intval($_GPC['id']);
		$item = pdo_fetch('SELECT * FROM ' . tablename('ewei_shop_order_comment') . ' WHERE id =:id and uniacid=:uniacid limit 1 ', array(':id' => $id, ':uniacid' => $_W['uniacid']));
		$goods = pdo_fetch('select id,thumb,title from ' . tablename('ewei_shop_goods') . ' where id=:id and uniacid=:uniacid limit 1', array(':id' => $item['goodsid'], ':uniacid' => $_W['uniacid']));
		$order = pdo_fetch('select id,ordersn from ' . tablename('ewei_shop_order') . ' where id=:id and uniacid=:uniacid limit 1', array(':id' => $item['orderid'], ':uniacid' => $_W['uniacid']));

		if ($_W['ispost']) {
			$data = array('uniacid' => $_W['uniacid'], 'reply_content' => $_GPC['reply_content'], 'reply_images' => (is_array($_GPC['reply_images']) ? iserializer(m('common')->array_images($_GPC['reply_images'])) : iserializer(array())), 'append_reply_content' => $_GPC['append_reply_content'], 'append_reply_images' => (is_array($_GPC['append_reply_images']) ? iserializer($_GPC['append_reply_images']) : iserializer(array())));
			pdo_update('ewei_shop_order_comment', $data, array('id' => $id));
			plog('shop.comment.post', '回复商品评价 ID: ' . $id . ' 商品ID: ' . $goods['id'] . ' 商品标题: ' . $goods['title']);
			show_json(1, array('url' => merchUrl('shop/comment')));
		}


		include $this->template();
	}
}


?>