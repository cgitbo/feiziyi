<?php

class pool
{
    //错误信息
    private $error  = '';

    /**
     * @brief 池操作的构造函数
     * @param array $config => array('pool' => 池增减(正，负区分))
     */
    public function update($config)
    {
        if (!isset($config['pool']) || intval($config['pool']) == 0) {
            $this->error = '池格式不正确';
        } else {
            $finalNum = $this->editpool($config['pool']);
            if ($finalNum) {
                // 记录更新后的池数量
                $config['value_log'] = $finalNum;
                if (!$this->writeLog($config)) {
                    $this->error = '记录日志失败';
                }
            } else {
                $this->error = '池更新失败';
            }
        }

        return $this->error == '' ? true : false;
    }

    //返回错误信息
    public function getError()
    {
        return $this->error;
    }

    /**
     * @brief 日志记录
     * @param array $config => array('pool' => 池增减(正，负区分))
     */
    private function writeLog($config)
    {
        //修改poolLog表
        $poolLogObj    = new IModel('pool_log');
        $poolLogArray = array(
            'datetime' => ITime::getDateTime(),
            'value'   => $config['pool'],
            'value_log' => $config['value_log'],
            'o_id'    => $config['o_id'],
        );
        $poolLogObj->setData($poolLogArray);
        return $poolLogObj->add();
    }

    /**
     * @brief 池更新
     * @param int $pool   池数(正，负)
     */
    private function editpool($pool)
    {
        $confDB  = new IModel('system');
        $confRow = $confDB->getObj('keyword = "pool"');
        $finalNum = $confRow['value'] + $pool;

        $is_success = $confDB->setData([
            'value' => $finalNum,
        ])->update('keyword = "pool"');

        return $is_success ? $finalNum : false;
    }

    // 系统总池数量
    static function getSystempool()
    {
        // 系统总的池 数量
        $poolDB = new IModel('system');
        $poolRow = $poolDB->getObj('keyword = "pool"');

        return $poolRow['value'] ?? 0;
    }

    // 信息显示
    static function levelText($level = '')
    {
        $text = ['注册用户', 'VIP', '销售经理', '销售总监'];
        return $level ? $text[$level] : $text;
    }

    // 更新用户
    public static function updateTidData($orderRow)
    {
        if ($orderRow['pay_status'] != 1 || $orderRow['_hash']) return;

        // 实际要下的单用户ID 没tid 就是当前uid
        $curID = $orderRow['tid'] ? $orderRow['tid'] : $orderRow['user_id'];

        $userDB = new IModel('user');
        $userRow  = $userDB->getObj($curID);

        // 更新用户下单次数
        $userDB->setData(['times' => $userRow['times'] + 1])->update($curID);

        // 判断自己是不是VIP 如果不是 升为VIP
        if ($userRow['level'] == 0) {
            $userDB->setData(['level' => 1])->update($curID);
        }

        // 邀请人ID 如果用户自身购买 则存在fid 否则就是当前uid
        $fid = $orderRow['fid'] ? $orderRow['fid'] : $orderRow['user_id'];

        // 判断是否达到升级要求
        pool::updateUserLevel($fid);

        // 增加池数量
        $mul = 0.05;
        $poolObj = new Pool();
        $poolObj->update([
            'pool' => $orderRow['real_amount'] * $mul,
            'o_id' => $orderRow['id'],
        ]);

        $orderObj = new IModel('order');
        $orderObj->setData(['_hash' => '1'])->update($orderRow['id']);

        // 如果是 fid 说明用户是自身购买 tid 说明是经理帮用户购买
        $cid = $orderRow['tid'] ? $orderRow['tid'] : $orderRow['fid'];
        if (!$cid) return;

        // 当前tid增加积分
        $pointConfig = array(
            'user_id' => $cid,
            'point'   => $orderRow['point'],
            'log'     => '成功购买了订单号：' . $orderRow['order_no'] . '中的商品,奖励积分' . $orderRow['point'],
        );
        $pointObj = new Point();
        $pointObj->update($pointConfig);
    }

    // 升级方法
    static function updateUserLevel($fid)
    {
        $userDB = new IModel('user');
        $userRow  = $userDB->getObj($fid);

        if ($userRow['level'] == 0) return;

        // 查看当前邀请了VIP数量。或者 销售经理数量
        $fidVipCount = $userDB->getObj('level = 1 and fid =' . $fid, 'sum(times) as vipCount');

        $fidManegerCount = $userDB->getObj('level = 2 and fid =' . $fid, 'sum(times) as vipCount');

        if ($userRow['level'] == 1 && $fidVipCount['vipCount'] >= 10) {
            $userDB->setData(['level' => 2])->update($fid);
        } else if ($userRow['level'] == 2 && $fidManegerCount['vipCount'] >= 5) {
            $userDB->setData(['level' => 3])->update($fid);
        }
    }
}
