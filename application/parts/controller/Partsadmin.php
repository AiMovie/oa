<?php
// +----------------------------------------------------------------------
// | 功能：机械配件
// +----------------------------------------------------------------------
// | 作者：田泽辉
// +----------------------------------------------------------------------
// | 日期：2017-7-24
// +----------------------------------------------------------------------
namespace app\parts\Controller;

use app\common\controller\Auth;
use think\Db;
class Partsadmin extends Auth
{
    //配置信息
    public function Setting(){

        if(request()->isAjax())
        {
            $options = input('options/a');
            $options['check_open'] = request()->post('check_open',0,'intval');
            $options['see_open'] = request()->post('see_open',0,'intval');
            Db::name('options')->where(array('option_name'=>'partsSetting'))->setField('option_value',serialize($options));
            $this->success('修改成功',url('setting'),1);
        }
        else
        {
            $sys = Db::name('options')->where(array('option_name'=>'partsSetting'))->value("option_value");
           
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

    //列表
    public function index(){

        //拼接搜索条件
        $car_tid = input('car_tid');
        $parts_id = input('parts_tid');
        $service_tid = input('service_tid');
        $province = input('province_id');
        $city = input('city_id');
        

        if(!empty($car_tid) ){
            $where['a.car_tid'] .=  $car_tid;
        };
        if(!empty($parts_id) ){
            $where['a.parts_id'] .=  $parts_id;
        };
        if(!empty($service_tid) ){
            $where['a.service_tid'] .=  $service_tid;
        };
        if(!empty($province) ){
            $where['a.province_id'] .=  $province;
        };
        if(!empty($city) ){
            $where['a.city_id'] .=  $city;
        };

        if(isset($where)){
          
           $lists = Db::name('parts')
            ->alias('a')
            ->join('member_list e','e.member_list_id = a.member_list_id')
            ->join('region c ','c.id= a.province_id','LEFT')
            ->join('region d ','d.id= a.city_id','LEFT')
            ->join('parameter b ', 'b.id= a.parts_tid', 'LEFT')
            ->join('parameter f ', 'f.id= a.service_tid', 'LEFT')
            ->where($where)
            ->order('a.parts_id desc')
            ->field('a.*, e.member_list_username, c.name as prename, d.name as cityname, b.name as parts_name, f.name as service_name')
            ->paginate(15);

        }else{
            $lists = Db::name('parts')
            ->alias('a')
            ->join('member_list e','e.member_list_id = a.member_list_id')
            ->join('region c ','c.id= a.province_id','LEFT')
            ->join('region d ','d.id= a.city_id','LEFT')
            ->join('parameter b ', 'b.id= a.parts_tid', 'LEFT')
            ->join('parameter f ', 'f.id= a.service_tid', 'LEFT')
            ->order('a.parts_id desc')
            ->field('a.*, e.member_list_username,c.name as prename, d.name as cityname, b.name as parts_name, f.name as service_name')
            // ->select();
            ->paginate(15);
            
            // ee($list);


        }
         $list = $lists->items();
         foreach($list as $key => $value)
        {
            $list[$key]['parts_tid'] = Db::name('parameter')->field('name')->where(['id'=>['IN',$value['parts_tid']]])->select();
            $list[$key]['service_tid'] = Db::name('parameter')->field('name')->where(['id'=>['IN',$value['service_tid']]])->select();
        }
        
        
			$province = Db::name('region')->where('type',1)->select();
			
			//参数类型
			$info     = Db::name('parameter')->where(['module'=>'5','parent_id'=>'0'])->field('id,name')->select();
			$data     = [];
        foreach ($info as $k=> $v) {
            $data[] =  $v['id'];
        }
		$wherein = implode(',',$data);
		$data    = Db::name('parameter')->where('parent_id','in',$wherein)->field('id,name,parent_id')->select();
		$my_info = $this->adsp_group($data,'parent_id'); 
        // q($list);
        $this->assign('province', $province);
        $this->assign('page', $lists->render());
        $this->assign('list', $list);
        $this->assign('my_info', $my_info);

        return $this->fetch();
    }

    //参数设置
    public function paramsSetting(){
        
        $where['module']= '5';
        $where['parent_id']= 0;
        $key = input('key');
        if($key){
            $list = Db::name('parameter')->where($where)->where('name','like','%'.$key.'%')->select();
        }else{
            $list = Db::name('parameter')->where($where)->select();
        }
       
        // die;
        $this->assign('keyword',$key);
        $this->assign('list',$list);
        return $this->fetch();
        }

        //参数设置
    public function paramssettingadd(){
        //有参数提交
        if(request()->isAjax())
        {
            $data = [
                'module' => 5,//1机械买卖；2机械租赁；3租赁运输；4修理厂；5机械配件；6司机；7证件；8融资；9代办证件管理
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
                'module' => 5,//1机械买卖；2机械租赁；3租赁运输；4修理厂；5机械配件；6司机；7证件；8融资；
                'name' => input('name'),
                'multi' => 1,
                'state' => input('state',0),
                'value' => input('name'),
                'parent_id' => input('pid'),
            ];

            Db::name('parameter')->where(['id'=>$id])->update($data);


            $this->success('修改成功',url('paramssettingvalueadd'),1);
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
                'module' => 5,//1机械买卖；2机械租赁；3租赁运输；4修理厂；5机械配件；6司机；7证件；8融资；9代办证件
                'name' => input('name'),
                'multi' => 1,
                'state' => input('state',0),
                'value' => input('name'),
                'parent_id' => $pid
            ];

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
     $key = input('key');
    if($key){
        $data = Db::name('parameter')->where('parent_id',$pid)->where('name','like','%'.$key.'%')->select();

    }else{
     $data = Db::name('parameter')->where('parent_id',$pid)->select();        
    }   
     // echo Db::getLastSql();die;
     $this->assign('list',$data);
     $this->assign('pid',$pid);
    $this->assign('keyword',$key);  
     // dump($data);
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
                'module' => 5,//1机械买卖；2机械租赁；3租赁运输；4修理厂；5机械配件；6司机；7证件；8融资；
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
    public function certificatesAdd()
    {
        if(request()->isAjax())
        {
           $member_list_tel = input('member_list_tel');
            $member_list_id = Db::name('member_list')->where("member_list_tel",$member_list_tel)->value('member_list_id');
            if(!$member_list_id)
            {
                $this->error('不存在该用户',0,0);
            }

            $parts_type = $_POST['parts_type'];
            $service_type = $_POST['service_type'];
            
            if(is_array($parts_type))
            {
                $parts_type = implode(',',$parts_type);
            }
            if(is_array($service_type))
            {
                $service_type = implode(',',$service_type);
            }
            //图片处理
            $file = request()->file('file0');
            if($file)
            {
                // 移动到框架应用根目录/public/data/uploads/ 目录下
                $info = $file->move(ROOT_PATH . 'public' . DS . 'data'. DS . 'uploads');
                if($info) {
                    $shop_img_url=substr(\think\Config::get('UPLOAD_DIR'),1).$info->getSaveName();//如果上传成功则完成路径拼接
                }else{
                    $this->error($file->getError(),url('index'),0);//否则就是上传错误，显示错误原因
                }
            }
            //图片处理结束

            $data = [
                'car_tid' =>input('car_type'),
                'parts_tid' => $parts_type,
                'service_tid' => $service_type,
                'province_id' => input('province'),
                'city_id' => input('city'),
                'username'=> trim(input('username')),
                'phone' => intval(input('phone')),
                'title' => trim(input('title')),
                'content' => trim(input('content')),
                'cover_image' => $shop_img_url,
                'updatetime' => time(),
                'createtime' => time(),
                'is_check' => input('is_check',1),
                'is_effective' => input('is_effective',1),
                'member_list_id'=>$member_list_id,
            ];
           // ee($data);
            Db::name('parts')->insert($data);
            $this->success('添加成功',url('index'),1);
        }
        else
        {
            
            //参数信息 前台以id为条件
            $info = Db::name('parameter')
                            ->where(['module'=>'5','parent_id'=>'0','state'=>1])
                            // ->field('id,name,state,multi')
                            ->select();
            // qq($info);
            // 找出父id
            $data = [];
            foreach ($info as $k=> $v) {
                $data[] =  $v['id'];

            }
            $wherein = implode(',',$data);
            // 以父id为条件 查询子参数
            $data    = Db::name('parameter')
                                ->where('state',1)->where('parent_id','in',$wherein)
                                ->field('id,module,name,value,parent_id,state')
                                ->select();
            // 数据拼接组装
            foreach ($data as $key => $value) {
                foreach ($info  as $k => $v) {
                    if($value['parent_id'] == $v['id']){
                        $data[$key]['typename'] = $v['name'];
                        $data[$key]['my_multi'] = $v['multi'];
                        // echo $key.$v['name'].'<br>';
                    }
                }
            }
            $parameter_info    = $this->adsp_group($data,'parent_id');
             foreach ($parameter_info as $k => $v) {
               
                $parame["$k"] = $v;
                $parame["$k"]['multi'] .= $v[0]['my_multi'];
            }
            // q($parame);
            // $parameter_info    = $this->adsp_group($data,'typename');
            // $parameter_info    = $this->adsp_group($parameter_info,'typename');
            // foreach ($parameter_info as $k => $v) {
               
            //     $parame["$k".'-'.$v[0]['my_multi']] = $v;
            // }
            /*$parame["$k".'-'.$v[0]['my_multi']] 
            * $k string    是父级参数的name值
            * $$v[0]['my_multi']  int  代表父级参数单选还是多选  0单选 1多选
            */
          

            $this->assign('parameter_info',$parame);

            //配件类型
            $part_id = Db::name('parameter')->where("name",'配件类型')->where("module",5)->where("state",1)->value('id');
            $part_list = Db::name('parameter')->where("parent_id",$part_id)->where("state",1)->select();
            $this->assign('part_list',$part_list);
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
    public function driver_active()
    {
        //获取要修改的id
        $id = input("x");
        $newsmechanics_db = db("parts");
        //判断此用户状态情况
        $status=$newsmechanics_db->where(array('parts_id'=>$id))->value('is_effective');
        if($status == 1){
            //禁止
            $statedata = array('is_effective'=>0);
            $auth_group=$newsmechanics_db->where(array('parts_id'=>$id))->setField($statedata);
            $this->success('未激活',1,1);
        }else{
            //开启
            $statedata = array('is_effective'=>1);
            $auth_group=$newsmechanics_db->where(array('parts_id'=>$id))->setField($statedata);
            $this->success('已激活',1,1);
        }
    }

        /**
     * 删除信息
     */
    public function driverDelete()
    {
        //获取要删除的ID
        $tid = input('id');
        $p = input('page');
        // 判断ID是否为空
        if(empty($tid)){
            $this->error('参数错误',Url('index',array('page'=>$p)),0);
        }
        //删除数据
        $res = Db::name('parts')->where('parts_id',$tid)->delete();
        // 判断是否删除成功
        if($res){
            $this->success('删除成功',Url('index',array('page'=>$p)),1);
        }
        else{
            $this->error('删除失败',Url('index',array('page'=>$p)),0);
        }
    }

    /**
     * 修改
     * @return mixed
     */
    public function certificatesedit()
    {   
        $id = input('parts_id');
 		
        // 内容
        $my_info = Db::name('parts')
            ->alias('a')
            ->join('member_list e','e.member_list_id = a.member_list_id')
            ->join('region c ','c.id= a.province_id','LEFT')
            ->join('region d ','d.id= a.city_id','LEFT')
            ->join('parameter b ', 'b.id= a.parts_tid', 'LEFT')
            ->join('parameter f ', 'f.id= a.service_tid', 'LEFT')
            ->where('a.parts_id',$id)
            ->field('a.*, e.member_list_tel, c.name as prename, d.name as cityname, b.name as parts_name, f.name as service_name')
            ->find();
           
            // 视图层去判断多选框是否选中  如果复选框的value 在这个字符串里就选中
           (string) $parts_tid_string =$my_info['parts_tid']; 
            (string) $service_tid_string =$my_info['service_tid']; 
            $this->assign('parts_tid_string',$parts_tid_string);
            $this->assign('service_tid_string',$service_tid_string);
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
        
        //参数分类
          //参数信息 前台以id为条件
            $info = Db::name('parameter')
                            ->where(['module'=>'5','parent_id'=>'0','state'=>1])
                            // ->field('id,name,state,multi')
                            ->select();
            // qq($info);
            // 找出父id
            $data = [];
            foreach ($info as $k=> $v) {
                $data[] =  $v['id'];

            }
            $wherein = implode(',',$data);
            // 以父id为条件 查询子参数
            $data    = Db::name('parameter')
                                ->where('state',1)->where('parent_id','in',$wherein)
                                ->field('id,module,name,value,parent_id,state')
                                ->select();
            // 数据拼接组装 把父名称和单选 复选放进去
            foreach ($data as $key => $value) {
                foreach ($info  as $k => $v) {
                    if($value['parent_id'] == $v['id']){
                        $data[$key]['typename'] = $v['name'];
                        $data[$key]['my_multi'] = $v['multi'];
                        // echo $key.$v['name'].'<br>';
                    }
                }
            }
            $parameter_info    = $this->adsp_group($data,'parent_id');
             foreach ($parameter_info as $k => $v) {
               
                $parame["$k"] = $v;
                $parame["$k"]['multi'] .= $v[0]['my_multi'];
            }
        //配件类型
        $part_id = Db::name('parameter')->where("name",'配件类型')->where("module",5)->where("state",1)->value('id');
        $part_list = Db::name('parameter')->where("parent_id",$part_id)->where("state",1)->select();
        $this->assign('part_list',$part_list);
        
        $this->assign('parameter_info', $parame);
        $this->assign('province', $province);
        $this->assign('city', $city);
        $this->assign('info',$info);
        $this->assign('my_info',$my_info);
    
        return $this->fetch();
        
    }

    public function partsupdate(){
        $data = $input;

         if(request()->isAjax())
        {
            $member_list_tel = input('member_list_tel');
            $member_list_id = Db::name('member_list')->where("member_list_tel",$member_list_tel)->value('member_list_id');
            if(!$member_list_id)
            {
                $this->error('不存在该用户',0,0);
            }
            // ee($_POST);
             $parts_type = $_POST['parts_type'];
            $service_type = $_POST['service_type'];
            
            if(is_array($parts_type))
            {
                $parts_type = implode(',',$parts_type);
            }
            if(is_array($service_type))
            {
                $service_type = implode(',',$service_type);
            }

            $checkpic=input('checkpic');
            $oldcheckpic=input('oldcheckpic');
            //如果有新上传
            if($checkpic!=$oldcheckpic)
            {
                $file = request()->file('file0');

                if($file)
                {
                    // 移动到框架应用根目录/public/data/uploads/ 目录下
                    $info = $file->move(ROOT_PATH . 'public' . DS . 'data'. DS . 'uploads');
                    if($info)
                    {
                        $img_url=substr(\think\Config::get('UPLOAD_DIR'),1).$info->getSaveName();//如果上传成功则完成路径拼接
                    }else{
                        $this->error($file->getError(),url('repairedit',['id'=>$id]),0);//否则就是上传错误，显示错误原因
                    }
                }
                $shop_img_url = $img_url;
            }
            else
            {
                //原有图片
                $shop_img_url = input('oldcheckpicname');
            }


            $data = [
                'car_tid' =>input('car_type'),
                'parts_tid' => $parts_type,
                'service_tid' => $service_type,
                'province_id' => input('province'),
                'city_id' => input('city'),
                'username'=> trim(input('username')),
                'phone' => input('phone'),
                'title' => trim(input('title')),
                'content' => trim(input('content')),
                'cover_image' => $shop_img_url,
                'updatetime' => time(),
                'reject_reason'=>trim(input('reject_reason')),
                'is_check' => input('is_check'),
                'is_effective' => input('is_effective'),
            ];
        }

         Db::name('parts')->where('parts_id',input('parts_id'))->update($data);
            $this->success('修改成功',url('index'),1);
    }
}