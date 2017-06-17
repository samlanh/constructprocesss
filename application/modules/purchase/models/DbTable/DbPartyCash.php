<?php

class Purchase_Model_DbTable_DbPartyCash extends Zend_Db_Table_Abstract
{
	protected function GetuserInfo(){
		$user_info = new Application_Model_DbTable_DbGetUserInfo();
		$result = $user_info->getUserInfo();
		return $result;
	}
	function getProductName($id){
		$db = $this->getAdapter();
		$sql = "SELECT p.id,p.`item_code`,p.`item_name`,(SELECT m.name FROM `tb_measure` AS m WHERE m.id=p.`measure_id` LIMIT 1) AS measure FROM `tb_product` AS p WHERE p.id=$id";
		return $db->fetchRow($sql);
	}
	function getRequestPrint($id){
		$db = $this->getAdapter();
		$sql = "SELECT 
				  p.`number_request` ,
				  p.`re_code`,
				  p.`date_from_work_space`,
				  p.`date_request`,
  p.`remark` AS p_remark,
				  (SELECT pl.name FROM `tb_plan` AS pl WHERE pl.id=p.`plan_id`) AS plan,
				  (SELECT pl.name FROM `tb_sublocation` AS pl WHERE pl.id=p.`branch_id` LIMIT 1) AS branch,
				  (SELECT pd.item_name FROM `tb_product` AS pd WHERE pd.id=pci.`pro_id` LIMIT 1) AS item_name,
				  (SELECT pd.item_code FROM `tb_product` AS pd WHERE pd.id=pci.`pro_id` LIMIT 1) AS item_code,
				  (SELECT m.name FROM `tb_measure` AS m WHERE m.id=(SELECT pd.measure_id FROM `tb_product` AS pd WHERE pd.id=pci.`pro_id` LIMIT 1) LIMIT 1) AS measure,
				  pci.`price`,
				  pci.`qty`,
				  pci.`remark`,
				  pci.`date_in`
				FROM
				  `tb_purchase_request_party_cash` AS p,
				  `tb_purchase_request_party_cash_item` AS pci 
				  WHERE p.id=pci.`pur_id` AND p.`id`=$id";
		return $db->fetchAll($sql);
	}
	
	function getPoPrint($id){
		$db = $this->getAdapter();
		$sql = "SELECT 
				   p.`re_work_number` AS number_request  ,
				  p.`re_code`,
				  p.`re_work_date` AS `date_from_work_space`,
				  p.`re_date` AS `date_request`,
				  p.`remark` AS p_remark,
				  p.`po_date`,
				  p.`order_number`,
				   pci.`sample_price`,
				  (SELECT pl.name FROM `tb_plan` AS pl WHERE pl.id=p.`paln_id`) AS plan,
				  (SELECT pl.name FROM `tb_sublocation` AS pl WHERE pl.id=p.`branch_id` LIMIT 1) AS branch,
				  (SELECT pd.item_name FROM `tb_product` AS pd WHERE pd.id=pci.`pro_id` LIMIT 1) AS item_name,
				  (SELECT pd.item_code FROM `tb_product` AS pd WHERE pd.id=pci.`pro_id` LIMIT 1) AS item_code,
				  (SELECT m.name FROM `tb_measure` AS m WHERE m.id=(SELECT pd.measure_id FROM `tb_product` AS pd WHERE pd.id=pci.`pro_id` LIMIT 1) LIMIT 1) AS measure,
				  pci.`price`,
				  pci.`qty`,
				  pci.`remark`,
				  pci.`date_in`
				FROM
				  `tb_purchase_party_cash` AS p,
				  `tb_purchase_party_cash_item` AS pci 
				  WHERE p.id=pci.`pur_id` AND p.`id`=$id";
		return $db->fetchAll($sql);
	}
	function getRequestCode($location){
		$db = $this->getAdapter();
		$user = $this->GetuserInfo();
		//$location = $user['branch_id'];
		
		$sql_pre = "SELECT pl.`prefix` FROM `tb_sublocation` AS pl WHERE pl.`id`=$location";
		$prefix = $db->fetchOne($sql_pre);
		
		$sql="SELECT r.id FROM `tb_purchase_request_party_cash` AS r WHERE r.`branch_id`=$location ORDER BY r.`id` DESC LIMIT 1";
		$num = $db->fetchOne($sql);
		
		$num_lentgh = strlen((int)$num+1);
		$num = (int)$num+1;
		$pre = $prefix."MRP-";
		for($i=$num_lentgh;$i<5;$i++){
			$pre.="0";
		}
		return $pre.$num;
	}
	function getPOCode($location){
		$db = $this->getAdapter();
		$user = $this->GetuserInfo();
		//$location = $user['branch_id'];
		
		$sql_pre = "SELECT pl.`prefix` FROM `tb_sublocation` AS pl WHERE pl.`id`=$location";
		$prefix = $db->fetchOne($sql_pre);
		
		$sql="SELECT r.id FROM `tb_purchase_party_cash` AS r WHERE r.`branch_id`=$location ORDER BY r.`id` DESC LIMIT 1";
		$num = $db->fetchOne($sql);
		
		$num_lentgh = strlen((int)$num+1);
		$num = (int)$num+1;
		$pre = $prefix."POP-";
		for($i=$num_lentgh;$i<5;$i++){
			$pre.="0";
		}
		return $pre.$num;
	}
	function getRequestDetail($id){
		$db = $this->getAdapter();
		$sql = "SELECT 
				  p.*,
				  (SELECT pr.item_code FROM `tb_product` AS pr WHERE pr.id=p.`pro_id` LIMIT 1) AS item_code,
				  (SELECT pr.item_name FROM `tb_product` AS pr WHERE pr.id=p.`pro_id` LIMIT 1) AS item_name,
				  (SELECT m.name FROM `tb_measure` AS m WHERE m.id=(SELECT pr.measure_id FROM `tb_product` AS pr WHERE pr.id=p.`pro_id` LIMIT 1) LIMIT 1) AS measure 
				FROM
				  `tb_purchase_request_party_cash_item` AS p 
				WHERE p.`pur_id` = $id";
		return $db->fetchAll($sql);
	}
	function getAllRequest($search){
		$db = $this->getAdapter();
		$sql = "SELECT 
				  p.`id`,
				  (SELECT s.name FROM `tb_sublocation` AS s WHERE s.id=p.`branch_id`) AS branch,
				  p.`re_code`,
				  p.`date_request`,
				  p.`status` ,
				  p.re_edit,
				  p.po_id,
				  p.`date_from_work_space`,
				  p.`number_request`,
				  (SELECT pl.`name` FROM `tb_plan` AS pl WHERE pl.id=p.`plan_id`) AS plan,
				  p.`pedding` AS pedding_stat,
				  p.`appr_status` AS appr_stat,
				  (SELECT name_en FROM `tb_view` AS v WHERE v.key_code = p.`status` AND v.type=5) AS `pstatus`,
				  (SELECT name_en FROM `tb_view` AS v WHERE v.key_code = p.`appr_status` AND v.type=12) AS `app_status`,
				  (SELECT name_en FROM `tb_view` AS v WHERE v.key_code = p.`pedding` AND v.type=11) AS `pedding`,
				  (SELECT u.username FROM tb_acl_user AS u WHERE u.user_id = P.user_id LIMIT 1 ) AS user_name,
				  (SELECT s.is_edit FROM `tb_su_price_idcompare` AS s WHERE s.re_id=p.id LIMIT 1) AS is_edit
				FROM
				  `tb_purchase_request_party_cash` AS p ";
		
		$from_date =(empty($search['start_date']))? '1': " date_request >= '".$search['start_date']."'";
		$to_date = (empty($search['end_date']))? '1': " date_request <= '".$search['end_date']."'";
		$where = " WHERE ".$from_date." AND ".$to_date;
		if(!empty($search['text_search'])){
			$s_where = array();
			$s_search = trim(addslashes($search['text_search']));
			$s_where[] = " re_code LIKE '%{$s_search}%'";
			$s_where[] = " id LIKE '%{$s_search}%'";
			$s_where[] = " status LIKE '%{$s_search}%'";
			$where .=' AND ('.implode(' OR ',$s_where).')';
		}
		/*if($search['purchase_status']>0){
			$where .= " AND purchase_status =".$search['purchase_status'];
		}*/
		$dbg = new Application_Model_DbTable_DbGlobal();
		$where.=$dbg->getAccessPermission();
		$order=" ORDER BY id DESC ";
 		//echo $sql.$where.$order;
		return $db->fetchAll($sql.$where.$order);
	}
	function getRequestById($id){
		$db = $this->getAdapter();
		$sql = "SELECT 
				  p.`id`,
				  p.`date_request`,
				  p.`re_code`,
				  p.`status`,
				  p.`branch_id`,
				  p.`remark`,
				  p.`number_request`,
				  p.`date_from_work_space`,
				  p.`plan_id`,
				  (SELECT pr.remark FROM `tb_purchase_request_party_cash_remark` AS pr WHERE pr.re_id=p.`id` AND pr.type=1) AS reject_check,
				  (SELECT pr.remark FROM `tb_purchase_request_party_cash_remark` AS pr WHERE pr.re_id=p.`id` AND pr.type=2) AS reject_approve
				FROM
				  `tb_purchase_request_party_cash` AS p 
				WHERE p.`id` =$id";
		return $db->fetchRow($sql);
	}
	function  add($data){
		$db = $this->getAdapter();
		$db->beginTransaction();
		$user = $this->GetuserInfo();
		$GetUserId = $user['user_id'];
		//print_r($data);exit();
		$identity = $data["identity"];
		$ids=explode(',',$data['identity']);
		try{
			if($identity!=""){
				$arr = array(
					're_code'				=>	$data["request_no"],
					'plan_id'				=>	$data["plan"],
					'number_request'		=>	$data["number_work_space"],
					'date_from_work_space'	=>	date("Y-m-d",strtotime($data["date_request_work_space"])),
					'branch_id'				=>	$data["branch"],
					'date_request'			=>	date("Y-m-d",strtotime($data["date_request"])),
					'user_id'				=>	$GetUserId,
					'status'				=>	$data["status"],
					'pedding'				=>	6,
					'appr_status'			=>	0,
					're_edit'				=>	0,
					'remark'				=>	$data["remark"],
				);
				$this->_name = "tb_purchase_request_party_cash";
				$id = $this->insert($arr);
				
				foreach($ids as $i){
					$orderdata = array(
						'pur_id'		=>	$id,
						"pro_id"      	=> 	$data['item_id_'.$i],
						"qty"     		=> 	$data["qty_".$i],
						'price'			=> 	$data["price_".$i],
						"date_in"     	=> 	date("Y-m-d",strtotime($data["date_in_".$i])),
						"remark"       	=> 	$data["remark_".$i],
					);
					
					$this->_name='tb_purchase_request_party_cash_item';
					$recieved_order = $this->insert($orderdata);
				}
			}
			$db->commit();
			return $id;
		}catch(Exception $e){
			$db->rollBack();
			$err =$e->getMessage();
			Application_Model_DbTable_DbUserLog::writeMessageError($err);			}
	}
	
	function  edit($data){
		$db = $this->getAdapter();
		$db->beginTransaction();
		$user = $this->GetuserInfo();
		$GetUserId = $user['user_id'];
		//print_r($data);exit();
		$identity = $data["identity"];
		$ids=explode(',',$data['identity']);
		try{
			if($identity!=""){
				
				$arr = array(
					//'re_code'				=>	$data["request_no"],
					'plan_id'				=>	$data["plan"],
					'number_request'		=>	$data["number_work_space"],
					'date_from_work_space'	=>	date("Y-m-d",strtotime($data["date_request_work_space"])),
					'branch_id'				=>	$data["branch"],
					'date_request'			=>	date("Y-m-d",strtotime($data["date_request"])),
					'user_id'				=>	$GetUserId,
					'status'				=>	$data["status"],
					'pedding'				=>	6,
					'appr_status'			=>	0,
					're_edit'				=>	0,
					'remark'				=>	$data["remark"],
				);
				$this->_name = "tb_purchase_request_party_cash";
				$where = "id=".$data["id"];
				$this->update($arr,$where);
				
				$sql = "DELETE FROM tb_purchase_request_party_cash_item WHERE pur_id="."'".$data["id"]."'";
				$db->query($sql);
				
				foreach($ids as $i){
					$orderdata = array(
						'pur_id'		=>	$data["id"],
						"pro_id"      	=> 	$data['item_id_'.$i],
						"qty"     		=> 	$data["qty_".$i],
						'price'			=> 	$data["price_".$i],
						"remark"       	=> 	$data["remark_".$i],
						"date_in"     	=> 	date("Y-m-d",strtotime($data["date_in_".$i])),
					);
					
					$this->_name='tb_purchase_request_party_cash_item';
					$recieved_order = $this->insert($orderdata);
				}
			}
			$db->commit();
			return $data["id"];
		}catch(Exception $e){
			$db->rollBack();
			$err =$e->getMessage();
			Application_Model_DbTable_DbUserLog::writeMessageError($err);			}
	}
	
	function  addpo($data){
		$db = $this->getAdapter();
		$db->beginTransaction();
		$user = $this->GetuserInfo();
		$GetUserId = $user['user_id'];
		//print_r($data);exit();
		$identity = $data["identity"];
		$ids=explode(',',$data['identity']);
		try{
			if($identity!=""){
				
				$arr = array(
					//'vendor_name'			=>	$data["vendor"],
					're_code'				=>	$data["request_no"],
					'paln_id'				=>	$data["plan"],
					'order_number'			=>	$data["po_no"],
					're_work_number'		=>	$data["number_work_space"],
					're_id'					=>	$data["id"],
					're_work_date'			=>	date("Y-m-d",strtotime($data["date_request_work_space"])),
					'branch_id'				=>	$data["branch"],
					're_date'				=>	date("Y-m-d",strtotime($data["date_request"])),
					'po_date'				=>	date("Y-m-d",strtotime($data["po_date"])),
					'date_order'			=>	new Zend_date(),
					'user_id'				=>	$GetUserId,
					'status'				=>	$data["status"],
					'pending_status'		=>	6,
					'appr_status'			=>	1,
					'remark'				=>	$data["remark"],
				);
				$this->_name = "tb_purchase_party_cash";
				$po_id  = $this->insert($arr);
				
				$arr_id = array(
					'po_id'		=>	$po_id,
					're_edit'	=>	1,
				);
				$this->_name = "tb_purchase_request_party_cash";
				$where = "id=".$data["id"];
				$this->update($arr_id,$where);
				
				foreach($ids as $i){
					$orderdata = array(
						'pur_id'		=>	$po_id,
						"pro_id"      	=> 	$data['item_id_'.$i],
						"qty"     		=> 	$data["qty_".$i],
						'sample_price'	=>	$data["price_request_".$i],
						'price'			=> 	$data["price_".$i],
						"remark"       	=> 	$data["remark_".$i],
						"date_in"     	=> 	date("Y-m-d",strtotime($data["date_in_".$i])),
					);
					
					$this->_name='tb_purchase_party_cash_item';
					$recieved_order = $this->insert($orderdata);
				}
			}
			$db->commit();
			//return $data["id"];
		}catch(Exception $e){
			$db->rollBack();
			$err =$e->getMessage();
			Application_Model_DbTable_DbUserLog::writeMessageError($err);			}
	}
	
	public function getProductOption(){
		$db = $this->getAdapter();
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$user_info = new Application_Model_DbTable_DbGetUserInfo();
		$result = $user_info->getUserInfo();
		$sql_cate = 'SELECT `id`,name FROM tb_category WHERE status = 1 AND name!="" ORDER BY name ';
		
		$row_cate = $db->fetchAll($sql_cate);
		$user_info = new Application_Model_DbTable_DbGetUserInfo();
		$result = $user_info->getUserInfo();
		$option="";		
		if($result["level"]==1 OR $result["level"]==2){
			$option .= '<option value="-1">'.$tr->translate("SELECT_PRODUCT").'</option>';
		}
		foreach($row_cate as $cate){
			$option .= '<optgroup  label="'.htmlspecialchars($cate['name'], ENT_QUOTES).'">';
			if($result["level"]==1 OR $result["level"]==2){
				$sql = "SELECT id,item_name,
				(SELECT tb_brand.name FROM `tb_brand` WHERE tb_brand.id=brand_id limit 1) As brand_name,
				item_code FROM tb_product WHERE cate_id = ".$cate['id']." 
						AND item_name!='' ORDER BY id DESC ";
			}else{
				$sql = " SELECT p.id,p.item_name,p.item_code,
				(SELECT tb_brand.name FROM `tb_brand` WHERE tb_brand.id=p.brand_id limit 1) As brand_name
				 FROM tb_product AS p
				INNER JOIN tb_prolocation As pl ON p.id = pl.pro_id
				WHERE p.cate_id = ".$cate['id']."
				AND p.item_name!='' AND pl.location_id =".$result['branch_id']." ORDER BY user_id DESC ";
			}
				$rows = $db->fetchAll($sql);
				if($rows){
					foreach($rows as $value){
						$option .= '<option value="'.$value['id'].'" label="'.htmlspecialchars($value['item_name']." ".$value['brand_name'], ENT_QUOTES).'">'.
							htmlspecialchars($value['item_name']." ".$value['brand_name'], ENT_QUOTES)." ".htmlspecialchars($value['item_code'], ENT_QUOTES)
						.'</option>';
					}
				}
			$option.="</optgroup>";
		}
		
		return $option;
	}
}