<?php
// +----------------------------------------------------------------------
// | 功能：机械管理后台
// +----------------------------------------------------------------------
// | 作者：程龙飞
// +----------------------------------------------------------------------
// | 日期：2017-6-28
// +----------------------------------------------------------------------
namespace app\Mechanics\Controller;

use app\common\controller\Auth;
use think\Db;
class Mechanicsadmin extends Auth
{
	//新机械配置信息
	public function newSetting(){
		if(request()->isAjax())
		{
			$options = input('options/a');
			$options['check_open'] = request()->post('check_open',0,'intval');
			$options['see_open'] = request()->post('see_open',0,'intval');
			Db::name('options')->where(array('option_name'=>'newSetting'))->setField('option_value',serialize($options));
			$this->success('修改成功',url('newSetting'),1);
		}
		else
		{
			$sys = Db::name('options')->where(array('option_name'=>'newSetting'))->value("option_value");
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
     * 新机械列表
     * @return mixed
     */
	public function newsMechanicsList(){
        if($_POST)
        {
            if(input('mechanical_name')){
                $data['b.mechanical_name'] = input('mechanical_name');
            }
            if(input('brand_name')){
                $data['c.brand_name'] = input('brand_name');
            }
            if(input('model_name')){
                $data['d.model_name'] = input('model_name');
            }
            if(input('province_id')){
                $data['a.province_id'] = input('province_id');
            }
            if(input('city_id')){
                $data['a.city_id'] = input('city_id');
            }
            $data['a.module'] =1;
            $data['a.type'] = 1;
            $result = Db::name('mechanics')
                ->alias('a')
                ->join('mechanical b ','b.id= a.mechanical_id','LEFT')
                ->join('brand c ','c.id= a.brand_id','LEFT')
                ->join('mechanical_model d ','d.id= a.mechanical_model_id','LEFT')
                ->join('region e ','e.id= a.city_id','LEFT')
                ->join('member_list f ','f.member_list_id= a.member_list_id','LEFT')
                ->where($data)
                ->order('create_time desc')
                ->field('a.*,b.mechanical_name,c.brand_name,d.model_name,e.name,f.member_list_tel')
                ->paginate(15);
            $this->assign('data',$data);
        }else{
		$map['a.module'] = 1;
		$map['a.type'] = 1;
		$result = Db::name('mechanics')
            ->alias('a')
            ->join('mechanical b ','b.id= a.mechanical_id','LEFT')
            ->join('brand c ','c.id= a.brand_id','LEFT')
            ->join('mechanical_model d ','d.id= a.mechanical_model_id','LEFT')
            ->join('region e ','e.id= a.city_id','LEFT')
            ->join('member_list f ','f.member_list_id= a.member_list_id','LEFT')
            ->where($map)
            ->order('create_time desc')
            ->field('a.*,b.mechanical_name,c.brand_name,d.model_name,e.name,f.member_list_tel')
            ->paginate(15);
        }
        //查询所有的省及直辖市
        $province=Db::name('region')->where('type',1)->field('id,name')->select();
//        p($province);
        $this->assign('province',$province);
		$this->assign('page',$result->render());
		$this->assign('list',$result->items());
		return $this->fetch();
	}

    /**
     * 二手机械列表
     * @return mixed
     */
	public function usedMechanicsList(){
        if($_POST)
        {
            if(input('mechanical_name')){
                $data['b.mechanical_name'] = input('mechanical_name');
            }
            if(input('brand_name')){
                $data['c.brand_name'] = input('brand_name');
            }
            if(input('model_name')){
                $data['d.model_name'] = input('model_name');
            }
            if(input('province_id')){
                $data['a.province_id'] = input('province_id');
            }
            if(input('city_id')){
                $data['a.city_id'] = input('city_id');
            }
            $data['a.module'] =1;
            $data['a.type'] = 2;
            $result = Db::name('mechanics')
                ->alias('a')
                ->join('mechanical b ','b.id= a.mechanical_id','LEFT')
                ->join('brand c ','c.id= a.brand_id','LEFT')
                ->join('mechanical_model d ','d.id= a.mechanical_model_id','LEFT')
                ->join('region e ','e.id= a.city_id','LEFT')
                ->join('member_list f ','f.member_list_id= a.member_list_id','LEFT')
                ->where($data)
                ->order('create_time desc')
                ->field('a.*,b.mechanical_name,c.brand_name,d.model_name,e.name,f.member_list_tel')
                ->paginate(15);
            $this->assign('data',$data);
        }else{
            $map['a.module'] = 1;
            $map['a.type'] = 2;
            $result = Db::name('mechanics')
                ->alias('a')
                ->join('mechanical b ','b.id= a.mechanical_id','LEFT')
                ->join('brand c ','c.id= a.brand_id','LEFT')
                ->join('mechanical_model d ','d.id= a.mechanical_model_id','LEFT')
                ->join('region e ','e.id= a.city_id','LEFT')
                ->join('member_list f ','f.member_list_id= a.member_list_id','LEFT')
                ->where($map)
                ->order('create_time desc')
                ->field('a.*,b.mechanical_name,c.brand_name,d.model_name,e.name,f.member_list_tel')
                ->paginate(15);
        }
        //查询所有的省及直辖市
        $province=Db::name('region')->where('type',1)->field('id,name')->select();
        $this->assign('province',$province);
        $this->assign('page',$result->render());
        $this->assign('list',$result->items());
        return $this->fetch();
	}

	//二手机械配置信息
	public function oldSetting(){
		$n_id = input('n_id');
		if (empty($n_id)){
			$this->error('参数错误',url('news_list'),0);
		}else{
			$news_list=Db::name('news')->where(array('n_id'=>input('n_id')))->find();
			/*
			 * 多图字符串转换成数组
			 */
			$text = $news_list['news_pic_allurl'];
			$pic_list = array_filter(explode(",", $text));
			$this->assign('pic_list',$pic_list);

			$nav = new \org\Leftnav;
			$menu_next=Db::name('menu')->where('menu_type <> 4 and menu_type <> 2')-> order('listorder') -> select();
			$diyflag=Db::name('diyflag')->select();
			$arr = $nav::menu_n($menu_next);
			$source=Db::name('source')->select();//来源
			$this->assign('source',$source);
			$this->assign('menu',$arr);
			$this->assign('diyflag',$diyflag);
			$this->assign('news_list',$news_list);
			// var_dump($diyflag);exit;
			return $this->fetch();
		}
	}

	//参数设置
	public function new_paramsSetting(){
		
        $where = [
                    'module'=>'10',
                    'parent_id'=>'0'
                ];
        $list = Db::name('parameter')->where($where)->select();
        $this->assign('list',$list);
		return $this->fetch();
	}

	//参数设置
	public function old_paramsSetting(){
		if (!request()->isAjax()){
			$this->error('提交方式不正确',url('news_list'),0);
		}
		$news=Db::name('news');
		//获取图片上传后路径
		$checkpic=input('checkpic');
		$oldcheckpic=input('oldcheckpic');

		$pic_oldlist=input('pic_oldlist');//老多图字符串

		if($pop=$_FILES['pic_one']['name'][0] || $popp=$_FILES['pic_all']['name'][0]){ //images 是你上传的名称
			$file = request()->file('pic_one');
			if($file)
			{
			    // 移动到框架应用根目录/public/data/uploads/ 目录下
			    $info = $file->move(ROOT_PATH . 'public/data/uploads');
				if($info) {
					$img_url=substr(\think\Config::get('UPLOAD_DIR'),1).$info->getSaveName();//如果上传成功则完成路径拼接
					//写入文件记录数据库
					$data['uptime']=time();
					$data['filesize']=$info->getInfo()['size'];
					$data['path']=$img_url;
					Db::name('plug_files')->insert($data);
				}else{
					$this->error($file->getError(),url('news_list'),0);//否则就是上传错误，显示错误原因
				}				
			}
			$picall_url="";
			//文章图集
		    $files  = request()->file('pic_all');

		    if($files)
		    {
			    foreach($files as $file)
			    {
				    $info = $file->move(ROOT_PATH . 'public/data/uploads');
					if($info) {
						$picall=substr(\think\Config::get('UPLOAD_DIR'),1).$info->getSaveName();//如果上传成功则完成路径拼接
						//写入文件记录数据库
						$data['uptime']=time();
						$data['filesize']=$info->getInfo()['size'];
						$data['path']=$img_url;
						Db::name('plug_files')->insert($data);
						$picall_url=$picall.','.$picall_url;
					}else{
						$this->error($file->getError(),url('news_list'),0);//否则就是上传错误，显示错误原因
					}
			    }		    	
		    }


			$picall_list=$pic_oldlist.$picall_url;//整合新的多图字符串以及老的字符串
		}else{
			$picall_list=$pic_oldlist;//整合新的多图字符串以及老的字符串
		}
		$sll_data=array(
			'n_id'=>input('n_id'),
		);

		//获取文章属性
		$news_flag = input('news_flag/a');
		if($news_flag)
		{
			$flag=array();
			foreach ($news_flag as $v){
				$flag[] = $v;
			}
			$flagdata = implode(',',$flag);
		}
		else
		{
			$flagdata = '';
		}

		$sl_data=array(
			'news_title'=>input('news_title'),
			'news_titleshort'=>input('news_titleshort'),
			'news_columnid'=>input('news_columnid'),
			'news_flag'=>$flagdata,
			'news_zaddress'=>input('news_zaddress'),
			'news_key'=>input('news_key'),
			'news_tag'=>input('news_key'),
			'news_source'=>input('news_source'),
			'news_pic_type'=>input('news_pic_type'),
			'news_pic_content'=>input('news_pic_content'),
			'news_open'=>input('news_open'),
			'news_scontent'=>input('news_scontent'),
			'news_content'=>htmlspecialchars_decode(input('news_content')),
		);
		if ($checkpic!=$oldcheckpic){
			$sl_data['news_img']=$img_url;
		}
		$sl_data['news_pic_allurl']=$picall_list;
		$rst=Db::name('news')->where(array('n_id'=>input('n_id')))->update($sl_data);

		if($rst!==false){
			$this->success('文章修改成功,返回列表页',url('news_list'),1);
		}else{
			$this->error('文章修改失败',url('news_list'),0);
		}
	}


    /**
     * 添加新机械
     * @return mixed
     */
    public function newsmechanicsAdd()
    {
        if(request()->isAjax())
        {
            $member_list_tel = input('member_list_tel');
            $member_list_id = Db::name('member_list')->where("member_list_tel",$member_list_tel)->value('member_list_id');
            if(!$member_list_id)
            {
                $this->error('不存在该用户',0,0);
            }
            //图片处理
            $file = request()->file('file0');
            if($file)
            {
                // 移动到框架应用根目录/public/data/uploads/ 目录下
                $info = $file->move(ROOT_PATH . 'public' . DS . 'data'. DS . 'uploads');
                if($info) {
                    $mechanics_img_url=substr(\think\Config::get('UPLOAD_DIR'),1).$info->getSaveName();//如果上传成功则完成路径拼接
                }else{
                    $this->error($file->getError(),url('index'),0);//否则就是上传错误，显示错误原因
                }
            } 
            //图片处理结束
            $data = [
                'producing_country' => input('producing_country'),
                'mechanical_id' => input('mechanical_id'),
                'brand_id' => input('brand_id'),
                'mechanical_model_id' => input('mechanical_model_id'),
                'province_id' => input('province_id'),
                'city_id' => input('city_id'),
                'company_name' => input('company_name'),
                'telphone' => input('telphone'),
                'price' => input('price'),
                'is_invoice' => input('is_invoice'),
                'is_check' => input('is_check'),
                'is_effective' => input('is_effective'),
                'photo'=>$mechanics_img_url,
                'member_list_id'=>$member_list_id,
                'create_time' => time(),
                'type'=>1
            ];
            $res=Db::name('mechanics')->insert($data);
            if($res){
                $this->success('添加成功',url('newsmechanicslist'),1);
            }else{
                $this->error('添加失败',Url('newsmechanicslist'),0);
            }

        }
        else
        {
            //类型列表
            $mechanical_list = Db::name('mechanical')->field('id,mechanical_name')->select();
            //查询所有的省及直辖市
            $province=Db::name('region')->where('type',1)->field('id,name')->select();
//            $province=Db::name('region')->where('pid',0)->field('cityid,name')->select();
            //生产国列表
            $country_id = Db::name('parameter')->where("name",'生产国')->where("module",10)->where("state",1)->value('id');
            $product_country = Db::name('parameter')->where("parent_id",$country_id)->where("state",1)->field('id,name')->select();
            $this->assign('mechanical_list',$mechanical_list);
            $this->assign('province',$province);
            $this->assign('product_country',$product_country);
            return $this->fetch();
        }

    }

    /**
     * 添加二手机械
     * @return mixed
     */
    public function usedmechanicsAdd()
    {
        if($_POST)
        {
            $member_list_tel = input('member_list_tel');
            $member_list_id = Db::name('member_list')->where("member_list_tel",$member_list_tel)->value('member_list_id');
            if(!$member_list_id)
            {
                $this->error('不存在该用户',0,0);
            }
            //图片处理
            $file = request()->file('file0');
            if($file)
            {
                // 移动到框架应用根目录/public/data/uploads/ 目录下
                $info = $file->move(ROOT_PATH . 'public' . DS . 'data'. DS . 'uploads');
                if($info) {
                    $mechanics_img_url=substr(\think\Config::get('UPLOAD_DIR'),1).$info->getSaveName();//如果上传成功则完成路径拼接
                }else{
                    $this->error($file->getError(),url('index'),0);//否则就是上传错误，显示错误原因
                }
            } 
            //图片处理结束
            $data = [
                'producing_country' => input('producing_country'),
                'mechanical_id' => input('mechanical_id'),
                'brand_id' => input('brand_id'),
                'mechanical_model_id' => input('mechanical_model_id'),
                'province_id' => input('province_id'),
                'city_id' => input('city_id'),
                'company_name' => input('company_name'),
                'tonnage' => input('tonnage'),
                'nation' => input('nation'),
                'work_hours' => input('work_hours'),
                'manufacture_date' => input('manufacture_date'),
                'is_invoice' => input('is_invoice'),
                'telphone' => input('telphone'),
                'car_number' => input('car_number'),
                'price' => input('price'),
                'is_effective' => input('is_effective'),
                'photo'=>$mechanics_img_url,
                'create_time' => time(),
                'type'=>2
            ];
            $data['is_check']=(input('is_check')==2)?2:1;
            $id =Db::name('mechanics')->insertGetId($data);
            $fileurl_tmp = input('fileurl_tmp/a');
            //添加商品图片
            foreach ($fileurl_tmp as $key => $value) {
                $data = array(
                    'uptime' => time(),
                    'path' => $value,
                    'product_id' => $id,
                    'type' => 1
                );
                Db::name('plug_files')->insert($data);
            }
            $this->success('添加成功',url('usedMechanicsList'),1);
        }
        else
        {
            //类型列表
            $mechanical_list = Db::name('mechanical')->field('id,mechanical_name')->select();
            //查询所有的省及直辖市
            $province=Db::name('region')->where('type',1)->field('id,name')->select();
//            $province=Db::name('region')->where('pid',0)->field('cityid,name')->select();
            $country_id = Db::name('parameter')->where("name",'生产国')->where("module",10)->where("state",1)->value('id');
            $product_country = Db::name('parameter')->where("parent_id",$country_id)->where("state",1)->field('id,name')->select();
            $this->assign('product_country',$product_country);
            $this->assign('mechanical_list',$mechanical_list);
            $this->assign('province',$province);
            return $this->fetch();
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
     * 删除新机械信息
     */
    public function newsMechanics_del(){
        //获取要删除的ID
        $tid = input('id');
        $p = input('page');
        // 判断ID是否为空
        if(empty($tid)){
            $this->error('参数错误',Url('newsMechanicsList',array('page'=>$p)),0);
        }
        //删除数据
        $res = Db::name('mechanics')->where('id',$tid)->delete();
        // 判断是否删除成功
        if($res){
            $this->success('删除成功',Url('newsMechanicsList',array('page'=>$p)),1);
        }
        else{
            $this->error('删除失败',Url('newsMechanicsList',array('page'=>$p)),0);
        }
    }


    /**
     * 删除新机械信息
     */
    public function usedMechanics_del(){
        //获取要删除的ID
        $tid = input('id');
        $p = input('page');
        // 判断ID是否为空
        if(empty($tid)){
            $this->error('参数错误',Url('usedMechanicsList',array('page'=>$p)),0);
        }
        //删除数据
        $res = Db::name('mechanics')->where('id',$tid)->delete();
        // 判断是否删除成功
        if($res){
            $this->success('删除成功',Url('usedMechanicsList',array('page'=>$p)),1);
        }
        else{
            $this->error('删除失败',Url('usedMechanicsList',array('page'=>$p)),0);
        }
    }



    /**
     * 修改新机械激活状态
     */
    public function newsmechanics_active(){
        //获取要修改的id
        $id = input("x");
        $newsmechanics_db = db("mechanics");
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
     * 修改二手机械激活状态
     */
    public function usedmechanics_active(){
        //获取要修改的id
        $id = input("x");
        $usedmechanics_db = db("mechanics");
        //判断此用户状态情况
        $status=$usedmechanics_db->where(array('id'=>$id))->value('is_effective');
        if($status == 1){
            //禁止
            $statedata = array('is_effective'=>0);
            $auth_group=$usedmechanics_db->where(array('id'=>$id))->setField($statedata);
            $this->success('未激活',1,1);
        }else{
            //开启
            $statedata = array('is_effective'=>1);
            $auth_group=$usedmechanics_db->where(array('id'=>$id))->setField($statedata);
            $this->success('已激活',1,1);
        }
    }


    /**
     * 修改新机械信息
     * @return mixed
     */
    public function newsmechanicsEdit(){
        if($_POST){
            $mid = input('id');
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
                $mechanics_img_url = $img_url;
            }
            else
            {
                //原有图片
                $mechanics_img_url = input('oldcheckpicname');
            }

            $data = [
                'producing_country' => input('producing_country'),
                'mechanical_id' => input('mechanical_id'),
                'brand_id' => input('brand_id'),
                'mechanical_model_id' => input('mechanical_model_id'),
                'province_id' => input('province_id'),
                'city_id' => input('city_id'),
                'company_name' => input('company_name'),
                'telphone' => input('telphone'),
                'price' => input('price'),
                'is_invoice' => input('is_invoice'),
                'is_check' => input('is_check'),
                'is_effective' => input('is_effective'),
                'photo' => $mechanics_img_url,
                'create_time' => time(),
                'type'=>1,
                'reject_reason'=>input('reject_reason')
            ];
            $member_list_tel=input('member_list_tel');
            $data['member_list_id']=Db::name('member_list')->where('member_list_tel',$member_list_tel)->value('member_list_id');
            if($data['member_list_id']){
                $res = Db::name('mechanics')->where('id',$mid)->update($data);
                $this->success('修改成功',url('newsmechanicslist'),1);
            }else{
                $this->error('所属用户不存在',Url('newsmechanicslist'),0);
            }
        }
        else
        {
            $map['a.module'] = 1;
            $map['a.type'] = 1;
            $map['a.id'] = input('id');
            $result = Db::name('mechanics')
                ->alias('a')
                ->join('mechanical b ','b.id= a.mechanical_id','LEFT')
                ->join('brand c ','c.id= a.brand_id','LEFT')
                ->join('mechanical_model d ','d.id= a.mechanical_model_id','LEFT')
                ->join('region e ','e.id= a.city_id','LEFT')
                ->join('region f ','f.id= a.province_id','LEFT')
                ->join('member_list g ','g.member_list_id= a.member_list_id','LEFT')
                ->where($map)
                ->order('create_time desc')
                ->field('a.*,b.mechanical_name,c.brand_name,d.model_name,e.name,f.name as province_name,g.member_list_tel')
                ->find();
            //类型列表
            $mechanical_list = Db::name('mechanical')->field('id,mechanical_name')->select();
            //查询所有的省及直辖市
            $province=Db::name('region')->where('type',1)->field('id,name')->select();
            //生产国列表
            $country_id = Db::name('parameter')->where("name",'生产国')->where("module",10)->where("state",1)->value('id');
            $product_country = Db::name('parameter')->where("parent_id",$country_id)->where("state",1)->field('id,name')->select();
            $this->assign('product_country',$product_country);
            $this->assign('mechanical_list',$mechanical_list);
            $this->assign('province',$province);
            $this->assign('result',$result);
            return $this->fetch();
        }
    }

    /**
     * 修改二手机械信息
     * @return mixed
     */
    public function usedmechanicsEdit(){
        $mid = input('id');
        if($_POST){
            $member_list_tel = input('member_list_tel');
            $member_list_id = Db::name('member_list')->where("member_list_tel",$member_list_tel)->value('member_list_id');
            if(!$member_list_id)
            {
                $this->error('不存在该用户',0,0);
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
                $mechanics_img_url = $img_url;
            }
            else
            {
                //原有图片
                $mechanics_img_url = input('oldcheckpicname');
            }
            $data = [
                'member_list_id' => $member_list_id,
                'producing_country' => input('producing_country'),
                'mechanical_id' => input('mechanical_id'),
                'brand_id' => input('brand_id'),
                'mechanical_model_id' => input('mechanical_model_id'),
                'province_id' => input('province_id'),
                'city_id' => input('city_id'),
                'company_name' => input('company_name'),
                'tonnage' => input('tonnage'),
                'nation' => input('nation'),
                'work_hours' => input('work_hours'),
                'manufacture_date' => input('manufacture_date'),
                'is_invoice' => input('is_invoice'),
                'telphone' => input('telphone'),
                'car_number' => input('car_number'),
                'price' => input('price'),
                'is_effective' => input('is_effective'),
                'is_certificate' => input('is_certificate'),
                'photo'=>$mechanics_img_url,
                'create_time' => time(),
                'type'=>2
            ];

            Db::name('mechanics')->where('id',$mid)->update($data);
            Db::name('plug_files')->where(['type'=>1,'product_id'=>$mid])->delete();
            $fileurl_tmp = input('fileurl_tmp/a');
            //添加商品图片
            if(!empty($fileurl_tmp)){
                foreach ($fileurl_tmp as $key => $value) {
                    $data = array(
                        'uptime' => time(),
                        'path' => $value,
                        'product_id' => $mid,
                        'type' => 1
                    );
                    Db::name('plug_files')->insert($data);
                }
            }

            $this->success('修改成功',url('usedmechanicslist',['p'=>input('p')]),1);
        }
        else
        {
            $map['a.module'] = 1;
            $map['a.type'] = 2;
            $map['a.id'] = input('id');
            $result = Db::name('mechanics')
                ->alias('a')
                ->join('mechanical b ','b.id= a.mechanical_id','LEFT')
                ->join('brand c ','c.id= a.brand_id','LEFT')
                ->join('mechanical_model d ','d.id= a.mechanical_model_id','LEFT')
                ->join('region e ','e.id= a.city_id','LEFT')
                ->join('region f ','f.id= a.province_id','LEFT')
                ->join('member_list g ','g.member_list_id= a.member_list_id','LEFT')
                ->where($map)
                ->order('create_time desc')
                ->field('a.*,b.mechanical_name,c.brand_name,d.model_name,e.name,f.name as province_name,g.member_list_tel')
                ->find();
            //类型列表
            $mechanical_list = Db::name('mechanical')->field('id,mechanical_name')->select();
            //查询所有的省及直辖市
            $province=Db::name('region')->where('type',1)->field('id,name')->select();
            $img_list = Db::name('plug_files')->where(['type'=>1,'product_id'=>$mid])->order('id ASC')->select();
            //生产国列表
            $country_id = Db::name('parameter')->where("name",'生产国')->where("module",10)->where("state",1)->value('id');
            $product_country = Db::name('parameter')->where("parent_id",$country_id)->where("state",1)->field('id,name')->select();
            $this->assign('product_country',$product_country);
            $this->assign('img_list',$img_list);
            $this->assign('mechanical_list',$mechanical_list);
            $this->assign('province',$province);
            $this->assign('result',$result);
            return $this->fetch();
        }
    }

    //管理设置
    public function setting()
    {
        if(request()->isAjax())
        {
            $options = input('options/a');
            $options['check_open'] = request()->post('check_open',0,'intval');
            $options['see_open'] = request()->post('see_open',0,'intval');
            Db::name('options')->where(array('option_name'=>'mechanicsSetting'))->setField('option_value',serialize($options));
            $this->success('修改成功',url('setting'),1);
        }
        else
        {
            $sys = Db::name('options')->where(array('option_name'=>'mechanicsSetting'))->value("option_value");
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


    //tian
    //参数设置
    public function paramssettingadd(){
        //有参数提交
        if(request()->isAjax())
        {
            $data = [
                'module' => 10,//1机械买卖；2机械租赁；3租赁运输；4修理厂；5机械配件；6司机；7证件；8融资；9代办证件管理；10新机械管理
                'name' => input('name'),
                'multi' => input('multi',0),
                'state' => input('state',0),
                'value' => '',
                'parent_id' => 0,
                'type'=>input('type'),
                'input_type'=>input('input_type'),
            ];

            Db::name('parameter')->insert($data);

            $this->success('添加成功',url('new_paramssetting'),1);
        }
        //没有参数提交显示页面
        else
        {
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
     * 修改参数
     * @return mixed
     */
    public function paramsSettingEdit()
    {
        $id = input('id');
        if(!id)
        {
            $this->redirect(url('paramssetting'));
        }
        //有参数提交
        if(request()->isAjax())
        {
            $data = [
                'module' => 10 ,//1机械买卖；2机械租赁；3租赁运输；4修理厂；5机械配件；6司机；7证件；8融资；
                'name' => input('name'),
                'multi' => input('multi',0),
                'state' => input('state',0),
                'value' => '',
                'parent_id' => 0,
                'type'=>input('type'),
                'input_type'=>input('input_type'),
            ];

            Db::name('parameter')->where(['id'=>$id])->update($data);


            $this->success('修改成功',url('new_paramssetting'),1);
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
            $this->error('数据不存在',url('new_paramssetting'),0);
        }
        Db::name('parameter')->where(array('id'=>input('id')))->delete();

        if($rst!==false){
            $this->success('删除成功',url('new_paramssetting'),1);
        }else{
            $this->error('删除失败',url('new_paramssetting'),0);
        }
    }

    /*二级参数*/
     public function paramssettingvalue(){
     $pid = input('pid');
     $data = Db::name('parameter')->where('parent_id',$pid)->select();
     $this->assign('list',$data);
     $this->assign('pid',$pid);
    return $this->fetch();

 }
public function paramsSettingValueAdd(){
         $pid = input('pid');
        if(empty($pid))
        {
            $this->redirect(url('paramssettingvalue'));
        }
        //有参数提交
        if(request()->isAjax())
        {
            $data = [
                'module' => 10,//1机械买卖；2机械租赁；3租赁运输；4修理厂；5机械配件；6司机；7证件；8融资；9代办证件
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
                'module' => 10,//1机械买卖；2机械租赁；3租赁运输；4修理厂；5机械配件；6司机；7证件；8融资；
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


    /*
    二手机械参数设置
     */
    public function oldParamsSetting(){
        $key = input('key');
        $where['module'] = 11;//1机械买卖；2机械租赁；3租赁运输；4修理厂；5机械配件；6司机；7证件；8融资；9代办证件 11二手机械参数设置
        $where['parent_id'] = 0;
        if($key){
            $where['name'] .=$key;
        }
        $list = Db::name('parameter')->where($where)->select();
        $this->assign('list',$list);
     
        return $this->fetch();
    }

    /*
    *二手机械参数设置
     */
    public function oldParamsSettingAdd(){
        //有参数提交
        if(request()->isAjax())
        {
            $data = [
                'module' => 11,//1机械买卖；2机械租赁；3租赁运输；4修理厂；5机械配件；6司机；7证件；8融资；9代办证件管理 10新机械 11 二手机械
                'name' => input('name'),
                'multi' => input('multi',0),
                'state' => input('state',0),
                'value' => '',
                'parent_id' => 0,
                'type'=>input('type'),
                'input_type'=>input('input_type'),
            ];

            Db::name('parameter')->insert($data);

            $this->success('添加成功',url('oldParamsSetting'),1);
        }
        //没有参数提交显示页面
        else
        {
            return $this->fetch();
        }
    }

    /*
    *二手机械参数修改
     */
    public function oldParamsSettingEdit()
    {
        $id = input('id');
        if(!id)
        {
            $this->redirect(url('oldParamsSetting'));
        }
        //有参数提交
        if(request()->isAjax())
        {
            $data = [
                'module' => 11 ,//1机械买卖；2机械租赁；3租赁运输；4修理厂；5机械配件；6司机；7证件；8融资；11 二手机械
                'name' => input('name'),
                'multi' => input('multi',0),
                'state' => input('state',0),
                'value' => '',
                'parent_id' => 0
            ];

            Db::name('parameter')->where(['id'=>$id])->update($data);


            $this->success('修改成功',url('oldParamsSetting'),1);
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
    /*
    *二手机械二级参数列表
     */
     public function oldParamsSettingValue(){
        $key = input('key');
        if(!empty($key)){
            $where['name'] = array('like','%'.$key.'%');
        }
        $where['parent_id'] = $pid = input('pid');
        $data = Db::name('parameter')->where($where)->select();
        $this->assign('list',$data);
        $this->assign('pid',$pid);
        return $this->fetch();

    }

    /*
    *二手机械二级参数值设置
     */
    public function oldParamsSettingValueAdd(){
        $pid = input('pid');
        if(!$pid)
        {
            $this->redirect(url('oldParamsSettingValue'));
        }
        //有参数提交
        if(request()->isAjax())
        {
            $data = [
                'module' =>11, //1机械买卖；2机械租赁；3租赁运输；4修理厂；5机械配件；6司机；7证件；8融资；9代办证件管理 10新机械 11 二手机械
                'name' => input('name'),
                'multi' => 1,
                'state' => input('state',0),
                'value' => input('name'),
                'parent_id' => $pid
            ];

            Db::name('parameter')->insert($data);


            $this->success('添加成功',url('oldParamsSettingValue',array('pid'=>$pid)),1);
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
     * 二手机械二级参数值修改
     */
    public function oldParamsSettingvalueEdit()
    {
        $id = input('id');
        if(!id)
        {
            $this->redirect(url('oldParamsSettingValueadd',array('pid'=>input('pid'))));
        }

        //有参数提交
        if(request()->isAjax())
        {
            $data = [
                'module' => 11,//1机械买卖；2机械租赁；3租赁运输；4修理厂；5机械配件；6司机；7证件；8融资；11 二手机械
                'name' => input('name'),
                'multi' => 1,
                'state' => input('state',0),
                'value' => input('name'),
                'parent_id' => input('pid'),
            ];

            Db::name('parameter')->where(['id'=>$id])->update($data);


            $this->success('修改成功',url('oldParamsSettingValue',array('pid'=>input('pid'))),1);
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

    /*
    *删除二手机械二级参数
     */
    public function oldParamsSettingDelete()
    {
        $id = input('id');
        $pid = input('pid');
        if (empty($id)){
            $this->error('数据不存在',url('oldParamsSettingValue'),0);
        }
        Db::name('parameter')->where(array('id'=>input('id')))->delete();

        if($rst!==false){
            $this->success('删除成功',url('oldParamsSettingValue',array('pid'=>$pid)),1);
        }else{
            $this->error('删除失败',url('oldParamsSettingValue',array('pid'=>$pid)),0);
        }
    }




    //删除参数
    public function paramsSettingValueDelete()
    {
        $id = input('id');
        if (empty($id)){
            $this->error('数据不存在',url('paramsSetting'),0);
        }
        $rst=Db::name('parameter')->where(array('id'=>input('id')))->delete();

        if($rst!==false){
            $this->success('删除成功',url('paramsSettingValue',array('pid'=>input('pid'))),1);
        }else{
            $this->error('删除失败',url('paramsSettingValue',array('pid'=>input('pid'))),0);
        }
    }

}