<?php
// +----------------------------------------------------------------------
// | 功能：租赁管理后台
// +----------------------------------------------------------------------
// | 作者：程龙飞
// +----------------------------------------------------------------------
// | 日期：2017-6-28
// +----------------------------------------------------------------------
namespace app\lease\Controller;

use app\common\controller\Auth;
use think\Db;
class Leaseadmin extends Auth
{
    /**
     * 信息列表
     * @return mixed
     */
    public function index()
    {
        if ($_POST) {
            if (input('province_id')) {
                $map['a.province_id'] = input('province_id');
                $city = Db::name('region')->where('pid', $info['province_id'])->select();
                $this->assign('city_list', $city);
            }

            if (input('city_id')) {
                $map['a.city_id'] = input('city_id');
            }

            if (input('type')) {
                $map['a.type'] = input('type');
            }

            if (input('class_id')) {
                $map['a.class_id'] = input('class_id');
            }

            if (input('telphone')) {
                $map['a.telphone'] = input('telphone');
            }

            $list = Db::name('lease')
                ->alias('a')
                ->join('parameter b ', 'b.id= a.province_id', 'LEFT')
                ->join('region c ','c.id= a.province_id','LEFT')
                ->join('region d ','d.id= a.city_id','LEFT')
                ->join('member_list f ','f.member_list_id= a.member_list_id','LEFT')
                ->join('mechanical e ','e.id= a.mechanical_id','LEFT')
                ->join('brand g ','g.id= a.brand_id','LEFT')
                ->join('mechanical_model h ','h.id= a.mechanical_model_id','LEFT')
                ->where($map)
                ->order('create_time desc')
                ->field('a.*,b.name AS parameter_name,c.name AS province,d.name AS city,f.member_list_tel,e.mechanical_name,g.brand_name,h.model_name')
                ->paginate(15);
        } else {
            $list = Db::name('lease')
                ->alias('a')
                ->join('parameter b ', 'b.id= a.province_id', 'LEFT')
                ->join('region c ','c.id= a.province_id','LEFT')
                ->join('region d ','d.id= a.city_id','LEFT')
                ->join('member_list f ','f.member_list_id= a.member_list_id','LEFT')
                ->join('mechanical e ','e.id= a.mechanical_id','LEFT')
                ->join('brand g ','g.id= a.brand_id','LEFT')
                ->join('mechanical_model h ','h.id= a.mechanical_model_id','LEFT')
                ->join('parameter i ', 'i.id= a.type', 'LEFT')
                ->join('parameter j ', 'j.id= a.class_id', 'LEFT')
                ->order('create_time desc')
                ->field('a.*,b.name AS parameter_name,c.name AS province,d.name AS city,f.member_list_tel,e.mechanical_name,g.brand_name,h.model_name,i.name AS lease_type,j.name AS lease_class')
                ->paginate(15);
        }
//        p($list);
        //查询所有的省及直辖市
        $province=Db::name('region')->where('type',1)->field('id,name')->select();
        $this->assign('province',$province);
        //租赁类型
        $style = Db::name('parameter')->where("name",'类型')->value('id');
        $style_list = Db::name('parameter')->where("parent_id",$style)->select();
        $this->assign('style_list',$style_list);
        //类型
        $type = Db::name('parameter')->where("name",'机械类型')->value('id');
        $type_list = Db::name('parameter')->where("parent_id",$type)->select();
        $this->assign('type_list',$type_list);
        //分页
        $this->assign('page', $list->render());
        $this->assign('list', $list);
        return $this->fetch();
    }


    /**
     * 添加
     * @return mixed
     */
    public function LeaseAdd()
    {
        if($_POST)
        {
            $member_list_tel = input('member_list_tel');
            $member_list_id = Db::name('member_list')->where("member_list_tel",$member_list_tel)->value('member_list_id');
            if(!$member_list_id)
            {
                $this->error('不存在该用户',0,0);
            }
            $data = [
                'member_list_id' => $member_list_id,
                'type' => input('type'),
                'mechanical_id' => input('mechanical_id'),
                'brand_id' => input('brand_id'),
                'mechanical_model_id' => input('mechanical_model_id'),
                'class_id' => input('class_id'),
                'province_id' => input('province_id'),
                'city_id' => input('city_id'),
                'squares' => input('squares'),
                'tonnage' => input('tonnage'),
                'manufacture_date' => input('manufacture_date'),
                'hours' => input('hours'),
                'car_num' => input('car_num'),
                'name' => input('name'),
                'nation' => input('nation'),
                'telphone' => input('telphone'),
                'update_time' => time(),
                'create_time' => time(),
                'is_check' => input('is_check',0),
                'is_effective' => input('is_effective',0),
            ];
            Db::name('lease')->insert($data);
            $this->success('添加成功',url('index'),1);
        }
        else
        {
            //类型
            $type = Db::name('parameter')->where("name",'机械类型')->value('id');
            $type_list = Db::name('parameter')->where("parent_id",$type)->select();
            $this->assign('type_list',$type_list);
            //租赁类型
            $style = Db::name('parameter')->where("name",'类型')->value('id');
            $style_list = Db::name('parameter')->where("parent_id",$style)->select();
            $this->assign('style_list',$style_list);
            //类型列表
            $mechanical_list = Db::name('mechanical')->field('id,mechanical_name')->select();
            $this->assign('mechanical_list',$mechanical_list);
            //查询所有的省及直辖市
            $province=Db::name('region')->where('type',1)->field('id,name')->select();
            $this->assign('province',$province);
            return $this->fetch();
        }
    }


    /**
     * 修改
     * @return mixed
     */
    public function Leaseedit()
    {
        if ($_POST) {
            $mid = input('id');
            $member_list_tel = input('member_list_tel');
            $member_list_id = Db::name('member_list')->where("member_list_tel", $member_list_tel)->value('member_list_id');
            if (!$member_list_id) {
                $this->error('不存在该用户', 0, 0);
            }
            $data = [
                'member_list_id' => $member_list_id,
                'type' => input('type'),
                'mechanical_id' => input('mechanical_id'),
                'brand_id' => input('brand_id'),
                'mechanical_model_id' => input('mechanical_model_id'),
                'class_id' => input('class_id'),
                'province_id' => input('province_id'),
                'city_id' => input('city_id'),
                'squares' => input('squares'),
                'tonnage' => input('tonnage'),
                'manufacture_date' => input('manufacture_date'),
                'hours' => input('hours'),
                'car_num' => input('car_num'),
                'name' => input('name'),
                'nation' => input('nation'),
                'telphone' => input('telphone'),
                'update_time' => time(),
                'create_time' => time(),
                'is_check' => input('is_check',0),
                'is_effective' => input('is_effective',0),
            ];
            $res = Db::name('lease')->where('id', $mid)->update($data);
            $this->success('修改成功', url('index'), 1);
        } else {
            $map['a.id'] = input('id');
            $result = Db::name('lease')
                ->alias('a')
                ->join('parameter b ', 'b.id= a.province_id', 'LEFT')
                ->join('region c ', 'c.id= a.province_id', 'LEFT')
                ->join('region e ', 'e.id= a.city_id', 'LEFT')
                ->join('member_list f ', 'f.member_list_id= a.member_list_id', 'LEFT')
                ->join('region g ', 'g.id= a.city_id', 'LEFT')
                ->join('mechanical h ','h.id= a.mechanical_id','LEFT')
                ->join('brand i ','i.id= a.brand_id','LEFT')
                ->join('mechanical_model j ','j.id= a.mechanical_model_id','LEFT')
                ->order('create_time desc')
                ->where($map)
                ->field('a.*,b.name AS parameter_name,f.member_list_tel,h.mechanical_name,i.brand_name,j.model_name')
                ->find();
            $this->assign('result', $result);
//            p($result);
            //籍贯
            //查询所有的省及直辖市
            $province = Db::name('region')->where('type', 1)->field('id,name')->select();
            $this->assign('province', $province);
            //查询当前省下的市区列表
            $province_id = Db::name('lease')->where('id', $map['a.id'])->value('province_id');
            if ($province_id) {
                $zhi = array(2, 25, 27, 32);
                if (in_array($province_id, $zhi)) {
                    $id = Db::name('region')->where('pid', $province_id)->field('id,name')->value('id');
                    $citylist = Db::name('region')->where('pid', $id)->field('id,name')->select();
                } else {
                    $citylist = Db::name('region')->where('pid', $province_id)->field('id,name')->select();
                }
            }
            $this->assign('citylist', $citylist);
            //类型
            $type = Db::name('parameter')->where("name",'机械类型')->value('id');
            $type_list = Db::name('parameter')->where("parent_id",$type)->select();
            $this->assign('type_list',$type_list);
            //租赁类型
            $style = Db::name('parameter')->where("name",'类型')->value('id');
            $style_list = Db::name('parameter')->where("parent_id",$style)->select();
            $this->assign('style_list',$style_list);
            //类型列表
            $mechanical_list = Db::name('mechanical')->field('id,mechanical_name')->select();
            $this->assign('mechanical_list',$mechanical_list);
//            p($mechanical_list);
            return $this->fetch();
        }
    }

    /**
     * 删除信息
     */
    public function LeaseDelete()
    {
        //获取要删除的ID
        $tid = input('id');
        $p = input('page');
        // 判断ID是否为空
        if(empty($tid)){
            $this->error('参数错误',Url('index',array('page'=>$p)),0);
        }
        //删除数据
        $res = Db::name('lease')->where('id',$tid)->delete();
        // 判断是否删除成功
        if($res){
            $this->success('删除成功',Url('index',array('page'=>$p)),1);
        }
        else{
            $this->error('删除失败',Url('index',array('page'=>$p)),0);
        }
    }

    /**
     * 状态激活
     */
    public function Lease_active()
    {
        //获取要修改的id
        $id = input("x");
        $newsmechanics_db = db("lease");
        //判断此用户状态情况
        $status=$newsmechanics_db->where(array('id'=>$id))->value('is_effective');
        if($status == 1){
            //禁止
            $statedata = array('is_effective'=>0);
            $auth_group=$newsmechanics_db->where(array('id'=>$id))->setField($statedata);
            $this->success('未激活',1,1);
        }else{
            //开启
            $statedata = array('is_effective'=>1);
            $auth_group=$newsmechanics_db->where(array('id'=>$id))->setField($statedata);
            $this->success('已激活',1,1);
        }
    }

    /**
     * 根据类型、品牌获取型号
     */
    public function getModle()
    {
        $mechanical_id = input('mechanical_id');
        $brand_id = input('brand_id');
        $map = [
            'mechanical_id' => $mechanical_id,
            'brand_id' => $brand_id
        ];
        $list = Db::name('mechanical_model')->where($map)->select();
        $html = '<option value="">请选择</option>';
        foreach($list as $key => $value)
        {
            $html .= "<option value=\"{$value['id']}\">{$value['model_name']}</option>";
        }
        echo $html;exit;

    }


    /**
     * 根据省获取城市
     */
    public function getCity()
    {
        $province_id = input('province_id');
        $zhi=array(2,25,27,32);
        if(in_array($province_id,$zhi)){
            $id = Db::name('region')->where('pid',$province_id)->field('id,name')->value('id');
            $list = Db::name('region')->where('pid',$id)->field('id,name')->select();
        }else{
            $list = Db::name('region')->where('pid',$province_id)->field('id,name')->select();
        }
        $html = '<option value="">请选择</option>';
        foreach($list as $key => $value)
        {
            $html .= "<option value=\"{$value['id']}\">{$value['name']}</option>";
        }
        echo $html;exit;
    }


    /**
     * 配置信息
     * @return mixed
     */
    public function setting()
    {
        if(request()->isAjax())
        {
            $options = input('options/a');
            $options['check_open'] = request()->post('check_open',0,'intval');
            $options['see_open'] = request()->post('see_open',0,'intval');
            Db::name('options')->where(array('option_name'=>'LeaseSetting'))->setField('option_value',serialize($options));
            $this->success('修改成功',url('setting'),1);
        }
        else
        {
            $sys = Db::name('options')->where(array('option_name'=>'LeaseSetting'))->value("option_value");

            if($sys)
            {
                $sys=unserialize($sys);
            }
            else
            {
                $sys =  [];
            }
            $this->assign('sys',$sys);
            return $this->fetch();
        }
    }


    /**
     * 参数管理
     * @return mixed
     */
    public function paramsSetting()
    {
        $map = [
            'module' => 3, //1机械买卖；2机械租赁；3租赁运输；4修理厂；5机械配件；6司机；7证件；8融资；
            'parent_id' => 0
        ];
        $list = Db::name('parameter')->where($map)->select();

        $this->assign('list',$list);

        return $this->fetch();
    }


    /**
     * 添加参数
     * @return mixed
     */
    public function paramsSettingAdd()
    {
        //有参数提交
        if(request()->isAjax())
        {
            $data = [
                'module' => 3,//1机械买卖；2机械租赁；3租赁运输；4修理厂；5机械配件；6司机；7证件；8融资；
                'name' => input('name'),
                'multi' => input('multi',0),
                'state' => input('state',0),
                'value' => '',
                'parent_id' => 0,
                 'type'=>input('type'),
                'input_type'=>input('input_type'),
            ];

            Db::name('parameter')->insert($data);


            $this->success('添加成功',url('paramsSetting'),1);
        }
        //没有参数提交显示页面
        else
        {
            return $this->fetch();
        }
    }


    /**
     * 修改参数
     * @return mixed
     */
    public function paramsSettingEdit()
    {
        $id = input('id');
        if(!id)
        {
            $this->redirect(url('paramsSetting'));
        }
        //有参数提交
        if(request()->isAjax())
        {
            $data = [
                'module' => 3,//1机械买卖；2机械租赁；3租赁运输；4修理厂；5机械配件；6司机；7证件；8融资；
                'name' => input('name'),
                'multi' => input('multi',0),
                'state' => input('state',0),
                'value' => '',
                'parent_id' => 0,
                 'type'=>input('type'),
                'input_type'=>input('input_type'),
            ];

            Db::name('parameter')->where(['id'=>$id])->update($data);


            $this->success('修改成功',url('paramsSetting'),1);
        }
        //没有参数提交显示页面
        else
        {
            $info = Db::name('parameter')->where('id',$id)->find();
            $this->assign('info',$info);
            $this->assign('id',$id);
            return $this->fetch();
        }
    }


    /**
     * 删除参数
     */
    public function paramsSettingDelete()
    {
        $id = input('id');
        if (empty($id)){
            $this->error('数据不存在',url('paramsSetting'),0);
        }
        Db::name('parameter')->where(array('id'=>input('id')))->delete();

        if($rst!==false){
            $this->success('删除成功',url('paramsSetting'),1);
        }else{
            $this->error('删除失败',url('paramsSetting'),0);
        }
    }

    /**
     * 查看参数值
     * @return mixed
     */
    public function paramsSettingValue()
    {
        $pid = input('pid');
        if(empty($pid))
        {
            $this->redirect(url('paramsSetting'));
        }
        $name = Db::name('parameter')->where('id',$pid)->value('name');
        $this->assign('name',$name);
        $this->assign('pid',$pid);

        $map = [
            'parent_id' => $pid,
        ];
        $list = Db::name('parameter')->where($map)->select();
        $this->assign('list',$list);

        return $this->fetch();
    }


    /***
     * 添加参数值
     * @return mixed
     */
    public function paramsSettingValueAdd()
    {
        $pid = input('pid');
        if(empty($pid))
        {
            $this->redirect(url('paramsSetting'));
        }
        //有参数提交
        if(request()->isAjax())
        {
            $data = [
                'module' => 3,//1机械买卖；2机械租赁；3租赁运输；4修理厂；5机械配件；6司机；7证件；8融资；
                'name' => input('name'),
                'multi' => 1,
                'state' => input('state',0),
                'value' => input('name'),
                'parent_id' => $pid
            ];

            Db::name('parameter')->insert($data);


            $this->success('添加成功',url('paramsSettingValue',array('pid'=>$pid)),1);
        }
        //没有参数提交显示页面
        else
        {
            $name = Db::name('parameter')->where('id',$pid)->value('name');
            $this->assign('name',$name);
            $this->assign('pid',$pid);
            return $this->fetch();
        }
    }


    /**
     * 修改参数值
     * @return mixed
     */
    public function paramssettingvalueedit()
    {
        $id = input('id');
        if(!id)
        {
            $this->redirect(url('paramsSettingValue',array('pid'=>input('pid'))));
        }

        //有参数提交
        if(request()->isAjax())
        {
            $data = [
                'module' => 3,//1机械买卖；2机械租赁；3租赁运输；4修理厂；5机械配件；6司机；7证件；8融资；
                'name' => input('name'),
                'multi' => 1,
                'state' => input('state',0),
                'value' => input('name'),
                'parent_id' => input('pid'),
            ];

            Db::name('parameter')->where(['id'=>$id])->update($data);


            $this->success('修改成功',url('paramsSetting'),1);
        }
        //没有参数提交显示页面
        else
        {
            $info = Db::name('parameter')->where('id',$id)->find();
            $this->assign('info',$info);
            $this->assign('id',$id);
            $name = Db::name('parameter')->where('id',input('pid'))->value('name');
            $this->assign('name',$name);
            return $this->fetch();
        }
    }


    /**
     * 修改参数状态（开启关闭）
     */
    public function paramsSettingState()
    {
        $id = input('x');
        $status = Db::name('parameter')->where(array('id'=>$id))->value('state');//判断当前状态情况
        if($status==1){
            $statedata = array('state'=>0);
            $auth_group=Db::name('parameter')->where(array('id'=>$id))->setField($statedata);
            $this->success('状态禁止',1,1);
        }else{
            $statedata = array('state'=>1);
            $auth_group=Db::name('parameter')->where(array('id'=>$id))->setField($statedata);
            $this->success('状态开启',1,1);
        }
    }

    /**
     * 删除参数
     */
    public function paramsSettingValueDelete()
    {
        $id = input('id');
        if (empty($id)){
            $this->error('数据不存在',url('paramsSetting'),0);
        }
        Db::name('parameter')->where(array('id'=>input('id')))->delete();

        if($rst!==false){
            $this->success('删除成功',url('paramsSettingValue',array('pid'=>input('pid'))),1);
        }else{
            $this->error('删除失败',url('paramsSettingValue',array('pid'=>input('pid'))),0);
        }
    }
}