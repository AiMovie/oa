<?php
// +----------------------------------------------------------------------
// | 功能：代办证件管理后台
// +----------------------------------------------------------------------
// | 作者：程龙飞
// +----------------------------------------------------------------------
// | 日期：2017-6-28 
// +----------------------------------------------------------------------
namespace app\certificates\Controller;

use app\common\controller\Auth;
use think\Db;
class Certificatesadmin extends Auth
{
	//配置信息
	public function Setting(){
		if(request()->isAjax())
        {
            $options = input('options/a');
            $options['check_open'] = request()->post('check_open',0,'intval');
            $options['see_open'] = request()->post('see_open',0,'intval');
            Db::name('options')->where(array('option_name'=>'certificatesSetting'))->setField('option_value',serialize($options));
            $this->success('修改成功',url('setting'),1);
        }
        else
        {
            $sys = Db::name('options')->where(array('option_name'=>'certificatesSetting'))->value("option_value");
            	//dump($sys);
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
        // q($_POST);
		$certificate = $_POST['certificate'];
		$school = $_POST['schoolname'];
		$province = $_POST['province_id'];
		$city_id = $_POST['city_id'];
        $where = "1=1";
		if(!empty($certificate) ){
            //%% 这样模糊查询 如果输入id是51 数据库有512,513，都会匹配出来 应该怎么办
            $where .= " and a.certificate_typeid like '%".$certificate."%'" ;
		};
		if(!empty($school) ){
            $where .= " and a.schoolname like '%".$school."%'";

		};
		if(!empty($province) ){
            $where .=" and a.province_id = ".$province;
		};
		if(!empty($city_id) ){
            $where .= " and a.city_id = ".$city_id;
		};
        // q($where);eee
		if(isset($where)){
			$lists = Db::name('certificate')
			->alias('a')
			->join('parameter b ', 'b.id= a.certificate_typeid', 'LEFT')
			->join('parameter f ', 'f.id= a.schoolname', 'LEFT')
			->join('region c ','c.id= a.province_id','LEFT')
        	->join('region d ','d.id= a.city_id','LEFT')
        	->join('member_list e','e.member_list_id = a.member_list_id')
        	->where($where)
            ->order('a.certificate_id desc')
            ->field('a.*,b.name,c.name as pro_name,d.name as ciyt_name,a.schoolname, f.name as level,e.member_list_username')
             // ->select(false);
            ->paginate(15);
             // ee($lists);
            $list = $lists->items();
             foreach($list as $key => $value)
            {
                $list[$key]['level'] = Db::name('parameter')->field('name')->where(['id'=>['IN',$value['certificate_level']]])->select();
            }
		}else{
			$lists = Db::name('certificate')
			->alias('a')
			->join('parameter b ', 'b.id= a.certificate_typeid', 'LEFT')
			->join('parameter f ', 'f.id= a.certificate_level', 'LEFT')
			->join('region c ','c.id= a.province_id','LEFT')
        	->join('region d ','d.id= a.city_id','LEFT')
        	->join('member_list e','e.member_list_id = a.member_list_id')
            ->order('a.certificate_id desc')
			 ->field('a.*,b.name,c.name as pro_name,d.name as ciyt_name,a.schoolname, f.name as level,e.member_list_username')
			->paginate(15);
            // ->SELECT();
            // EE($lists);
             $list = $lists->items();
             foreach($list as $key => $value)
            {
                $list[$key]['level'] = Db::name('parameter')->field('name')->where(['id'=>['IN',$value['certificate_level']]])->select();
            }
        
		}
		
		
		$province = Db::name('region')->where('type',1)->select();

		//驾校分类，证件类型9
		 $info  = Db::name('parameter')->where(['module'=>'9','parent_id'=>'0'])->field('id,name')->select();
        $data = [];
        foreach ($info as $k=> $v) {
        	$data[] =  $v['id'];
        }
        $wherein = implode(',',$data);
		$data    = Db::name('parameter')->where('parent_id','in',$wherein)->field('id,name,parent_id')->select();
		$my_info    = $this->adsp_group($data,'parent_id'); 

		$this->assign('province', $province);
		$this->assign('page', $lists->render());
        $this->assign('list', $list);
        $this->assign('my_info', $my_info);
		return $this->fetch();
	}

	//参数设置
	public function paramsSetting(){
		$where = [
					'module'=>'9',
					'parent_id'=>'0'
				];
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
                'module' => 9,//1机械买卖；2机械租赁；3租赁运输；4修理厂；5机械配件；6司机；7证件；8融资；9代办证件管理
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
    //删除参数值
     public function paramssettingvaluedelete()
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
                'module' => 9,//1机械买卖；2机械租赁；3租赁运输；4修理厂；5机械配件；6司机；7证件；8融资；9代办证件
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
                'module' => 9,//1机械买卖；2机械租赁；3租赁运输；4修理厂；5机械配件；6司机；7证件；8融资；
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
            
            $certificate_typeid = $_POST['type'];

            if(is_array($certificate_typeid)){
                $certificate_typeid = implode(',',$certificate_typeid);
            }


            $parts_type = $_POST['parts_type'];
            if(is_array($parts_type)){
                $parts_type = implode(',',$parts_type);
            }
           
            $is_check = input('is_check');
            // 封面图片处理
            // 获取表单上传文件 例如上传了001.jpg
            $file = request()->file('file0');
            // 移动到框架应用根目录/public/uploads/ 目录下
            $info = $file->validate(['ext'=>'jpg,png,gif'])->move(ROOT_PATH . 'public' . DS . '/img/Certificates/');
            if($info){
               // 成功上传后 获取上传信息
                $pic_path = '/img/Certificates/'.$info->getSaveName();

            }else{
                 $this->error($file->getError());
                // 上传失败获取错误信息
            }


            $data = [
                'school_age'=>input('school_age'),
                'info_title'=>input('info_title'),
                'info_content'=>input('content'),
                'pic_path' => $pic_path,
                'member_list_id' => $member_list_id,
                'parts_type' => $parts_type,
                'certificate_typeid' => $certificate_typeid,
                'schoolname' => input('school'),
                'province_id' => input('province'),
                'city_id' => input('city'),
                'updatetime' => time(),
                'createtime' => time(),
                // 'certificate_level'=>$certificate_level,
                'is_effective' => input('is_effective',0),
            ];
            
            if(!$is_check){
                $data['is_check'] = 1;
            }else{
                $data['is_check'] = 2;
            }
            
            Db::name('certificate')->insert($data);
            $this->success('添加成功',url('index'),1);
        }
        else
        {
            $info  = Db::name('parameter')->where(['module'=>'9','type'=>1,'parent_id'=>'0','state'=>1])->field('id,name,multi')->select();
            // $multi = $this->adsp_group($info,'id');
            //证件类型的单复选按钮参数值 0单选 1多选
            $type_multi = Db::name('parameter')->where('id',37)->value('multi');

            $this->assign('type_multi',$type_multi);
            $data = [];
            foreach ($info as $k=> $v) {
            	$data[] =  $v['id'];
            }

            $wherein = implode(',',$data);
            $data    = Db::name('parameter')->where('parent_id','in',$wherein)->field('id,name,parent_id')->select();
            $list = $this->adsp_group($data,'parent_id');

            // 输入模块参数
            $input_data  = Db::name('parameter')->where(['module'=>'9','type'=>2,'parent_id'=>'0','state'=>1])->field('multi,parent_id,id,name')->select();
            $input_data = $this->adsp_group($input_data,'id');
            $this->assign('input_data',$input_data);
           
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
    public function driver_active()
    {
        //获取要修改的id
        $id = input("x");
        $newsmechanics_db = db("certificate");
        //判断此用户状态情况
        $status=$newsmechanics_db->where(array('certificate_id'=>$id))->value('is_effective');
        if($status == 1){
            //禁止
            $statedata = array('is_effective'=>0);
            $auth_group=$newsmechanics_db->where(array('certificate_id'=>$id))->setField($statedata);
            $this->success('未激活',1,1);
        }else{
            //开启
            $statedata = array('is_effective'=>1);
            $auth_group=$newsmechanics_db->where(array('certificate_id'=>$id))->setField($statedata);
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
        $res = Db::name('certificate')->where('certificate_id',$tid)->delete();
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
    
    	$id = input('certificate_id');
    	// 内容
    	$my_info = Db::name('certificate')
					->alias('a')
					->where('certificate_id',$id)
					->join('parameter b ', 'b.id= a.certificate_typeid', 'LEFT')
                    ->join('parameter f ', 'f.id= a.schoolname', 'LEFT')
					->join('parameter hh ', 'hh.id= a.parts_type', 'LEFT')
					->join('region c ','c.id= a.province_id','LEFT')
                	->join('region d ','d.id= a.city_id','LEFT')
                	->join('member_list e','e.member_list_id = a.member_list_id')
					->field('a.*, a.parts_type as asd, e.member_list_tel, c.name as pro_name,d.name as ciyt_name,a.schoolname,e.member_list_username')
					->find();
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
		 $info  = Db::name('parameter')->where(['module'=>'9','state'=>'1','parent_id'=>'0'])->field('id,name,state')->select();
        $data = [];
        foreach ($info as $k=> $v) {
            $data[] =  $v['id'];
        }

		$wherein = implode(',',$data);
		$data    = Db::name('parameter')->where('state=1')->where('parent_id','in',$wherein)->field('id,name,parent_id')->select();
		$list    = $this->adsp_group($data,'parent_id');

        //多选判断依据   驾驶证种类复选框value和这个变量比较
        $type = $my_info['certificate_typeid'];
        //驾照种类是单选还是复选参数
        $multi = Db::name('parameter')->where('id','37')->value('multi');
        $this->assign('multi',$multi);
        $this->assign('list', $list);
        $this->assign('type', $type);
        $this->assign('province', $province);
        $this->assign('city', $city);
        $this->assign('info',$info);
        $this->assign('my_info',$my_info);
	   
        return $this->fetch();
        
    }

    public function certificateupdate(){
    	$data = $input;

    	 if(request()->isAjax())
        {
            $member_list_tel = input('member_list_tel');
            $member_list_id = Db::name('member_list')->where("member_list_tel",$member_list_tel)->value('member_list_id');
            if(!$member_list_id)
            {
                $this->error('不存在该用户',0,0);
            }

            $certificate_typeid = $_POST['type'];
            if(is_array($certificate_typeid)){
                $certificate_typeid = implode(',',$certificate_typeid);
            }  

            $parts_type = $_POST['parts_type'];
            if(is_array($parts_type)){
                $parts_type = implode(',',$parts_type);
            }

            $data = [
                'school_age'=>input('school_age'),
                'info_title'=>input('info_title'),
                'info_content'=>input('content'),
                'member_list_id' => $member_list_id,
                'parts_type' => $parts_type,
                'certificate_typeid' => $certificate_typeid,
                'schoolname' => input('school'),
                'province_id' => input('province'),
                'city_id' => input('city'),
                'updatetime' => time(),
                'createtime' => time(),
                'reject_reason'=>input('reject_reason'),
                'is_check' => input('is_check'),
                'is_effective' => input('is_effective'),
            ];
            // 封面主图上传处理
             $file = request()->file('file0');
             if($file){
                // 移动到框架应用根目录/public/img/Certificates/ 目录下
                $info = $file->validate(['ext'=>'jpg,png,gif'])->move(ROOT_PATH . 'public' . DS . '/img/Certificates/');
                // echo $info;
                if($info){
                   // 成功上传后 获取上传信息
                    $pic_path = '/img/Certificates/'.$info->getSaveName();
                    $old_pic_path = input('oldpic');
                    $data['pic_path']=$pic_path; 
                    @unlink(ROOT_PATH.'public'.$old_pic_path);
                }else{
                     $this->error($file->getError());
                    // 上传失败获取错误信息
                }
             }
        }
          
        Db::name('certificate')->where('certificate_id',$_POST['certificate_id'])->update($data);
        $this->success('修改成功',url('index'),1);
    }

    public function paramssettingvalueadd(){
         if(request()->isAjax()){
             $pid = input('pid');
        if(empty($pid))
        {
            $this->redirect(url('paramsSetting'));
        }
        //有参数提交
        if(request()->isAjax())
        {
            $data = [
                'module' => 9,//1机械买卖；2机械租赁；3租赁运输；4修理厂；5机械配件；6司机；7证件；8融资；9代办证件
                'name' => input('name'),
                'multi' => 1,
                'state' => input('state',0),
                'value' => input('name'),
                'parent_id' => $pid
            ];

            Db::name('parameter')->insert($data);


            $this->success('添加成功',url('paramssetting',array('pid'=>$pid)),1);
        }
        }else{
            $pid= input('pid');
            $this->assign('pid',$pid);
      return $this->fetch();
            
        }
    }
}