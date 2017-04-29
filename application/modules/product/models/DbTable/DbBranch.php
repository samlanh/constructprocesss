<?php

class Product_Model_DbTable_DbBranch extends Zend_Db_Table_Abstract
{
	protected $_name = "tb_sublocation";
	
	public function getUserId(){
		return Application_Model_DbTable_DbGlobal::GlobalgetUserId();
	}
	function getAllLocation(){
		// $db = $this->getAdapter();
		// $sql = "SELECT * FROM tb_location WHERE status=1";
		// return $db->fetchAll($sql);
	}
	public function add($data){
		//print_r($data);exit();
		$db = $this->getAdapter();
		$part= PUBLIC_PATH.'/images/logo/';
		$photo = $_FILES['logo'];
			if($photo["name"]!=""){
				$temp = explode(".", $photo["name"]);
				$newfilename = $data["branch_name"]. '.' . end($temp);
				move_uploaded_file($_FILES['logo']["tmp_name"], $part . $newfilename);
				$photo_name = $newfilename;
			}
			
			
		$arr = array(
			//'loc_id'			=>	$data["location"],
			'name'				=>	$data["branch_name"],
			'branch_code'		=>	$data["code"],
			'prefix'			=>	$data["prefix"],
			'contact'			=>	$data["contact"],
			'phone'				=>	$data["contact_num"],
			'email'				=>	$data["email"],
			'contact'			=>	$data["contact"],
			'title_report_en'	=>	$data["title_en"],
			'title_report_kh'	=>	$data["title_kh"],
			'logo'				=>	$photo_name,
			'fax'				=>	$data["fax"],
			'address'			=>	$data["address"],
			'user_id'			=>	$this->getUserId(),
			'last_mod_date'		=>	new Zend_Date(),
			'status'			=>	$data["status"],
			'remark'			=>	$data["remark"],
		);
		$this->_name = "tb_sublocation";
		$id = $this->insert($arr);
		
		$sql = "SELECT id FROM tb_product";
		$rs = $db->fetchAll($sql);
		if(!empty($rs)){
			foreach ($rs as $row){
				$array = array(
					'pro_id'		=>	$row["id"],
					'location_id'	=>	$id,
					'qty'			=>	0,
					'qty_warning'	=>	0,
					'last_mod_date'	=>	new Zend_Date(),
					'user_id'		=>	$this->getUserId(),
				);
				
				$this->_name = "tb_prolocation";
				$this->insert($array);
			}
		}
	}
	
	public function edit($data){
		//print_r($data);exit();
		$db = $this->getAdapter();
		$db->beginTransaction();
		try{
			$part= PUBLIC_PATH.'/images/logo/';
			$photo = $_FILES['logo'];
				if($photo["name"]!=""){
					$temp = explode(".", $photo["name"]);
					$newfilename = $data["branch_name"]. '.' . end($temp);
					move_uploaded_file($_FILES['logo']["tmp_name"], $part . $newfilename);
					$photo_name = $newfilename;
				}else{
					$photo_name = $data["old_photo"];
				}
				
			$arr = array(
				//'loc_id'			=>	$data["location"],
				'name'				=>	$data["branch_name"],
				'branch_code'		=>	$data["code"],
				'prefix'			=>	$data["prefix"],
				'contact'			=>	$data["contact"],
				'phone'				=>	$data["contact_num"],
				'email'				=>	$data["email"],
				'contact'			=>	$data["contact"],
				'title_report_en'	=>	$data["title_en"],
				'title_report_kh'	=>	$data["title_kh"],
				'logo'				=>	$photo_name,
				'fax'				=>	$data["fax"],
				'address'			=>	$data["address"],
				'user_id'			=>	$this->getUserId(),
				'last_mod_date'		=>	date("Y-m-d"),
				'status'			=>	$data["status"],
				'remark'			=>	$data["remark"],
			);
			$this->_name = "tb_sublocation";
			$where = $db->quoteInto("id=?", $data["id"]);
			$this->update($arr, $where);
			$sql = "SELECT p.`id` FROM tb_product AS p WHERE p.id NOT IN(SELECT pl.pro_id FROM `tb_prolocation` AS pl WHERE  pl.`location_id`=".$data["id"].")";
			
			$rs = $db->fetchAll($sql);
			//print_r($rs);exit();
			if(!empty($rs)){
				foreach ($rs as $row){
					$array = array(
						'pro_id'		=>	$row["id"],
						'location_id'	=>	$data["id"],
						'qty'			=>	0,
						'qty_warning'	=>	0,
						'last_mod_date'	=>	new Zend_Date(),
						'user_id'		=>	$this->getUserId(),
					);
					
					$this->_name = "tb_prolocation";
					$this->insert($array);
				}
			}
			
			$db->commit();
		}catch(Exception $e){
			//$db->rollBack();
			$err =$e->getMessage();
			Application_Model_DbTable_DbUserLog::writeMessageError($err);			
		}
	
	}
	
	public function getAllBranch($data){
		$db = $this->getAdapter();
		$sql = "SELECT 
				  s.`id`,
				  s.`name`,
				  s.`branch_code`,
				  s.prefix,
				  s.`contact`,
				  s.`phone`,
				  s.`email`,
				  s.`office_tel`,
				  s.`address`,
				  (SELECT name_en FROM `tb_view` WHERE type=5 AND key_code=s.`status` LIMIT 1) status
				FROM
				  `tb_sublocation` AS s where 1 ";
		$where='';
		if($data["branch_name"]!=""){
			$s_where=array();
			$s_search = addslashes(trim($data['branch_name']));
			$s_where[]= " s.`name` LIKE '%{$s_search}%'";
			$s_where[]=" s.`contact` LIKE '%{$s_search}%'";
			$s_where[]=" s.`phone` LIKE '%{$s_search}%'";
			$s_where[]=" s.`branch_code` LIKE '%{$s_search}%'";
			$s_where[]=" s.`prefix` LIKE '%{$s_search}%'";
			$s_where[]=" s.`email` LIKE '%{$s_search}%'";
			$s_where[]=" s.`office_tel` LIKE '%{$s_search}%'";
			$s_where[]=" s.`address` LIKE '%{$s_search}%'";
			//$s_where[]= " cate LIKE '%{$s_search}%'";
			$where.=' AND ('.implode(' OR ', $s_where).')';
		}
		if($data["status"]!=""){
			$where.=' AND s.status='.$data["status"];
		}
		//echo $sql;
		return $db->fetchAll($sql.$where);
	}
	
	public function getBranchById($id){
		$db = $this->getAdapter();
		$sql = "SELECT 
				  s.* 
				FROM
				  `tb_sublocation` AS s 
				WHERE s.id = $id";
		return $db->fetchRow($sql);
	}
}