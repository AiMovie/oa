<?php
/**
 * 功能：广告管理
 * 修改时间：2017-6-28
 * 修改人：程龙飞
 */
namespace  app\admin\controller;

use app\common\controller\Auth;
use think\Session;
use think\Db;
use org\Stringnew;
class Advertisement extends Auth
{
    //广告PC端列表
	public function index()
	{
		$key = input('key');
		$plug_adtype_id = input('plug_adtype_id');
		$this->assign('plug_adtype_id',$plug_adtype_id);
		
		
		if($key  != '')
		{	 
			$map['plug_ad_name'] = array('like',"%".$key."%");
			$map['member_list_nickname'] = array('like',"%".$key."%");
			$map['_logic'] = 'OR';   //  或
			$arr['_complex'] = $map;   //   与
		}
		if($plug_adtype_id != ''){
			$plug_adtype_name = Db::name('plug_adtype')->where(array('plug_adtype_id'=>$plug_adtype_id))->getField('plug_adtype_name');
			$arr['plug_adtype_name'] = array('like',"%".$plug_adtype_name."%");
		}
		// $arr['b.plug_adtype_type'] = 'PC';
		$arr['b.plug_adtype_type'] = 'APP';
	    $member_list_db = Db::name('member_list');
        $plug_adtype_db = Db::name('plug_adtype');
        // $where['plug_adtype_type'] = 'PC';
        $where['plug_adtype_type'] = 'APP';
        $plug_adtype_list=Db::name('plug_adtype')->where($where)->order('plug_adtype_order')->select();//获取所有广告位
        $this->assign('plug_adtype_list',$plug_adtype_list);
		//实例化广告表
        $plug_ad_db = Db::name("plug_ad");

		$plug_list = $plug_ad_db
					->alias('a')
					->join('plug_adtype b',' a.plug_ad_adtypeid = b.plug_adtype_id','LEFT')
					->field('a.plug_ad_id,a.plug_ad_name,b.plug_adtype_name,a.plug_ad_starttime,a.plug_ad_endtime,a.plug_ad_checkid,a.plug_ad_url,a.plug_ad_adtypeid,a.plug_ad_js,a.plug_ad_object_id,a.plug_ad_pic,a.plug_ad_order,b.plug_adtype_type')
					->where($arr)
					->limit($Page->firstRow.','.$Page->listRows)
					->order("a.plug_ad_id DESC")
					->paginate(15);
        // ee($plug_list);
        foreach ($plug_list as $key => $value) {
        	if($value['plug_ad_checkid'] == 1)
        	{
        		$plug_list[$key]['plug_adtype_type'] = $value['plug_ad_pic'];
        	}
        	if($value['plug_ad_checkid'] == 2)
        	{
        		$plug_list[$key]['plug_adtype_type'] = $value['plug_ad_js'];
        	}
        	if($value['plug_ad_checkid'] == 3 || $value['plug_ad_checkid'] == 4)
        	{
        		$plug_list[$key]['plug_adtype_type'] = $value['plug_ad_object_id'];
        	}
        }
        $this->assign('page',$plug_list->render());
		
		$this->assign('list',$plug_list->items());	
		$this->assign('serach',input("key"));
		return $this->fetch();
	}
	//广告增加	
	public function adsAdd(){
		$member_list_db = Db::name('member_list');
        $member_info_db = Db::name('member_info');
        $plug_ad_db = Db::name('plug_ad');
        $plug_adtype_db = Db::name('plug_adtype');
        $object_id = input('object_id');
        $plug_ad_adtypeid = input('plug_ad_adtypeid');
       
		if (request()->isAjax()){
			//处理时间
	        $time = twoTime(input('reservation')); 
	        $plug_ad_checkid = input('plug_ad_checkid');
			$file = request()->file('file0');
			// ee($file);

			//最多添加
			$ads_max_num = Db::name('plug_adtype')->where('plug_adtype_id',$plug_ad_adtypeid)->find();
			$ads_now_num = Db::name('plug_ad')->where('plug_ad_adtypeid',$plug_ad_adtypeid)->count();
			
			if($ads_now_num >= $ads_max_num['plug_adtype_number']  ){
					$this->error('广告数量已最大,请删除后添加',Url('adsadd'),1);
			}
		    // 移动到框架应用根目录/public/data/uploads/ 目录下
		
		    $info = $file->move(ROOT_PATH . 'public' . DS . 'data'. DS . 'uploads');
			if($info) {
				$plug_ad_pic=substr(\think\Config::get('UPLOAD_DIR'),1).$info->getSaveName();//如果上传成功则完成路径拼接
			}else{
				$this->error($file->getError(),url('Sys/sys'),0);//否则就是上传错误，显示错误原因
			}
		// qq($_POST);
			// $object_id == 0 代表不绑定对象
			switch ($plug_ad_checkid) {
				case '2':
					if($object_id !== 0){
						$num = Db::name('mechanics')->where('id',$object_id)->count();
						if(!$num)
						{	
							$this->error('产品或厂商存在,请验证对象id',Url('adsadd'),1);
						}	
						
					}
					break;
				case '3':
					if($object_id !== 0){
						$num = Db::name('mechanics')->where('id',$object_id)->count();
						if(!$num)
						{	
							$this->error('产品或厂商存在,请验证对象id',Url('adsadd'),1);
						}	
					}
					break;
				case '4':
					if($object_id !== 0){
						$num = Db::name('repair')->where('id',$object_id)->count();
						if(!$num)
						{	
							$this->error('修理厂不存在,请验证对象id',Url('adsadd'),1);
						}
					}	
					break;
				case '5':
					if($object_id !== 0){
						$num = Db::name('Parts')->where('parts_id',$object_id)->count();
						if(!$num)
						{	
							$this->error('机械配件不存在,请验证对象id',Url('adsadd'),1);
						}
					}	
					break;
				case '6':
					if($object_id !== 0){
						$num = Db::name('lease')->where('id',$object_id)->count();
						if(!$num)
						{	
							$this->error('租赁不存在,请验证对象id',Url('adsadd'),1);
						}
					}	
					break;
				case '7':
					if($object_id !== 0){
						$num = Db::name('financing')->where('fianceing_id',$object_id)->count();
						if(!$num)
						{	
							$this->error('融资按揭不存在,请验证对象id',Url('adsadd'),1);
						}
					}	
					break;
				case '8':
					if($object_id !== 0){
						$num = Db::name('driver')->where('id',$object_id)->count();
						if(!$num)
						{	
							$this->error('司机不存在,请验证对象id',Url('adsadd'),1);
						}	
					}
					break;
				case '9':
					if($object_id !== 0){
						$num = Db::name('certificate')->where('certificate_id',$object_id)->count();
						if(!$num)
						{	
							$this->error('代办证件机构不存在,请验证对象id',Url('adsadd'),1);
						}
					}	
					break;
					
			}		   
				
					// 图片的
					$sl_data=array(
					'plug_ad_adtypeid'=>input('plug_ad_adtypeid'),
					'plug_ad_name'=>input('plug_ad_name'),
					'plug_ad_pic'=>$plug_ad_pic,
					'plug_ad_url'=>input('plug_ad_url'),
					'plug_ad_checkid'=>$plug_ad_checkid,
					// 'plug_ad_object_id'=>$plug_ad_object_id,
					// 'plug_ad_js'=>input('plug_ad_js'),
					'plug_ad_open'=>1,
					'plug_ad_order'=>input('plug_ad_order'),
					'plug_ad_content'=>input('plug_ad_content'),
					'plug_ad_addtime'=>SYS_TIME,
					'plug_ad_starttime'=>$time[0],
					'plug_ad_endtime'=>$time[1],
					'plug_ad_object_id'=>$object_id,
					// 'plug_ad_depid'=>$m_id,
					//plug_ad_butt 数据库中为预留字段
				); 
			
			
				Db::name('plug_ad')->insert($sl_data);
				$this->success('广告添加成功',Url('index'),1);
		}
		else
		{
			$where['plug_adtype_type'] = 'App';
            $plug_adtype_list=Db::name('plug_adtype')->where($where)->order('plug_adtype_order')->select();//获取所有广告位
            $this->assign('plug_adtype_list',$plug_adtype_list);

        	return $this->fetch();
        }
	}

	//广告列表编辑
	public function ad_pc_edit()
	{
		$member_list_db = Db::name('member_list');
        $member_info_db = Db::name('member_info');
        $plug_ad_db = Db::name('plug_ad');
        $plug_adtype_db = Db::name('plug_adtype');
        $plug_ad_id = input('plug_ad_id');
        $object_id = input('object_id');
        $plug_ad_adtypeid = input('plug_ad_adtypeid');
		if(request()->isAjax())
		{
			$time = twoTime(input('reservation')); 

	        $plug_ad_checkid = input('plug_ad_checkid');
	        //最多添加
			$ads_max_num = Db::name('plug_adtype')->where('plug_adtype_id',$plug_ad_adtypeid)->find();
			$ads_now_num = Db::name('plug_ad')->where('plug_ad_adtypeid',$plug_ad_adtypeid)->find();
			
			//如果改了广告类型 要判断新的广告类型数量有没有超出
			if(!$ads_now_num['plug_ad_adtypeid'] ==$plug_ad_adtypeid  ){
				if(count($ads_now_num) >= $ads_max_num['plug_adtype_number']  ){
					$this->error('广告数量已最大,请删除后添加',Url('adsadd'),1);
				}
			}
			
			$file = request()->file('file0');
			if($file){
		    // 移动到框架应用根目录/public/data/uploads/ 目录下
				 $info = $file->move(ROOT_PATH . 'public' . DS . 'data'. DS . 'uploads');
				if($info) {
					$plug_ad_pic=substr(\think\Config::get('UPLOAD_DIR'),1).$info->getSaveName();//如果上传成功则完成路径拼接
				}else{
					$this->error($file->getError(),url('Sys/sys'),0);//否则就是上传错误，显示错误原因
				}
			}
			// $object_id == 0 代表不绑定对象
			switch ($plug_ad_checkid) {
				case '2':
					if($object_id !== 0){
						$num = Db::name('mechanics')->where('id',$object_id)->count();
						if(!$num)
						{	
							$this->error('产品或厂商存在,请验证对象id',Url('adsadd'),1);
						}	
						
					}
					break;
				case '3':
					if($object_id !== 0){
						$num = Db::name('mechanics')->where('id',$object_id)->count();
						if(!$num)
						{	
							$this->error('产品或厂商存在,请验证对象id',Url('adsadd'),1);
						}	
					}
					break;
				case '4':
					if($object_id !== 0){
						$num = Db::name('repair')->where('id',$object_id)->count();
						if(!$num)
						{	
							$this->error('修理厂不存在,请验证对象id',Url('adsadd'),1);
						}
					}	
					break;
				case '5':
					if($object_id !== 0){
						$num = Db::name('Parts')->where('parts_id',$object_id)->count();
						if(!$num)
						{	
							$this->error('机械配件不存在,请验证对象id',Url('adsadd'),1);
						}
					}	
					break;
				case '6':
					if($object_id !== 0){
						$num = Db::name('lease')->where('id',$object_id)->count();
						if(!$num)
						{	
							$this->error('租赁不存在,请验证对象id',Url('adsadd'),1);
						}
					}	
					break;
				case '7':
					if($object_id !== 0){
						$num = Db::name('financing')->where('fianceing_id',$object_id)->count();
						if(!$num)
						{	
							$this->error('融资按揭不存在,请验证对象id',Url('adsadd'),1);
						}
					}	
					break;
				case '8':
					if($object_id !== 0){
						$num = Db::name('driver')->where('id',$object_id)->count();
						if(!$num)
						{	
							$this->error('司机不存在,请验证对象id',Url('adsadd'),1);
						}	
					}
					break;
				case '9':
					if($object_id !== 0){
						$num = Db::name('certificate')->where('certificate_id',$object_id)->count();
						if(!$num)
						{	
							$this->error('代办证件机构不存在,请验证对象id',Url('adsadd'),1);
						}
					}	
					break;
					
			}		   


			// qq($_POST);
			//匹配商家
		if($plug_ad_checkid == 300 || $plug_ad_checkid ==400){
			$member_list_nickname = input('member_list_nickname');
			$map['member_list_nickname'] = input("member_list_nickname");
			$map['member_list_tel'] = input("member_list_nickname");
			$map['_logic'] = "OR";
			$m_id = $member_list_db->where($map)->getField('member_list_id');
			if($m_id == ''){
                $this->error('没有找到匹配的商家');
			}else{
				$plug_ad_object_id=0;
				//构建数组
				if($plug_ad_checkid==3)
				{
					$fex = 'goods_id';
					$plug_ad_object_id = url_match_para(input("plug_ad_url"),$fex);
					
					if(!$plug_ad_object_id)
					{
						$this->error("域名不正确");
					}
				}
				elseif($plug_ad_checkid ==4)
				{
					$fex = 'shop_id';
					$plug_ad_object_id = url_match_para(input("plug_ad_url"),$fex);
					if(!$plug_ad_object_id)
					{
						$this->error("域名不正确");
					}
				}
				//商家或商品
				$sl_data=array(
					'plug_ad_adtypeid'=>input('plug_ad_adtypeid'),
					'plug_ad_name'=>input('plug_ad_name'),
					'plug_ad_pic'=>$plug_ad_pic,
					'plug_ad_url'=>input('plug_ad_url'),
					'plug_ad_checkid'=>$plug_ad_checkid,
					'plug_ad_object_id'=>$object_id,
					'plug_ad_js'=>input('plug_ad_js'),
					'plug_ad_open'=>1,
					'plug_ad_order'=>input('plug_ad_order'),
					'plug_ad_content'=>input('plug_ad_content'),
					'plug_ad_addtime'=>SYS_TIME,
					'plug_ad_starttime'=>$time[0],
					'plug_ad_endtime'=>$time[1],
					'plug_ad_depid'=>$m_id,
					//plug_ad_butt 数据库中为预留字段
						); 
			  			
					}
				}else
				{
					// 图片的
					$sl_data=array(
						'plug_ad_adtypeid'=>input('plug_ad_adtypeid'),
						'plug_ad_name'=>input('plug_ad_name'),
						'plug_ad_pic'=>$plug_ad_pic,
						'plug_ad_url'=>input('plug_ad_url'),
						'plug_ad_checkid'=>$plug_ad_checkid,
						'plug_ad_object_id'=>$object_id,
						// 'plug_ad_js'=>input('plug_ad_js'),
						'plug_ad_open'=>1,
						'plug_ad_order'=>input('plug_ad_order'),
						'plug_ad_content'=>input('plug_ad_content'),
						'plug_ad_addtime'=>SYS_TIME,
						'plug_ad_starttime'=>$time[0],
						'plug_ad_endtime'=>$time[1],
						// 'plug_ad_depid'=>$m_id,
						//plug_ad_butt 数据库中为预留字段
					); 
				}
				if(!isset($plug_ad_pic)){
						unset($sl_data['plug_ad_pic']);
				}
				
				if(empty($time)){
					unset($sl_data['plug_ad_starttime']);
					unset($sl_data['plug_ad_endtime']);
				}
				
				Db::name('plug_ad')->where('plug_ad_id',$plug_ad_id)->update($sl_data);
				$this->success('广告修改成功',url('index'),1);
		}else
		{
			//广告位
			$where['plug_adtype_type'] = 'APP';
			$plug_adtype_list = $plug_adtype_db->where($where)->order('plug_adtype_order')->select();//获取所有广告位
       		
       		// 广告信息
       		$ad_id = input('plug_ad_id');
       		$plug_ad_info =  Db::name('plug_ad')->where('plug_ad_id',$ad_id)->find();
       		
	        $this->assign('plug_adtype_list',$plug_adtype_list); 
       		$this->assign('plug_ad_info',$plug_ad_info);
			return $this->fetch();
		}
		
       	
		
	}

	//广告列表删除
	public function ad_pc_delete()
	{

        $plug_ad_db = Db::name('plug_ad');
        $plug_ad_id = input('plug_ad_id');
        $list_id = $plug_ad_db->where(array('plug_ad_id'=>$plug_ad_id))->delete();


        if($list_id)
        {
        	$this->success('广告删除成功',Url('index'),1);
        }
        	else
        {
            $this->error('广告删除失败',Url('index'),0);
        }
	} 
}