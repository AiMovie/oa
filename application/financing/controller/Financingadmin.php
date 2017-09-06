<?php
// +----------------------------------------------------------------------
// | 功能：融资按揭管理后台
// +----------------------------------------------------------------------
// | 作者：程龙飞
// +----------------------------------------------------------------------
// | 日期：2017-6-28 
// +----------------------------------------------------------------------
namespace app\Financing\Controller;

use app\common\controller\Auth;
use think\Db;
class Financingadmin extends Auth
{
    //配置信息
    public function Setting(){
        if(request()->isAjax())
        {
            $options = input('options/a');
            $options['check_open'] = request()->post('check_open',0,'intval');
            $options['see_open'] = request()->post('see_open',0,'intval');
            Db::name('options')->where(array('option_name'=>'FinancingSetting'))->setField('option_value',serialize($options));
            $this->success('修改成功',url('setting'),1);
        }
        else
        {
            $sys = Db::name('options')->where(array('option_name'=>'FinancingSetting'))->value("option_value");
            if($sys)
            {
                $sys=unserialize($sys);
            }
            else
            {
                $sys =  [];
            }
                // q($sys);
            $this->assign('sys',$sys);
            return $this->fetch();
        }
    }

    //列表
    public function index(){

        $telphone = intval(input('telphone'));
        $compangy_name = trim(input('compangy_name'));
        $fianceing_typeid = intval(input('type'));
        $province = intval(input('province_id'));
        $city = intval(input('city_id'));
        
       
        if(!empty($telphone) ){
            $where['a.phone'] .=  $telphone;
        };
        if(!empty($compangy_name) ){
            $where['a.company_name'] .=  $compangy_name;
        };
        if(!empty($fianceing_typeid) ){
            $where['a.fianceing_typeid'] .=  $fianceing_typeid;
        };
        if(!empty($province) ){
            $where['a.province_id'] .=  $province;
        };
        if(!empty($city) ){
            $where['a.city_id'] .=  $city;
        };
    
        if(isset($where)){
            $list = Db::name('financing')
            ->alias('a')
            ->join('parameter b ', 'b.id= a.fianceing_id', 'LEFT')
            ->join('region c ','c.id= a.province_id','LEFT')
            ->join('region d ','d.id= a.city_id','LEFT')
            ->where($where)
            ->field('a.*,b.name,c.name as pro_name, d.name as city_name')
            ->order('a.fianceing_id desc')
            ->paginate(15);
            
        }else{
            $list = Db::name('financing')
            ->alias('a')
            ->join('parameter b ', 'b.id= a.fianceing_id', 'LEFT')
            ->join('region c ','c.id= a.province_id','LEFT')
            ->join('region d ','d.id= a.city_id','LEFT')
            ->field('a.*,b.name,c.name as pro_name, d.name as city_name')
            ->order('a.fianceing_id desc')
            ->paginate(15);
        }
        
        
        $province = Db::name('region')->where('type',1)->select();

        //驾校分类，证件类型9
        // $info  = Db::name('parameter')->where(['module'=>'9','parent_id'=>'0'])->field('id,name')->select();
        // $data = [];
        // foreach ($info as $k=> $v) {
        //     $data[] =  $v['id'];
        // }
        // $wherein = implode(',',$data);
        // $data    = Db::name('parameter')->where('parent_id','in',$wherein)->field('id,name,parent_id')->select();
        // $my_info    = $this->adsp_group($data,'parent_id'); 

      


        $this->assign('province', $province);
        $this->assign('page', $list->render());
        $this->assign('list', $list);
        // $this->assign('my_info', $my_info);
        return $this->fetch();
    }

    //参数设置
    public function paramsSetting(){
        $key = input('key');
    
        $where['module'] = 8;
        $where['parent_id'] = 0;
        if($key){
            $where['name'] .=$key;
        }
        $list = Db::name('parameter')->where($where)->select();
        $this->assign('list',$list);
     
        return $this->fetch();
        }

        //参数设置
    public function paramssettingadd(){
        //有参数提交
        if(request()->isAjax())
        {
            $data = [
                'module' => 8,//1机械买卖；2机械租赁；3租赁运输；4修理厂；5机械配件；6司机；7证件；8融资；9代办证件管理 10新机械  
                'name' => input('name'),
                'multi' => input('multi',0),
                'state' => input('state',0),
                'value' => '',
                'parent_id' => 0,
                'type'=>input('type'),
				'input_type'=>input('input_type'),
            ];

            Db::name('parameter')->insert($data);

            $this->success('添加成功',url('paramssetting'),1);
        }
        //没有参数提交显示页面
        else
        {
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
    //删除参数值
    public function driverdelete()
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
     * 修改参数值
     * @return mixed
     */
    public function paramssettingvalueedit()
    {
        $id = input('id');
        if(!id)
        {
            $this->redirect(url('paramssettingvalueadd',array('pid'=>input('pid'))));
        }

        //有参数提交
        if(request()->isAjax())
        {
            $data = [
                'module' => 9,//1机械买卖；2机械租赁；3租赁运输；4修理厂；5机械配件；6司机；7证件；8融资；
                'name' => input('name'),
                'multi' => 1,
                'state' => input('state',0),
                'value' => input('name'),
                'parent_id' => input('pid'),
            ];

            Db::name('parameter')->where(['id'=>$id])->update($data);


            $this->success('修改成功',url('paramssettingvalue',array('pid'=>input('pid'))),1);
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

    public function paramsSettingVlueAdd(){
         $pid = input('pid');
        if(empty($pid))
        {
            $this->redirect(url('paramsSetting'));
        }
        //有参数提交
        if(request()->isAjax())
        {
            $data = [
                'member_list_id' => $member_list_id,
                'type' => input('type'),
                'province_id' => input('province_id'),
                'city_id' => input('city_id'),
                'company_name' => input('company_name'),
                'telphone' => input('telphone'),
                'update_time' => time(),
                'shop_img_url' => $shop_img_url,
                'create_time' => time(),
                'is_effective' => input('is_effective',0),
            ];

            $data['is_check']=(input('is_check')==2)?2:1;

            $id =Db::name('financing')->insertGetId($data);
            $fileurl_tmp = input('fileurl_tmp/a');

            Db::name('parameter')->insert($data);


            $this->success('添加成功',url('paramssetting',array('pid'=>$pid)),1);
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
 public function paramssettingvalue(){
     $pid = input('pid');
     $data = Db::name('parameter')->where('parent_id',$pid)->select();
     $this->assign('list',$data);
     $this->assign('pid',$pid);
    return $this->fetch();

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
                'module' => 8,//1机械买卖；2机械租赁；3租赁运输；4修理厂；5机械配件；6司机；7证件；8融资；
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
     * 添加
     * @return mixed
     */
    public function financingadd()
    {


        if(request()->isAjax())
        {
        	$member_list_tel = input('member_list_tel');
            $member_list_id = Db::name('member_list')->where("member_list_tel",$member_list_tel)->value('member_list_id');
            if(!$member_list_id)
            {
                $this->error('不存在该用户',0,0);
            }
            $data = $this->_from();
            $data['createtime'] = time();
           $data['member_list_id'] = $member_list_id ;
            if(input('is_check')){
                $data['is_check'] = 2;
            }else{
                $data['is_check'] = 1;

            }
            Db::name('financing')->insert($data);
            $this->success('添加成功',url('index'),1);
        }
        else
        {
            
            $info  = Db::name('parameter')->where(['module'=>'8','parent_id'=>'0'])->field('id,name')->select();

            $data = [];
            foreach ($info as $k=> $v) {
                $data[] =  $v['id'];

            }
            $wherein = implode(',',$data);
            $data = Db::name('parameter')->where('parent_id','in',$wherein)->field('id,name,parent_id')->select();
            $list = $this->adsp_group($data,'parent_id');

            // 以id为条件
            $this->assign('list',$list);
            //查询所有的省及直辖市
            $province=Db::name('region')->where('type',1)->field('id,name')->select();
            $this->assign('province',$province);
            return $this->fetch();
        }
    }

     public function adsp_group($arr, $key){
        
        $grouped = [];

        foreach ($arr as $value) {
            $grouped[$value[$key]][] = $value;
        }

        if (func_num_args() > 2) {

            $args = func_get_args();
            foreach ($grouped as $key => $value) {
                $parms = array_merge([$value], array_slice($args, 2, func_num_args()));
                $grouped[$key] = call_user_func_array('array_group_by', $parms);
            }
        }

        return $grouped;
        
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
     * 状态激活
     */
    public function financing_active()
    {
        //获取要修改的id
        $id = input("x");

        $newsmechanics_db = db("financing");
        //判断此用户状态情况
        $status=$newsmechanics_db->where(array('fianceing_id'=>$id))->value('is_effective');
        if($status == 1){
            //禁止
            $statedata = array('is_effective'=>0);
            $auth_group=$newsmechanics_db->where(array('fianceing_id'=>$id))->setField($statedata);
            $this->success('未激活',1,1);
        }else{
            //开启
            $statedata = array('is_effective'=>1);
            $auth_group=$newsmechanics_db->where(array('fianceing_id'=>$id))->setField($statedata);
            $this->success('已激活',1,1);
        }
    }

    /**
     * 删除信息
     */
    public function financingdelete()
    {
        //获取要删除的ID
        $tid = input('id');
        $p = input('page');
        // 判断ID是否为空
        if(empty($tid)){
            $this->error('参数错误',Url('index',array('pid'=>input('pid'),'page'=>$p)),0);
        }
        //删除数据
        $res = Db::name('financing')->where('fianceing_id',$tid)->delete();
        // 判断是否删除成功
        if($res){
            $this->success('删除成功',Url('index',array('pid'=>input('pid'),'page'=>$p)),1);
        }
        else{
            $this->error('删除失败',Url('index',array('pid'=>input('pid'),'page'=>$p)),0);
        }
    }


    /**
     * 修改
     * @return mixed
     */
    public function financingedit()
    { 
         if(request()->isAjax()){

         	$member_list_tel = input('member_list_tel');
            $member_list_id = Db::name('member_list')->where("member_list_tel",$member_list_tel)->value('member_list_id');
            if(!$member_list_id)
            {
                $this->error('不存在该用户',0,0);
            }
            $id = intval(input('id'));
            $data = $this->_from();
            $data['reject_reason'] = trim(input('reject_reason'));
            $res = Db::name('financing')->where('fianceing_id',$id)->update($data);
            $this->success('修改成功',url('index'),1);
         }else{

        $id = input('id');

        // 内容
        $my_info = Db::name('financing')
            ->alias('a')
            ->join('member_list e','e.member_list_id = a.member_list_id')
            ->join('parameter b ', 'b.id= a.fianceing_id', 'LEFT')
            ->join('region c ','c.id= a.province_id','LEFT')
            ->join('region d ','d.id= a.city_id','LEFT')
            ->where('fianceing_id',$id)
            ->field('a.*,b.name,c.name as pro_name, d.name as city_name,e.member_list_tel')
            ->find();

            //qq($my_info);
        // 所有省
        $province = Db::name('region')->where('type', 1)->field('id,name')->select();

        // 对应的市
        //直辖市找到三级区县
        switch ($my_info['province_id']) {
            case '2':
                $my_info['province_id'] = 52;
                break;
            case '25':
                $my_info['province_id'] = 321;
                break;
            case '27':
                $my_info['province_id'] = 343;
                break;
            case '32':
                $my_info['province_id'] = 394;
                break;
            default:
                # code...
                break;
        }
        $city = Db::name('region')->where('pid', $my_info['province_id'])->field('id,name')->select();
        
        //驾校分类，证件类型9
         $info  = Db::name('parameter')->where(['module'=>'9','parent_id'=>'0'])->field('id,name')->select();
        $data = [];
        foreach ($info as $k=> $v) {
            $data[] =  $v['id'];
        }
        $wherein = implode(',',$data);
        $data    = Db::name('parameter')->where('parent_id','in',$wherein)->field('id,name,parent_id')->select();
        $list    = $this->adsp_group($data,'parent_id');
        $this->assign('list', $list);
        $this->assign('province', $province);
        $this->assign('city', $city);
        $this->assign('info',$info);
        $this->assign('my_info',$my_info);
        return $this->fetch();
    }
        
    }

   

     // 添加二级参数
    public function paramsSettingValueAdd(){
         $pid = input('pid');
        if(empty($pid))
        {
            $this->redirect(url('paramsSetting'));
        }
        //有参数提交
        if(request()->isAjax())
        {
            $data = [
                'module' => 8,//1机械买卖；2机械租赁；3租赁运输；4修理厂；5机械配件；6司机；7证件；8融资；9代办证件
                'name' => input('name'),
                'multi' => 1,
                'state' => input('state',0),
                'value' => input('name'),
                'parent_id' => $pid
            ];

            Db::name('parameter')->insert($data);


            $this->success('添加成功',url('paramssettingvalue',array('pid'=>$pid)),1);
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

    public function _from(){
        $data = [
                'fianceing_typeid' => intval(input('type')),
                'company_name'=>trim(input('company_name')),
                'phone'=>trim(input('telphone')),
                'province_id'=>intval(input('province_id')),
                'city_id'=>intval(input('city_id')),
                'is_check'=>intval(input('is_check')),
                'is_effective'=>intval(input('state')),
                'updatetime'=>time(),
                ];
        return  $data;
    }

}